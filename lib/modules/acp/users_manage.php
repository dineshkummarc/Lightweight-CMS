<?php
class users_manage{
    var $module_info = array(
        'title' => "Users",
        'MODULES' => array(
            array('name' => 'manage_users','permissions' => 'a_manage_users'),
        )
    );

function get_ranks(){
    return array_to_combo(get_table_contents("ranks"),"name","id");
}

function get_groups(){
    return array_to_combo(get_table_contents("ranks"),"name","id");
}

 function addMissingParams(){
     if(!isset($_POST['show_facebook'])){
         $_POST['show_facebook'] = '0';
     }
     if(!isset($_POST['show_email'])){
         $_POST['show_email'] = '0';
     }
     if(!isset($_POST['founder'])){
         $_POST['founder'] = '0';
     }
     if(!isset($_POST['active'])){
         $_POST['active'] = '0';
     }
 }

function update_current_user(){
    global $current_user;
    $error = "ok";
    log_event('ADMINISTRATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "MANAGE USER", 'Altered user '. $_POST['username']);
    if($_POST['founder'] == "1" && $current_user['is_founder'] == "0"){
        return "Only founders can add new founders.";
    }
    
    if (!user_validate_name($_POST['username'])){return 8;}
    $change_pass = ($_POST['password'] == $_POST['password_confirm'] && $_POST['password'] != '');
    $password_mismatch = ($_POST['password'] != $_POST['password_confirm'] && $_POST['password'] != '');
    $change_mail = user_validate_email($_POST['email']);
    $change_facebook = secure_facebook($_POST['facebook']);
    $cover = secure_url($_POST['cover']);
    $this->addMissingParams();
    if($password_mismatch){$error = "Password mismatch";}
    _mysql_prepared_query(array(
        "query" => "UPDATE post SET username=:username WHERE user_id=:uid", // update cache
        "params" => array(
            ":username" => $_POST['username'],
            ":uid" =>  $_GET['uid']
        )
    ));
    $cmd = "UPDATE users ";
    $update = "SET user_avatar=:avatar, user_show_facebook=:show_fb, user_show_mail=:show_mail, user_signature=:signature, username=:username, user_default_group=:group, user_warn=:warn, user_rank=:rank, user_founder=:is_founder, cover=:cover, about=:about, cover_h_offset=:cover_h_offset, active=:active, ";
    $cmd_end = "WHERE user_id=:uid";
    $salt = random_string(10);
    if($change_pass){
        $update .= "user_password=:password, ";
        $update .= "salt=:salt, ";
    }
    if($change_mail){$update .= "user_email=:email, ";}
    if($change_facebook){$update .= "user_facebook=:facebook, ";}
    $update = StringTrimRight($update,2);
    $sql=$cmd."\n".$update."\n".$cmd_end;
    $result = _mysql_prepared_query(array(
        "query" => $sql,
        "params" => array(
            ":username" => $_POST['username'],
            ":avatar" => $_POST['avatar'],
            ":show_fb" => $_POST['show_facebook'],
            ":show_mail" => $_POST['show_email'],
            ":warn" => $_POST['warn'],
            ":is_founder" => $_POST['founder'],
            ":signature" => $_POST['signature'],
            ":uid" => $_POST['uid'],
            ":password" => encrypt($_POST['password'].$salt),
            ":salt" => $salt,
            ":rank" => $_POST['rank'],
            ":email" => $_POST['email'],
            ":facebook" => $_POST['facebook'],
            ":group" => $_POST['group'],
            ":cover" => $cover,
            ":about" => $_POST['about'],
            ":cover_h_offset" => $_POST['cover_h_offset'],
            ":active" => $_POST['active']
            )
        ));
    
    if($_POST['group'] > 0){
        user_set_default_group($_POST['uid'], $_POST['group']);
    }
    
    if(!$result){$error .= "<br>Query error";}

    return $error;
}

function update_user_groups(){
    $groups = StringTrimRight($_POST['groups'], 1);
    $parts = explode(":", $groups);
    $parts[1] = explode("|", $parts[1]);
    $values = [];
    $parts[0] = intval($parts[0]);
    for ($i = 0; $i < count($parts[1]); $i++) {
        $values[] = array($parts[0], intval($parts[1][$i]), "1");
    }
    if(sizeof($values) == 0){
        return 0;
    }

    _mysql_prepared_query(array(
        "query" => "DELETE FROM user_groups WHERE user_id=:uid",
        "params" => array(
            ":uid" => $parts[0]
        )
    ), true);
    _mysql_prepared_query(array(
        "query" => "INSERT INTO user_groups VALUES :values",
        "params" => array(
            ":values" => $values
        )
    ), true);
    $group = user_get_last_group($parts[0]);
    user_set_default_group($parts[0], $group);
}


    function main($module){
        global $current_user, $language;
        if(strlen($_GET['u'])>0){
            $user_info = user_get_info_by_name($_GET['u']);
            if($current_user['is_founder'] == "0" && $user_info['user_founder'] == "1"){
                $this->template = "failure_module";
                $this->vars=array(
                    'SUCCESSMSG' => $language['notifications']['user_founder_error']."<br> <a href=\"".str_replace("&mode=updateuser","",$_SERVER['HTTP_REFERER'])."\">back</a> "
                );
                return;
            }
        }
        switch($module){
            case 'manage_users':
                $selected_user = array();
                if(isset($_GET['mode'])){
                    if($_GET['mode'] == "listusers"){
                        $page = 0;
                        if (is_numeric($_GET["page"])){$page = $_GET["page"];}
                        $result = _mysql_query("SELECT value FROM general WHERE setting = 'users_per_page'");
                        $limit = _mysql_result($result, 0);
                        $arr = user_list($limit*$page,$limit+($limit*$page));
                        $result = _mysql_query("SELECT COUNT(*) FROM users");
                        $user_count = _mysql_result($result, 0);
                        $user_count = "var usercount = ".$user_count.";\nvar pagelimit = ".$limit.";\nvar currentpage = ".$page.";\n";
                        $user_list = array_to_js($arr,'user_list',true);
                        die($user_count.$user_list);
                    }elseif($_GET['mode'] == 'updateuser'){
                        $ret = $this->update_current_user();
                        if($ret != "ok"){
                            $this->template = "failure_module";
                            $this->vars=array(
                                'SUCCESSMSG' => $ret."<br> <a href=\"".str_replace("&mode=updateuser","",$_SERVER['HTTP_REFERER'])."\">back</a> "
                            );
                        }else{
                            $this->template = "success_module";
                            $this->vars=array(
                                'SUCCESSMSG' => $language['notifications']['user_update']."<br> <a href=\"".str_replace("&mode=updateuser","",$_SERVER['HTTP_REFERER'])."\">back</a> "
                            );
                        }
                        break;
                    }elseif($_GET['mode'] == 'updategroups'){
                        if(has_permission($current_user['permissions']['global'], "a_manage_groups") === true){
                            $this->template = "success_module";
                            $ret = $this->update_user_groups();
                            $this->vars=array(
                                'SUCCESSMSG' => $language['notifications']['user_update_groups']."<br> <a href=\"".str_replace("&mode=updateuser","",$_SERVER['HTTP_REFERER'])."\">back</a> "
                            );
                        }else{
                            $this->template = "fail_module";
                            $this->vars=array(
                                'SUCCESSMSG' => $language['notifications']['user_update_groups_fail']." <br> <a href=\"".str_replace("&mode=updateuser","",$_SERVER['HTTP_REFERER'])."\">back</a> "
                            );
                        }

                        break;
                    }
                }
                if(isset($_GET['uid'])||isset($_GET['u'])){
                    $uid = $_GET['uid'];
                    if ($uid == ""){$uid = user_get_id_by_name($_GET['u']);}
                    $selected_user = user_get_info_by_id($uid);
                    $selected_user = $selected_user[0];
                    $selected_user['user_show_facebook'] = int_to_checked($selected_user['user_show_facebook']) ;
                    $selected_user['user_show_mail'] = int_to_checked($selected_user['user_show_mail']);
                    $selected_user['user_founder'] = int_to_checked($selected_user['user_founder']);
                    $selected_user['active'] = int_to_checked($selected_user['active']);
                    $selected_user['uid'] = $uid;
                    if(has_permission($current_user['permissions']['global'], "a_manage_groups") === true){
                        $groups_modifiers = '<form id="gm"><h2>Manage user groups</h2>';
                        $groups_modifiers .= "<table>";
                        $groups_modifiers .= "<tr><td>Group name</td><td>Membership</td></tr>";
                        $group_list = groups_list();
                        $user_groups = user_get_groups($uid);
                        for ($i = 0; $i < count($group_list); $i++) {
                            $selected="";
                            if(in_array($group_list[$i]['id'], $user_groups)){
                                $selected = "checked";
                            }
                            $groups_modifiers .= '<tr><td>'.$group_list[$i]['name'].'</td><td><input type="checkbox" name="'.$group_list[$i]['id'].'" '.$selected.'></td></tr>';
                        }
                        $groups_modifiers .= "</table></form>";
                        $groups_modifiers .= '<button type="button" onclick="submitGroups();">Set groups</button> ';
                        
                    }
                }
                $this->page_title = $language['module_titles']['manage_user_select'];
                $this->template = "users_manage";
                $this->vars=array(
                    'VAR' => 'log',
                    'SELECTEDUSER' => $selected_user,
					'RANK' => $this->get_ranks(),
					'GROUP' => $this->get_groups(),
					'uid' => $uid,
                    'GROUP_TABLE' => $groups_modifiers
                );
            break;
        }
    }
}