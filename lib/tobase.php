<?php
//This file takes care of writing changes to data base.


function sanitarize_input($escape_html = true, $escape_sql = true)
{
    $_SERVER['HTTP_USER_AGENT'] = secure_input($_SERVER['HTTP_USER_AGENT'], $escape_html, $escape_sql);
    foreach (array('POST', 'GET') as $value) {
        $method_type = $value;
        if ($method_type == 'POST') {
            $method_data = $_POST;
        } elseif ($method_type == 'GET') {
            $method_data = $_GET;
        }
        $raw = get_table_contents("input", array("name", "type"), "WHERE method='" . $method_type . "'");
        for ($i = 0; $i < count($raw); $i++) {
            $type_def[strtolower($raw[$i]['name'])] = $raw[$i]['type'];
        }

        if (count($method_data) > 1024) {
            Die("Too much post parameters");
        }

        foreach ($method_data as $name => $value) {
            if (StringLeft($name, 4) == "sid:") {
                if (!isset($method_data['sid'])) {
                    $method_data['sid'] = array();
                }
                if ($value == 'all' && $_GET['a'] != 'sessions') {
                    $method_data['sid'][0] = 'all';
                }
                $method_data['sid'][] = secure_input($value, $escape_html, $escape_sql);
            } else {
                switch ($type_def[strtolower($name)]) {
                    case "string":
                        $method_data[$name] = secure_input($value, $escape_html, $escape_sql);
                        break;
                    case "email":
                        $method_data[$name] = secure_input($value, $escape_html, $escape_sql);
                        break;
                    case "image":
                        $method_data[$name] = secure_input($value, $escape_html, $escape_sql);
                        break;
                    case "check":
                        $method_data[$name] = checkbox_to_int($value);
                        break;
                    case "int":
                        $method_data[$name] = intval($value);
                        break;
                    case "html":
                        $method_data[$name] = secure_input($value, false, $escape_sql);
                        break;
                    default:
                        error_push_title("sanitarize_input error");
                        error_push_body("Variable type not defined for $method_type [$name]");
                        error_call();
                }
            }
        }
        if ($method_type == 'POST') {
            $_POST = $method_data;
        } elseif ($method_type == 'GET') {
            $_GET = $method_data;
        }
        $method_data = "";
    }
}

function checkbox_to_int($str)
{
    if ($str == "on" || $str == "true" || $str == "1") {
        return 1;
    }
    return 0;
}

function to_base_general($type)
{
    global $current_user;
    log_event('ADMINISTRATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "UPDATE settings", 'Updated settings for ' . $type);
    $settings = get_table_contents(general, 'ALL', " WHERE readonly=0 AND class='" . $type . "' ");
    $updateParams = [];
    for ($i = 0; $i < count($settings); $i++) {
        if ($settings[$i]["readonly"] == "0") {
            $data = $_POST[$settings[$i]["setting"]];
            if ($settings[$i]["type"] == "bool") {
                if ($data == "1" || $data == "0") {
                    $updateParams[] = array(
                        ":new_value" => $data,
                        ":setting" => $settings[$i]["setting"],
                        ":setting_class" => $type
                    );
                } else {
                    return false;
                }
            } elseif ($settings[$i]["type"] == "int") {
                if (is_numeric($data)) {
                    $updateParams[] = array(
                        ":new_value" => $data,
                        ":setting" => $settings[$i]["setting"],
                        ":setting_class" => $type
                    );
                } else {
                    return false;
                }
            } else {//string
                if ($settings[$i]["setting"] == 'language') {
                    $data = preg_replace('~\P{Xan}++~u', '', $data);
                }
                $updateParams[] = array(
                    ":new_value" => $data,
                    ":setting" => $settings[$i]["setting"],
                    ":setting_class" => $type
                );
            }
        }
    }

    foreach ( $updateParams as $updateParam){
        _mysql_prepared_query(array(
            "query" => "UPDATE general SET value=:new_value WHERE setting=:setting AND class=:setting_class",
            "params" => $updateParam
        ));
    }

    return true;
}

function group_set_permissions($group_id, $permissions, $forum_id = 0)
{
    $permissions = explode('|', $permissions);
    $values = [];
    if ($group_id != "") {
        for ($i = 0; $i < count($permissions); $i++) {
            $values[] = array( $group_id , $forum_id,  $permissions[$i] );
        }
        $result = _mysql_prepared_query(array(
            "query" => "DELETE FROM group_permissions WHERE group_id = :gid AND forum_id = :fid",
            "params" => array(
                ":gid" => $group_id,
                ":fid" => $forum_id
            )
        ));
        if ($permissions[0] != "") {
            $result = _mysql_prepared_query(array(
                "query" => "INSERT INTO group_permissions VALUES :values",
                "params" => array(
                    ":values" => $values
                )
            ));
        }
        return $result;
    }
    return false;
}


function group_delete_by_id($gid)
{
    global $current_user;
    if ($gid == 0 || $gid == "") {
        return false;
    }
    group_set_permissions($gid, '');//clean up
    $group_name = group_get_name_by_id($gid);
    log_event('ADMINISTRATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "MANAGE groups", 'Deleted group named ' . $group_name);
    return _mysql_prepared_query(array(
        "query" => "DELETE FROM groups WHERE id= :gid",
        "params" => array(
            ":gid" => $gid
        )
    ));
}

function secure_input($input, $html = true, $sql = true)
{
    if ($html) {
        $input = htmlentities($input, ENT_COMPAT | ENT_HTML401, 'UTF-8');
    }
    if ($sql) {
        $input = _mysql_real_escape_string($input);
    }
    return $input;
}

function decode_input($str)
{
    return str_replace(array("\\n", "\\r", "\\\"", "\\'", "\\\\"), array("\n", "\r", '"', "'", "\\"), $str);
}

function mkuser()
{
    return user_add($_POST['username'], $_POST['user_password'], $_POST['user_email'], '', '', time());
}