<?php

$PASSWORD_login = false;
$current_user = false;
$INACTIVE = false;


function define_user(){
    global $current_user;
    if ($current_user['uid'] == 0){
        $user = login($_POST['username'], $_POST['user_password']);
        $current_user['user'] = $user;
        $current_user['uid'] = $user['user_id'];
        $current_user['color'] = $user['user_color'];
        $current_user['groups'] = user_get_groups($current_user['uid'],false);
        $current_user['allowed_forums'] = get_allowed_forums("-1",true);
        $current_user['name'] = $user['username'];
        $current_user['is_founder'] = $user['user_founder'];
        $current_user['permissions']['global'] = permissions_to_string(user_get_permissions($current_user['uid']));
        for($i = 0; $i < count($current_user['allowed_forums']); $i++){
            $current_user['permissions'][$current_user['allowed_forums'][$i]['forum_id']] = permissions_to_string(user_get_permissions($current_user['uid'],$current_user['allowed_forums'][$i]['forum_id']));
        }
        if($current_user['is_founder'] == "1"){
            if(($key = array_search('u_view_only', $current_user['permissions']['global'])) !== false) {
                unset($current_user['permissions']['global'][$key]);
            }
        }
    }
}

function is_crawler($agent){
    $search_bot = Array(
        'Exabot',
        'Googlebot',
        'Yahoo',
        'msnbot',
        'setooz',
        'DotBot',
        'Cityreview',
        'SurveyBot',
        'Twiceler',
        'AddMe',
        'AppEngine-Google',
        'Baiduspider',
        'CatchBot',
        'Comodo-Certificates-Spider',
        'Content Crawler',
        'DLE_Spider.exe',
        'EC2LinkFinder',
        'findfiles.net',
        'findlinks',
        'gold crawler',
        'GSLFbot',
        'ia_archiver',
        'Jyxobot',
        'libwww-perl',
        'Mail.Ru',
        'bingbot',
        'DotBot',
        'MJ12bot',
        'NerdByNature',
        'ScoutJet',
        'SISTRIX',
        'WBSearchBot',
        'YandexBot',
        'YandexFavicon',
        'TwengaBot',
        'Google'
    );
   foreach ($search_bot as $bot)
    {
        if (stristr($agent, $bot))
        {
            return($bot);
        }
    }
    if (
        stristr($agent, "Crawler") ||
        stristr($agent, "Spyder") ||
        stristr($agent, "Search")        
        ) {
            return("UnknownSearchEngine");
    }

    return false;
}

function test_email_ban($user ,$uid = -1, $protect_special = true) {
    global $site_settings, $NOTICE, $IS_BANNED;
    if($user == null || $user['user_email'] == ""){
        return false;
    }
    $ban = _mysql_query("SELECT * FROM bans WHERE end_at > ".time()." AND email='".$user['user_email'] ."' ORDER BY end_at DESC LIMIT 0,1");
    $arr = _mysql_fetch_assoc( $ban );
    if($arr != false){
        if($user['user_id'] > -1 && has_permission(permissions_to_string(user_get_permissions($user['user_id'])),'u_ignore_ban')){
            $NOTICE = "This email address is banned.<br>reason: ".$arr['reason_to_banned']."<br>This ban will expire on ".  date($site_settings['time_format'], $arr['end_at']).".<br>This ban will not affect your current session because you have permission to ignore ban.";
            return false;
        }
        else{
            $NOTICE = "This email address is banned.<br>reason: ".$arr['reason_to_banned']."<br>This ban will expire on ".  date($site_settings['time_format'], $arr['end_at']).".";
            $IS_BANNED = true;
            return true;
        }
    }
    return false;
}

function test_user_ban($user, $protect_special = true) {
    global $site_settings, $NOTICE, $IS_BANNED;
    if($user['user_id'] < 2 && $protect_special){
        return false;
    }
    $ban = _mysql_query("SELECT * FROM bans WHERE end_at > ".time()." AND banned_user_id='".$user['user_id']."' ORDER BY end_at DESC LIMIT 0,1");
    $arr = _mysql_fetch_assoc( $ban );
    if($arr != false){
        if($user['user_id'] > -1 && has_permission(permissions_to_string(user_get_permissions($user['user_id'])),'u_ignore_ban')){
            $NOTICE = "This user is banned.<br>reason: ".$arr['reason_to_banned']."<br>This ban will expire on ".  date($site_settings['time_format'], $arr['end_at']).".<br>This ban will not affect your current session because you have permission to ignore ban.";
            return false;
        }
        else{
            $NOTICE = "This user is banned.<br>reason: ".$arr['reason_to_banned']."<br>This ban will expire on ".  date($site_settings['time_format'], $arr['end_at']).".";
            $IS_BANNED = true;
            return true;
        }
    }
    return false;
}

function test_ip_ban($user, $protect_special = true) {
    global $site_settings, $NOTICE, $IS_BANNED;
    if($user['user_id'] < 2 && $protect_special){
        return false;
    }
    $ban = _mysql_query("SELECT * FROM bans WHERE end_at > ".time()." AND ip_address='".$_SERVER['REMOTE_ADDR']."' ORDER BY end_at DESC LIMIT 0,1");
    $arr = _mysql_fetch_assoc( $ban );
    if($arr != false){
        if ($arr['banned_user_id'] == '0' && $arr['ip_address'] ==  $_SERVER['REMOTE_ADDR']){
            if(has_permission(permissions_to_string(user_get_permissions($user['user_id'])),'u_ignore_ban')){
                $NOTICE = "This ip address is banned.<br>reason: ".$arr['reason_to_banned']."<br>This ban will expire on ".  date($site_settings['time_format'], $arr['end_at']).".<br>This ban will not affect your current session because you have permission to ignore ban.";
                return false;
            }else{
                $NOTICE = "This ip address is banned.<br>reason: ".$arr['reason_to_banned']."<br>This ban will expire on ".  date($site_settings['time_format'], $arr['end_at']).".";
                $IS_BANNED = true;
                return true;
            }
        }
    }
    return false;
}

function test_active($user, $protect_special = true) {
    global $site_settings, $NOTICE, $IS_BANNED, $INACTIVE;
    if($user['user_id'] < 2 && $protect_special){
        return false;
    }

    if($user['active'] == "0"){
        if($user['user_founder'] == "1"){
            $NOTICE = "Your account is currently inactive.<br>This will not affect your session because you are founder of this site.";
            return false;
        } else{
            $INACTIVE = true;
            $NOTICE = "Your account is currently inactive.";
            $IS_BANNED = true;
            return true; 
        }
    }
    return false;
}


function login($user_name = '',$password = '',$remember = false, $hide = 0){
    $crawler = is_crawler($_SERVER['HTTP_USER_AGENT']);
    if($crawler){return user_get_info_by_name($crawler);}
    global $IS_BANNED;
    $user = false;
    if($_GET['a'] != "signin"){
        $user = lonin_cookie();
    }
    if(($user == false || $user['user_id'] == 1) && $user_name != '' && $password != ''){
        try{
            $user = login_password($user_name,$password);
        }catch (Exception $e){
            if($e->getCode() == 0){
                $user = user_get_info_by_id(1);
                return $user[0];//return guest
            }
        }
        if ($user['user_id'] > 0){
            session_new($user['user_id'],$remember,$hide);
        }
    }
    if($user != false && test_ip_ban($user) ||
       $user != false && test_email_ban($user) ||
       $user != false && test_user_ban($user) ||
       $user != false && test_active($user)){
        session_new(1,true,false);$user = user_get_info_by_id(1); $user = $user[0];
    }else{
        if($user == false){session_new(1,true,false);$user = user_get_info_by_id(1); $user = $user[0];}//guest
        if(test_ip_ban($user,false)){
            $IS_BANNED = true;
        }
    }
    _mysql_query("UPDATE users SET last_active='".time()."' WHERE user_id='".$user['user_id']."';");
    return $user;
}

function login_password($name,$password){
    global $site_settings, $PASSWORD_login;
    $fail_count = _mysql_query("SELECT COUNT(*) FROM login_attempts WHERE ip='".$_SERVER['REMOTE_ADDR']."' AND (".time()." - time) < 600");
    $attempts = 0;
    if($fail_count){
        $attempts = _mysql_result($fail_count,0);
    }
    if($attempts > $site_settings['max_login_attempts']){
        _mysql_query("INSERT INTO bans VALUES(NULL, 0, '".$_SERVER['REMOTE_ADDR']."', '', ".time().", '".(time()+$site_settings['login_ban_length'])."', 'Too many failed login attempts','Too many failed login attempts', 0)");
        log_event("USER", $name, $_SERVER['REMOTE_ADDR'], "login", "ip banned for failed attempts.");
    }
    $user = user_get_info_by_name($name);
    //dbg($user);
    if($user == false){throw new Exception('User not found',0);};
    $salt = $user['salt'];
    $pass = $user['user_password'];
    if($pass != encrypt($password.$salt)){
        $user = false;
        _mysql_query("INSERT INTO login_attempts VALUES ('".$name."', '".time()."', '".$_SERVER['REMOTE_ADDR']."', '".$_SERVER['HTTP_USER_AGENT']."');");
        throw new Exception('Password does not match',1);
    }
    $PASSWORD_login = true;
    return $user;
}

function session_new($uid,$remember, $hide)
{
    $session_id = random_string(15);
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $start = time();
    $end = time() + 3600*24*360*5; //5 years

    //$query = 'DELETE FROM sessions WHERE user_id = '.$uid; //allow only single login
    //@_mysql_query($query);
    $hideDbVal = '0';
    if($hide){
        $hideDbVal = '1';
    }

    $query = "INSERT INTO sessions VALUES ('".$session_id."', '".$uid."', '".$ip."', '".$user_agent."', '".$start."', '".$end."', ".$hideDbVal.");";
    @_mysql_query($query);
    //dbg($query);

    if(!$remember){
        $end = 0; //cookie will expire at the end of the session (when the browser closes).
    }
    _setcookie("Session", $session_id, $end, "/");
}

function session_delete($sid){
    _setcookie ("Session", "",  time()-3600,"/");
    $query = "DELETE FROM sessions WHERE session_id = '".$sid."'";
    $query2 = "DELETE FROM forum_session WHERE session_id = '".$sid."'";
    _mysql_query($query2);
    return _mysql_query($query);
}

function lonin_cookie(){
    global $site_settings;
    $sid = secure_input($_COOKIE['Session']);
    $query = 'SELECT * FROM sessions, users WHERE session_id = \''.$sid.'\' AND users.user_id = sessions.user_id';
    $result = @_mysql_query($query);
    $user = _mysql_fetch_assoc($result);
    if($user == false){
        session_delete($sid);//Delete invalid cookie
        return false;
    }else{
        if($user['End'] < time()){
            $query = "DELETE FROM sessions WHERE session_id = '".$sid."'"; //Delete expired session
            $query2 = "DELETE FROM forum_session WHERE session_id = '".$sid."'"; //Delete expired session
            @_mysql_query($query);
            @_mysql_query($query2);
            return false;
        }
        if ($user['End'] > time())
        {
            if($site_settings['validate_browser'] == 1 && $user['user_agent'] != $_SERVER['HTTP_USER_AGENT']){
                log_event("USER", $user['username'], $_SERVER['REMOTE_ADDR'], "login", "Browser mismatch");
                return false;
            }
            if($site_settings['validate_ip'] == 1 && $user['ip_address'] != $_SERVER['REMOTE_ADDR']){
                log_event("USER", $user['username'], $_SERVER['REMOTE_ADDR'], "login", "ip mismatch");
                return false;
            }
            return $user;
        }
    }
    //session_delete();//Delete invalid cookie
    return false;
}

function get_login_form()
{
    if(is_crawler($_SERVER['HTTP_USER_AGENT'])){return "";}
    global $form_path,$current_user,$site_settings,$library_path,$register_link;
    $register_link = $site_settings['allow_registraton'] == '1' ?  ' or <a href="'.$library_path.'/ucp.php?a=register">Register</a>' : "";
    if ($current_user['uid'] == 0 || $current_user['uid'] == 1){
        return template_replace(file_get_contents($form_path['FORMS'].'/login.html'), array());
    }else{
        return template_replace(file_get_contents($form_path['FORMS'].'/loggedin.html'), array());
    }
}
?>