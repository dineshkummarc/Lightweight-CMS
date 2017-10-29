<?php
function user_validate_name($name)
{
    if (strlen($name) > 0){
        return true;
    }
    return false;
}

function user_validate_email($mail)
{
    if (strlen($mail) > 3){
        if(strstr($mail,'@')){
            return true;
        }
        return false;
    }
    return false;
}

function user_dec_post_count($uid) {
    $dec_posts = "UPDATE users SET user_post_count = user_post_count - 1 WHERE user_id='".$uid."'";
    _mysql_query($dec_posts);
}

function user_inc_post_count($uid) {
    $inc_posts = "UPDATE users SET user_post_count = user_post_count + 1 WHERE user_id='".$uid."'";
    _mysql_query($inc_posts);
}


function user_get_groups($uid, $pending = true)
{
    $sql_pending = "";
    if(!$pending){
        $sql_pending = "AND user_status > 0 ";
    }
    $groups = get_table_contents("user_groups",array('user_group_id'),'WHERE user_id='.$uid.' '.$sql_pending);
    if(!is_array($groups)){return array();}
    return array_copy_dimension($groups,'user_group_id');
}

function user_get_pending_groups($uid)
{
    $groups = get_table_contents("user_groups",array('user_group_id'),'WHERE user_id='.$uid.' AND user_status = 0 ');
    if(!is_array($groups)){return array();}
    return array_copy_dimension($groups,'user_group_id');
}

function user_get_groups_full($uid, $pending = true)
{
    $sql_pending = "";
    if(!$pending){
        $sql_pending = "AND user_status > 0 ";
    }
    $groups = get_table_contents("","","",false,"SELECT * FROM user_groups, groups WHERE user_id = '".$uid."' AND user_group_id=id ".$sql_pending);
    return $groups;
}


function user_get_permissions($uid,$forum_id = 0){
    global $current_user;
    $groups = $current_user['groups'];
    if($groups == array()){return array();}
    $permission = array();
    /*for ($i = 0; $i < count($groups);$i++)
    {
        $permission = array_merge(group_list_permissions($groups[$i],$forum_id),$permission);
    }*/
    $permission = group_list_permissions($groups,$forum_id);
    if($current_user['is_founder'] == "1" && $forum_id == 0){
        $result = _mysql_query("SELECT permission_id FROM permissions WHERE permission_class = 'administrator'");
        for($i = 0; $i < _mysql_num_rows($result); $i++){
            $permission[] = _mysql_result($result, $i);
        }
    }
    return $permission;
}

/*
function Userhas_permission($permission,$forum_id  = 'global')
{
    global $current_user;
    if($current_user['permissions'][$forum_id] == NULL){return false;}
    if(strstr($permission,'|')){
        $permission = explode('|',$permission);
    }elseif (!is_array($permission)) {
        $permission = array($permission);
    }
    for($i =0;$i < count($permission);$i++){
        if(!array_search($permission[$i],$current_user['permissions'][$forum_id]) && $current_user['permissions'][$forum_id][0]!=$permission[$i]){//crappy fix
            return false;
        }
    }
    return true;

}
 */

function user_get_default_group($uid)
{
    $ret = user_get_info_by_id($uid, array('user_default_group'));
    return $ret[0]['user_default_group'];
}

function user_get_rank($uid)
{
    $ret = user_get_info_by_id($uid, array('user_rank'));
    return $ret[0]['user_rank'];
}

function user_set_default_group($member,$gid)
{
    $group_info = group_get_info_by_id($gid);
    $color = $group_info[0]['color'];
    _mysql_query("UPDATE forum SET last_post_poster_color='".$color."' WHERE last_post_poster_id='".$member."'");
    _mysql_query("UPDATE topic SET poster_color='".$color."' WHERE Poster='".$member."'");
    _mysql_query("UPDATE topic SET last_poster_color='".$color."' WHERE last_poster='".$member."'");
    return group_set_default($member,$gid);
    
}

function user_get_first_group($uid)
{
    if(is_array($uid)){return $uid[0];}
    $groups = get_table_contents("user_groups",array('user_group_id'),'WHERE user_id='.$uid.' LIMIT 1');
    return $groups[0]['user_group_id'];
}

function user_get_name_by_id($uid)
{
    $ret = user_get_info_by_id($uid, array('username'));
    return $ret[0]['username'];
}

function user_get_email_by_id($uid)
{
    $ret = user_get_info_by_id($uid, array('user_email'));
    return $ret[0]['user_email'];
}

function user_get_id_by_name($uid)
{
    if(is_array($uid)){
        return user_get_info_by_id( array_to_upper($uid),array('user_id'), 'upper(username)', 'STR');
    }else{
        $ret = user_get_info_by_id(strtoupper($uid), array('user_id'), 'upper(username)', 'STR');
        return $ret[0]['user_id'];
    }
}

function user_get_last_group($uid)
{
    $result = _mysql_query('SELECT user_group_id FROM user_groups WHERE user_id IN ('.$uid.') ORDER BY user_group_id DESC');
    $group = @_mysql_result($result,0);
    if(!$group){throw new Exception('Failed to get user last group user_get_last_group('.$uid.')');}
    return $group;
}

function user_is_founder($uid){
    $founder = user_get_info_by_id($uid,array('user_founder'));
    $founder = to_bool($founder[0]['user_founder']);
    return $founder;
}


/*  #FUNCTION# ;===============================================================================

name...........: user_get_info_by_id
description ...:
Syntax.........: user_get_info_by_id($uid[, $fields = 'ALL'[, $column = 'user_id']])
Parameters ....: $uid     -
                 $fields - [Optional]
                 $column  - [Optional]
Author ........:
Modified.......:
Remarks .......:
Related .......:
Parameters ....:
Link ..........:
Example .......:
;==========================================================================================*/
function user_get_info_by_id($uid,$fields = 'ALL', $column = 'user_id',$type = 'INT',$debug = false){
    if (is_array($uid)){
        if($type == 'STR'){$condition = implode_string(',',$uid);}
        else{$condition = implode(',',$uid);}
        return get_table_contents("users",$fields,' WHERE '.$column.' IN ('.$condition.')', $debug);
    }else{
        if($type == 'STR'){$uid = "'".$uid."'";}
        return get_table_contents("users",$fields,' WHERE '.$column.' = '.$uid, $debug);
    }
}

function user_get_info_by_name($name){
    $ret = user_get_info_by_id($name,'ALL',"username",'STR');
    return $ret[0];
}



function user_exists($uid)
{
    $result = _mysql_query('SELECT user_id FROM users WHERE user_id = '.$uid);
    return _mysql_num_rows($result);
}

function user_add($name, $password='password', $email='', $join_date=0, $post_count=0, $default_group=3)
{ 
    global $site_settings;
    $active = "1";
    if ($site_settings['require_email_validation']) {
        $active = "0";
    }
    if(user_get_id_by_name($name) > -1){
        return false;
    }
    $salt = random_string(10);
    $query = 'INSERT INTO users (username,user_password,salt,user_email,user_join_date,user_post_count,user_default_group,active, last_active) VALUES (\''.$name.'\',\''.encrypt($password.$salt).'\',\''.$salt.'\',\''.$email.'\','.$join_date.','.$post_count.','.$default_group.', \''.$active.'\', \''.$join_date.'\')';//secure it!!
    $result = _mysql_query($query);
    if ($site_settings['require_email_validation']) {
        _mysql_query("INSERT INTO activation VALUES ('". _mysql_insert_id(). "','". random_string(15)."')");   
    }
    if (!$result){return false;}
    return $result;
}

function user_list($start,$end)
{
    return get_table_contents("users",array('user_id','username','user_email','user_post_count','user_join_date'),'ORDER BY username ASC LIMIT '.$start.', '.$end);
}


?>