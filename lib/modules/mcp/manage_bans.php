<?php
class manage_bans{
    var $module_info = array(
        'title' => "Manage Modules",
        'MODULES' => array(
            array('name' => 'manage_ip_bans','permissions' => 'm_manage_bans'),
            array('name' => 'manage_user_bans','permissions' => 'm_manage_bans'),
            array('name' => 'manage_email_bans','permissions' => 'm_manage_bans')
        )
    );
    
    function parse_end($end) {
        if($end == "day"){
            return time() + 3600*24;
        }else if($end == "week"){
            return time() + 3600*24*7;
        }else if($end == "month"){
            return time() + 3600*24*30; 
        }else{
            return strtotime($end); 
        }
    }
    
    function add_ban() {
        global $current_user;
        if(isset($_POST['ip_address'])){
            _mysql_query("INSERT INTO bans VALUES(NULL,'','".$_POST['ip_address']."', '', '".time()."', '".$this->parse_end($_POST['end'])."', '".$_POST['reason']."', '".$_POST['reason_to_banned']."', '".$current_user['uid']."')");
            log_event('MODERATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "BAN ip", 'Banned ip '.$_POST['ip_address']);
        }else if(isset($_POST['username'])){
            _mysql_query("INSERT INTO bans VALUES(NULL,'".  user_get_id_by_name($_POST['username'])."','', '', '".time()."', '".$this->parse_end($_POST['end'])."', '".$_POST['reason']."', '".$_POST['reason_to_banned']."', '".$current_user['uid']."')");
            log_event('MODERATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "BAN USER", 'Banned user '.$_POST['username']);
        }else{
            _mysql_query("INSERT INTO bans VALUES(NULL,'','', '".$_POST['email']."', '".time()."', '".$this->parse_end($_POST['end'])."', '".$_POST['reason']."', '".$_POST['reason_to_banned']."', '".$current_user['uid']."')");
            log_event('MODERATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "BAN email", 'Banned email '.$_POST['email']);
        }      
    }

    function get_bans($type){
        $type_str = 'email'; 
        if($type == "user"){
           $type_str = 'banned_user_id'; 
        }else if($type == "ip"){
            $type_str = 'ip_address'; 
        }
        if($type_str  == 'email' || $type_str == 'ip_address'){
            $bans = get_table_contents("", '', '', false, "SELECT *, users.username FROM bans, users WHERE bans.".$type_str." != '' AND users.user_id = banned_by",array('end_at','start_at'));
        }else{
            $bans = get_table_contents("", '', '', false, "SELECT *, users.username, u2.username AS Bannedname FROM bans, users, users AS u2 WHERE bans.".$type_str." != '' AND users.user_id = banned_by AND u2.user_id = banned_user_id",array('end_at','start_at'));
        }
        $str = "";
        if($type_str == 'banned_user_id'){
            $type_str = 'Bannedname';
        }
        for($i = 0; $i < count($bans); $i++){
            $class = $bans[$i]['end_at_timestamp']  < time() ?  "expiredban" : "activeban";
            $str .= '<tr><td class="'.$class.'">'.$bans[$i][$type_str].'</td><td class="'.$class.'">'.$bans[$i]['start_at'].'</td><td class="'.$class.'">'.$bans[$i]['end_at'].'</td><td class="'.$class.'">'.$bans[$i]['reason'].'</td><td class="'.$class.'">'.$bans[$i]['reason_to_banned'].'</td><td class="'.$class.'">'.$bans[$i]['username'].'</td><td class="'.$class.'"><span onclick="deleteBan('.$bans[$i]['ban_id'].');">Delete</span></td></tr>';
        }
        return $str;
    }
            
    
    function main($module){
        global $current_user, $language;
        switch($module){
            case 'manage_ip_bans':
                if($_GET['mode'] == "add_ban"){
                   $this->add_ban();
                   $this->template = "success_module";
                   $this->vars=array(
                       'SUCCESSMSG' => $language['notifications']['ban_ip'].'<br><a href="?a='.$_GET['a'].'">Go back</a>'
                   );
                }else if($_GET['mode'] == "delete"){
                    _mysql_query("DELETE FROM bans WHERE ban_id = '".$_POST['ban_id']."'");
                    log_event('MODERATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "UNBAN ip", 'Unbanned ip');
                    die("ok");
                }else{
                    $this->get_bans("ip");
                    $this->page_title = $language['module_titles']['manage_ip_bans'];
                    $this->template = "manage_bans";
                    $this->vars=array(
                        'BANNABLE' => "ip address",
                        'BANNABLE_NAME' => "ip_address",
                        'bans' => $this->get_bans('ip'),
                    );
                }
            break;
            case 'manage_user_bans':
                if($_GET['mode'] == "add_ban"){
                    $user_info = user_get_info_by_name($_POST['username']);
                    if($current_user['is_founder'] == "0" && $user_info['user_founder'] == "1"){
                        $this->template = "failure_module";
                        $this->vars=array(
                            'SUCCESSMSG' => $language['notifications']['ban_user_founder'].'<br><a href="?a='.$_GET['a'].'">Go back</a>'
                        );
                    }else{
                        $this->add_ban();
                        $this->template = "success_module";
                        $this->vars=array(
                            'SUCCESSMSG' => $language['notifications']['ban_user'].'<br><a href="?a='.build_url_relative(array('id','a')).'">Go back</a>'
                        );
                    }
                }else if($_GET['mode'] == "delete"){
                    _mysql_query("DELETE FROM bans WHERE ban_id = '".$_POST['ban_id']."'");
                    log_event('MODERATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "UNBAN ip", 'Unbanned user');
                    die("ok");
                }else{
                    $this->page_title = $language['module_titles']['manage_user_bans'];
                    $this->template = "manage_bans";
                    $this->vars=array(
                        'BANNABLE' => "User name",
                        'BANNABLE_NAME' => "username",
                        'bans' => $this->get_bans('user'),
                    );
                }
            break;
            case 'manage_email_bans':
                if($_GET['mode'] == "add_ban"){
                   $this->add_ban();
                   $this->template = "success_module";
                   $this->vars=array(
                       'SUCCESSMSG' => $language['notifications']['ban_email'].'<br><a href="?a='.$_GET['a'].'">Go back</a>'
                   );
                }else if($_GET['mode'] == "delete"){
                    _mysql_query("DELETE FROM bans WHERE ban_id = '".$_POST['ban_id']."'");
                    log_event('MODERATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "UNBAN email", 'Unbanned email');
                    die("ok");
                }else{
                    $this->page_title = $language['module_titles']['manage_email_bans'];
                    $this->template = "manage_bans";
                    $this->vars=array(
                        'BANNABLE' => "email address",
                        'BANNABLE_NAME' => "email",
                        'bans' => $this->get_bans('email'),
                    );
                }
            break;
        }
    }
}