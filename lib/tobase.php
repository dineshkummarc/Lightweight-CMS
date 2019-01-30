<?php
//This file takes care of writeing changes to data base.


function sanitarize_input(){
    $_SERVER['HTTP_USER_AGENT'] = secure_input($_SERVER['HTTP_USER_AGENT']);
    foreach(array('POST','GET') as $value){
        $method_type = $value;
        if ($method_type == 'POST'){
            $method_data = $_POST;
        }elseif($method_type == 'GET'){
            $method_data = $_GET;
        }
        $raw = get_table_contents("input",array("name","type"),"WHERE method='".$method_type."'");
        for ($i = 0;$i < count($raw);$i++)
        {
            $type_def[strtolower($raw[$i]['name'])] = $raw[$i]['type'];
        }

        if(count($method_data) > 1024){Die("Too much post parameters");}

        foreach ($method_data as $name => $value){
            if(StringLeft($name, 4) == "sid:"){
                if(!isset($method_data['sid'])){
                    $method_data['sid'] = array();
                }
                if($value=='all' && $_GET['a'] != 'sessions'){
                    $method_data['sid'][0] = 'all';
                }
                $method_data['sid'][] =secure_input($value);
            }else{
                switch ($type_def[strtolower($name)]){
                    case "string":
                        $method_data[$name] = secure_input($value);
                        break;
                    case "email":
                        $method_data[$name] = secure_input($value);
                        break;
                    case "image":
                        $method_data[$name] = secure_input($value);
                        break;
                    case "check":
                        $method_data[$name] = checkbox_to_int($value);
                        break;
                    case "int":
                        $method_data[$name] = intval($value);
                        break;
                    case "html":
                        $method_data[$name] = secure_input($value,false);
                        break;
                    default:
                        error_push_title("sanitarize_input error");
                        error_push_body("Variable type not defined for $method_type [$name]");
                        error_call();
                }
            }
        }
        if ($method_type == 'POST'){
            $_POST = $method_data;
        }elseif($method_type == 'GET'){
            $_GET = $method_data;
        }
        $method_data = "";
    }
}

/*  #FUNCTION# ;===============================================================================

name...........: checkbox_to_int
description ...:
Syntax.........: checkbox_to_int($str)
Parameters ....: $str -
Author ........:
Modified.......:
Remarks .......:
Related .......:
Parameters ....:
Link ..........:
Example .......:
;==========================================================================================*/
function checkbox_to_int($str){
	if($str == "on" || $str == "true") {return 1;}
	return 0;
}

/*  #FUNCTION# ;===============================================================================

name...........: to_base_general
description ...:
Syntax.........: to_base_general()
Parameters ....:
Author ........:
Modified.......:
Remarks .......:
Related .......:
Parameters ....:
Link ..........:
Example .......:
;==========================================================================================*/
function to_base_general($type) {
    global $current_user;
    log_event('ADMINISTRATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "UPDATE settings", 'Updated settings for '.$type);
    $settings = get_table_contents(general,'ALL'," WHERE readonly=0 AND class='".$type."' ");
    for($i = 0;$i < count($settings);$i++){
        if ($settings[$i]["readonly"] == "0"){
            $data = $_POST[$settings[$i]["setting"]];
            if ($settings[$i]["type"] == "bool"){
                if($data == "1" || $data == "0"){
                    $update[] = 'UPDATE general SET value=\''.$data.'\' WHERE setting=\''.$settings[$i]["setting"]."' AND class='".$type."';\n";
                }else{
                    return false;
                }
            }elseif($settings[$i]["type"] == "int"){
                if(is_numeric($data)){
                    $update[] = 'UPDATE general SET value=\''.$data.'\' WHERE setting=\''.$settings[$i]["setting"]."'  AND class='".$type."';\n";
                }else{
                    return false;
                }
            }else{//string
                if($settings[$i]["setting"] == 'language'){
                    $data = preg_replace('~\P{Xan}++~u', '', $data);
                }
                $update[] = 'UPDATE general SET value=\''.$data.'\' WHERE setting=\''.$settings[$i]["setting"]."' AND class='".$type."';\n";
            }
        }
    }
    for($i = 0; $i < count($update);$i++){
        _mysql_query($update[$i]);
    }
    return true;
}

/*  #FUNCTION# ;===============================================================================

name...........: group_set_permissions
description ...:
Syntax.........: group_set_permissions($group_id, $permissions)
Parameters ....:$group_id     -
                 $permissions -
Author ........:
Modified.......:
Remarks .......:
Related .......:
Parameters ....:
Link ..........:
Example .......:
;==========================================================================================*/
function group_set_permissions($group_id,$permissions,$forum_id = 0){
    $permissions = explode('|',$permissions);
    $values = "";
    $forum_where = " AND forum_id = ".$forum_id;
    if ($group_id != "")
    {
        for ($i = 0; $i < count($permissions);$i++){
            $values .= "(".$group_id.", ".$forum_id.", ".$permissions[$i]."),";
        }
        $values = StringTrimRight($values,1);
        $sql = 'DELETE FROM group_permissions WHERE group_id = '.$group_id.$forum_where;
        $result = _mysql_query($sql);
        if ($permissions[0] != "") {
            $sql = 'INSERT INTO group_permissions VALUES '.$values;
            $result = _mysql_query($sql);
        }
        return $result;
    }
    return false;
}



/*  #FUNCTION# ;===============================================================================

name...........: group_delete_by_id
description ...:
Syntax.........: group_delete_by_id($gid)
Parameters ....: $gid -
Author ........:
Modified.......:
Remarks .......:
Related .......:
Parameters ....:
Link ..........:
Example .......:
;==========================================================================================*/
function group_delete_by_id($gid)
{
    global $current_user;
    if ($gid==0 || $gid == ""){return false;}
    group_set_permissions($gid,'');//clean up
    $group_name = group_get_name_by_id($gid);
    log_event('ADMINISTRATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "MANAGE groups", 'Deleted group named '. $group_name);
    return _mysql_query('DELETE FROM groups WHERE id='.$gid);
}

function to_base_user(){
    $error = 0;
    if($_GET['uid'] == NULL){return 32;}
    if(checkbox_to_int($_POST["delete_account"]) != 1){
        if (!user_validate_name($_POST['username'])){return 8;}
        $change_pass = ($_POST['password'] == $_POST['password_confirm'] && $_POST['password'] != '');
        $password_mismatch = ($_POST['password'] != $_POST['password_confirm'] && $_POST['password'] != '');
        $change_rank =($_POST['group'] == user_get_default_group($_GET['uid']) && $_POST['rank'] != user_get_rank($_GET['uid']));
        $change_group = ($_POST['group'] != user_get_default_group($_GET['uid']));
        $change_mail = user_validate_email($_POST['email']);
        $change_msn = user_validate_email($_POST['msn']);
        if($password_mismatch){$error = 1;}

        _mysql_query("UPDATE post SET username='".$_POST['username']."' WHERE user_id='".$_GET['uid']."'"); // update cache
        $cmd = 'UPDATE users';
        $update = 'SET username=\''.$_POST['username'].'\', user_avatar=\''.$_POST['avatar'].'\', user_show_facebook=\''.checkbox_to_int($_POST['showMSN']).'\', user_show_mail=\''.checkbox_to_int($_POST['show_email']).'\', user_warn=\''.$_POST['warn'].'\', user_founder=\''.checkbox_to_int($_POST['founder']).'\', user_signature=\''.$_POST['signature'].'\', ';
        $cmd_end = 'WHERE user_id='.$_GET['uid'];
        if($change_pass){$update .= 'user_password=\''.encrypt($_POST['password']).'\', ';}
        if($change_rank){$update .= 'user_rank=\''.$_POST['rank'].'\', ';}
        if($change_mail){$update .= 'user_email=\''.$_POST['email'].'\', ';}else{$error +=16;}
        if($change_msn){$update .= 'user_facebook=\''.$_POST['msn'].'\', ';}
        $update = StringTrimRight($update,2);
        $sql=$cmd."\n".$update."\n".$cmd_end;
        $result = _mysql_query($sql);
        if(!$result){$error += 2;}
        if ($change_group){
            $result = user_set_default_group($_GET['uid'],$_POST['group']);
            if(!$result){$error += 4;}
        }
    }else{
       $sql = "DELETE FROM users WHERE user_id = ".$_GET['uid'];
        $result = _mysql_query($sql);
        if(!$result){$error += 2;}
    }
    return $error;
}

function alter_rank(){
    if($_POST['image'] != "" && !validate_file_name($_POST['image'],'jpg|png|gif|bmp')){return false;}
    if($_POST['name'] == ''){return false;}
    if($_POST['id']  > 0 ){
        $sql = "UPDATE ranks SET image='".$_POST['image']."', name='".$_POST['name']."', special=".$_POST['special'].", required_posts=".$_POST['required_posts']." WHERE id = ".$_POST['id'];
    }else{
        $sql = "INSERT INTO ranks VALUES (NULL, '".$_POST['image'] ."', '".$_POST['name'] ."', '".$_POST['special'] ."', '".$_POST['required_posts'] ."');" ;
    }
    $result = _mysql_query($sql);
    return $result;
}

function delete_rank()
{
 $_POST['id'] = abs(intval($_POST['id']));
 return _mysql_query('DELETE FROM ranks WHERE id = '.$_POST['id']);
}

function secure_input($input,$html=true,$sql=true){
    if ($html){$input=htmlentities($input,ENT_COMPAT | ENT_HTML401,'UTF-8');}
    if ($sql){$input=_mysql_real_escape_string($input);}
    return $input;
}

function decode_input($str){
    return str_replace(array("\\n","\\r","\\\"","\\'","\\\\"),array("\n","\r",'"',"'","\\"),$str);
}

function mkuser()
{
    $_POST['username'] = secure_input($_POST['username']);
    $_POST['user_email'] = secure_input($_POST['user_email']);
    return user_add($_POST['username'],$_POST['user_password'],$_POST['user_email'],'','',time());
}

function to_base_bbcode(){
    definde_missing_post(
            array(
                'bbcode_display' => '0'   
            )
    );
    if ($_GET["save"] == "0"){
        return _mysql_query("INSERT INTO bbcode VALUES (NULL,'".$_POST["bbcode_hint"]."','".$_POST["bbcode_in"]."','".$_POST["bbcode_out"]."','".$_POST["bbcode_display"]."', '".$_POST['bbcode_attrib']."')");
    }else{
        return _mysql_query("UPDATE bbcode SET bbcode_hint='".$_POST["bbcode_hint"]."', bbcode='".$_POST["bbcode_in"]."', bbcode_html='".$_POST["bbcode_out"]."', bbcode_show='".$_POST["bbcode_display"]."', attrib_func='".$_POST['bbcode_attrib']."' WHERE bbcode_id='".$_GET["save"]."'");
    }
}