<?php
$library_path = ".";
function proccess_registration(){
    global $TABS_DATA_JS,$FILE_PATH,$CURRENT_MODULE,$notification,$login_form,$site_settings, $language ;
    $TABS_DATA_JS = "var Tabsdata = [];Tabsdata[\"0\"] = [];Tabsdata[\"0\"][\"link\"] = \"15\";Tabsdata[\"0\"][\"text\"] = \"register\";";
    $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/register.html';
    
    $_POST['user_email'] = secure_input($_POST['user_email']);
    if(test_email_ban($_POST)){
        $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/failure_module.html';
        $notification = $language['notifications']['email_banned']."<br> <a href=\"#\" onclick=\"history.go(-1)\">Retry</a> or <a href=\"../\">Go to board index</a>";
        return false;  
    }
    if($site_settings['user_pass_max_len'] < strlen($_POST['user_password'])
            || $site_settings['user_pass_min_len'] > strlen($_POST['user_password']) ){
        $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/failure_module.html';
        $notification = sprintf($language['notifications']['password_wrong_length'], $site_settings['user_pass_min_len'], $site_settings['user_pass_max_len'])."<br> <a href=\"#\" onclick=\"history.go(-1)\">Retry</a> or <a href=\"../\">Go to board index</a>";
        return false;
    }
    if($_POST['user_password'] != $_POST['user_password_confirm']){
        $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/failure_module.html';
        $notification = $language['notifications']['password_mismatch']."<br> <a href=\"#\" onclick=\"history.go(-1)\">Retry</a> or <a href=\"../\">Go to board index</a>";
        return;
    }
    if($_POST['user_password'] == "" || $_POST['username'] == ""|| $_POST['user_email'] == ""){
        $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/failure_module.html';
        $notification = $language['notifications']['missing_fields']."<br> <a href=\"#\" onclick=\"history.go(-1)\">Retry</a> or <a href=\"../\">Go to board index</a>";
        return false;
    }
    if(user_get_id_by_name($_POST['username'])!=""){
        $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/failure_module.html';
        $notification = $language['notifications']['user_exists']."<br> <a href=\"#\" onclick=\"history.go(-1)\">Retry</a> or <a href=\"../\">Go to board index</a>";
        return false;
    }
    if($site_settings['username_max_len'] < strlen($_POST['username'])
        || $site_settings['username_min_len'] > strlen($_POST['username']) ){
        $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/failure_module.html';
        $notification = sprintf($language['notifications']['username_wrong_length'], $site_settings['username_min_len'], $site_settings['username_max_len'])."<br> <a href=\"#\" onclick=\"history.go(-1)\">Retry</a> or <a href=\"../\">Go to board index</a>";
        return false;
    }
    if($_POST['agreement']!="1" || !isset($_POST['agreement'])){
        $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/failure_module.html';
        $notification = $language['notifications']['terms_not_accepted']."<br> <a href=\"#\" onclick=\"history.go(-1)\">Retry</a> or <a href=\"../\">Go to board index</a>";
        return false;
    }
    if($_POST['cmp']!="1"){
        $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/failure_module.html';
        $notification = $language['notifications']['not_human']."<br> <a href=\"#\" onclick=\"history.go(-1)\">Retry</a> or <a href=\"../\">Go to board index</a>";
        return false;
    }
    
    if($_POST['website']!=""){
        $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/failure_module.html';
        $notification = $language['notifications']['not_human']."<br> <a href=\"#\" onclick=\"history.go(-1)\">Retry</a> or <a href=\"../\">Go to board index</a>";
        _mysql_query("INSERT INTO bans VALUES(NULL, 0, '".$_SERVER['REMOTE_ADDR']."', '', ".time().", '".(time()+$site_settings['robot_ban_length'])."', 'Website filled for registration','Unhandled exception at 0xC0000005', 0)");
        log_event("USER", "system", $_SERVER['REMOTE_ADDR'], "register", "ip banned for filling filling in website.");
        return false;
    }
    if(!mkuser()){
        $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/failure_module.html';
        $notification = $language['notifications']['registration_failed']." <br> <a href=\"#\" onclick=\"history.go(-1)\">Retry</a> or <a href=\"../\">Go to board index</a>";
        return false;
    }else{
        //dbg("1");
        if ($site_settings['require_email_validation']) {
            //dbg("2");
            $result = _mysql_query("SELECT * FROM activation WHERE user_id='".  user_get_id_by_name($_POST['username'] )."'");
            $arr = _mysql_fetch_assoc($result);
            //dbg("3");
            send_activation_mail($_POST['username'], $_POST['user_email'], $arr['user_id'], $arr['activation_key']);
            //dbg("2");
        }
        $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/success_module.html';
        $reg_success = $language['notifications']['registration_success'];
        if($site_settings['require_email_validation'] == "1"){
            $reg_success .= sprintf($language['notifications']['confirmation_required'],$_POST['user_email']);
        }
        $notification = $language['notifications']['registration_success']."<br> <a href=\"#\" onclick=\"history.go(-1)\">Retry</a> or <a href=\"../\">Go to board index</a>";
        login($_POST['username'],$_POST['user_password'],true, 0);
        //_sendcookie();
        $login_form = get_login_form();
        group_add_member(3, $_POST['username'], 1, true);//add user to registered users
        redirect(5, '../');
    }
    return false;
}


function send_activation_mail($user_name, $to, $user_id, $key){
    global $site_settings;
    $link = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."?a=activate&uid=".$user_id."&key=".$key;
    $subject = 'Your account have been created';
    //define the message to be sent. Each line should be separated with \n
    $message = "Hello,<br><br>Thank you for registering<br>To activate your account, simply click on the following link:<br><a href=\"".$link."\">".$link."</a><br><br>Your account details:<br>username: ".$user_name."<br>User id: ".$user_id."<br>Validation Key: ".$key."<br>";
    //define the headers we want passed. Note that they are separated with \r\n
    $headers = "From: ".$site_settings['webmaster']."\r\nReply-To: ".$site_settings['webmaster']."\r\n";
    $headers .= 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
    //send the email
    //dbg($to, $subject, $message, $headers );
    mail( $to, $subject, $message, $headers );
}

$CURRENT_MODULE = 'ucp';
include_once "./control_panel.php";
if(!isset($_GET['a'])){
    $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/failure_module.html';
    $notification = $language['notifications']['warn_invalid_action'];
    load($MAIN_PAGE[$CURRENT_MODULE], $PAGE_TITLE[$CURRENT_MODULE], $FILE_PATH[$CURRENT_MODULE]);
    exit();
}

$cond = $_GET['a'] == 'login' || $_GET['a'] == 'signin' || $_GET['a'] == 'register' || $_GET['a'] == 'registerconfirm' || $_GET['a'] == 'toc' || $_GET['a'] == 'activate' || $_GET['a'] == 'resend';
if($cond){
    $NO_MODULE = true;
}
if($_GET['a'] == 'login'){
    $MODULE_TITLE = 'Login';
    $register_link = $site_settings['allow_registraton'] == '1' ?  ' or <a href="'.$library_path.'/ucp.php?a=register">Register</a>' : "";
    $TABS_DATA_JS = "var Tabsdata = [];Tabsdata[\"0\"] = [];Tabsdata[\"0\"][\"link\"] = \"login\";Tabsdata[\"0\"][\"text\"] = \"login\" ";
    $TABS_DATA_JS = $site_settings['allow_registraton'] == '1' ? $TABS_DATA_JS . "; Tabsdata[\"1\"] = [];Tabsdata[\"1\"][\"link\"] = \"register\";Tabsdata[\"1\"][\"text\"] = \"register\"" : $TABS_DATA_JS;
    $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/login.html';
}
if($IS_BANNED){
    if($INACTIVE){
        $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/success_module.html';
        $notification = sprintf($language['notifications']['user_inactive'], user_get_id_by_name($_POST['username']))."<br> <a href=\"../\">Go to board index</a>";
    }else{
        $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/failure_module.html';
        $notification = $language['notifications']['benned_ip']."<br> <a href=\"../\">Go to board index</a>";
    }
}else{
    
    if($_GET['a'] == 'signin'){
        $MODULE_TITLE = 'Sign in';
        $user = login($_POST['username'],$_POST['password'],Checkboxto_bool($_POST['remember']), checkbox_to_int($_POST['anonymous']));
        //_sendcookie();
        if(!$IS_BANNED){
            if($user['user_id'] > 1){
                $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/success_module.html';
                $notification = $language['notifications']['login_success']."<br> <a href=\"../\">Go to board index</a>";
                redirect(5, '../');
            }else{
                $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/failure_module.html';
                $notification = $language['notifications']['wrong_pw_user']."<br> <a href=\"#\" onclick=\"history.go(-1)\">Retry</a> or <a href=\"../\">Go to board index</a>";
            }
        }else{
            if($INACTIVE){
                $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/success_module.html';
                $notification = sprintf($language['notifications']['user_inactive'], user_get_id_by_name($_POST['username']))."<br> <a href=\"../\">Go to board index</a>";
            }else{
                $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/failure_module.html';
                $notification = $language['notifications']['login_fail_ip_ban']."<br><a href=\"../\">Go to board index</a>";
            }
        }
    }

    if($_GET['a'] == 'logout'){
        $MODULE_TITLE = 'Logout';
        session_delete($_COOKIE['Session']);
        $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/success_module.html';
        $notification = $language['notifications']['logout_success']."<br> <a href=\"../\">Go to board index</a>";
        redirect(5, '../');
    }
    
    if($_GET['a'] == 'activate'){
        $MODULE_TITLE = 'Activate';
        if(user_get_name_by_id($_GET['uid'])){
            $result = _mysql_query("SELECT * FROM activation WHERE user_id='".$_GET['uid']."' AND activation_key='".$_GET['key']."'");
            $arr = _mysql_fetch_assoc($result);
            if(is_array($arr)){
                $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/success_module.html';
                $notification = $language['notifications']['activation_success']."<br> <a href=\"../\">Go to board index</a>";
                _mysql_query("UPDATE users SET active=1 WHERE user_id='".$arr['user_id']."'");
                _mysql_query("DELETE FROM activation WHERE activation_key='".$arr['activation_key']."'");
            }else{
                $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/failure_module.html';
                $notification = $language['notifications']['activation_fail']."<br> <a href=\"../\">Go to board index</a>";
            }
        }else{
            $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/failure_module.html';
            $notification = $language['notifications']['activation_fail']."<br> <a href=\"../\">Go to board index</a>";
        }
    }
    
    if($_GET['a'] == 'toc'){
        $MODULE_TITLE = 'Terms of Use';
        $FILE_PATH[$CURRENT_MODULE] = './toc.html';
    }

    if($_GET['a'] == 'resend'){
        $MODULE_TITLE = "Resend Email";
        $result = _mysql_query("SELECT activation_key FROM activation WHERE user_id = '".$_GET['uid']."'");
        $key = _mysql_result($result, 0);
        if(!$key){
            $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/success_module.html';
            $notification = $language['notifications']['user_no_approval']."<br> <a href=\"../\">Go to board index</a>";
        }else{
            $user_info = user_get_info_by_id($_GET['uid']);
            //dbg($user_info);
            send_activation_mail($user_info[0]['username'], $user_info[0]['user_email'], $_GET['uid'], $key);
            $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/success_module.html';
            $notification = sprintf($language['notifications']['user_approval_sent'], $user_info[0]['user_email'])."<br> <a href=\"../\">Go to board index</a>";
        }
    }
    
    if($_GET['a'] == 'register'){
        $MODULE_TITLE = "Register";
        if($site_settings['allow_registraton'] == '1'){
            $TABS_DATA_JS = "var Tabsdata = [];Tabsdata[\"0\"] = [];Tabsdata[\"0\"][\"link\"] = \"login\";Tabsdata[\"0\"][\"text\"] = \"login\" ";
            $TABS_DATA_JS = $site_settings['allow_registraton'] == '1' ? $TABS_DATA_JS . "; Tabsdata[\"1\"] = [];Tabsdata[\"1\"][\"link\"] = \"register\";Tabsdata[\"1\"][\"text\"] = \"register\"" : $TABS_DATA_JS;
            $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/register.html';
        }else{
            $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/failure_module.html';
            $notification = $language['notifications']['registration_not_allowed']."<br> <a href=\"../\">Go to board index</a>";
            redirect(5, '../');
        }
    }
    if($_GET['a'] == 'registerconfirm'){
        $MODULE_TITLE = 'Confirm Registration';
        if($site_settings['allow_registraton'] == '1'){
            $fail_count = _mysql_query("SELECT COUNT(*) FROM login_attempts WHERE ip='".$_SERVER['REMOTE_ADDR']."' AND (".time()." - time) < 600");
            $attempts = 0;
            if($fail_count){
                $attempts = _mysql_result($fail_count,0);
            }
            if($attempts > $site_settings['max_registration_attempts']){
                _mysql_query("INSERT INTO bans VALUES(NULL, 0, '".$_SERVER['REMOTE_ADDR']."', '', ".time().", '".(time()+$site_settings['registration_ban_length'])."', 'Too many failed registration attempts','Too many failed registration attempts', 0)");
                log_event("USER", $name, $_SERVER['REMOTE_ADDR'], "login", "ip banned for failed attempts.");
            }else{
                if(!proccess_registration()){
                    _mysql_query("INSERT INTO login_attempts VALUES ('frm_registration', '".time()."', '".$_SERVER['REMOTE_ADDR']."', '".$_SERVER['HTTP_USER_AGENT']."');");
                }
            }
        }else{
            $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/failure_module.html';
            $notification = $language['notifications']['registration_not_allowed']."<br> <a href=\"../\">Go to board index</a>";
            redirect(5, '../');
        }
    }

}

render_forum_path();

    if($cond){
        $menu_data_js = 'var ACP_LeftMenu = [];';
        load($MAIN_PAGE[$CURRENT_MODULE], $PAGE_TITLE[$CURRENT_MODULE], $FILE_PATH[$CURRENT_MODULE]);
    }elseif($current_user['uid'] > 1){
        load($MAIN_PAGE[$CURRENT_MODULE], $PAGE_TITLE[$CURRENT_MODULE], $FILE_PATH[$CURRENT_MODULE]);
    }else{
        $FILE_PATH[$CURRENT_MODULE] = '../theme/'.$site_settings['template'].'/ucp/failure_module.html';
        $notification = $language['notifications']['not_authorized'];
        load($MAIN_PAGE[$CURRENT_MODULE], $PAGE_TITLE[$CURRENT_MODULE], $FILE_PATH[$CURRENT_MODULE]);
    }
?>