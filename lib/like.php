<?php
error_reporting(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR | E_WARNING | E_PARSE | E_USER_WARNING | E_USER_ERROR);

if (!function_exists("ConnectdataBase")){
include_once("../settings.php");
include_once("./funcs.php");
include_once("./lng/english.php");
include_once "../globals.php";
include_once("./groups.php");
include_once("./users.php");
include_once("./tobase.php");
include_once("./view_forum.php");
include_once("./view_topic.php");
include_once("./modules.php");
include_once("./permissions.php");
include_once "./bbcode.php";
}
if (!$GLOBALS_DEFINED == true){include_once("../globals.php");}
if (!function_exists("CreateUser")){include_once("./login.php");}

sanitarize_input();

define_user();
$library_path = "./lib";
$forum_links = get_allowed_forums();

$forum_id_const = $forum_links[0]['forum_id'];
if(isset($_GET['p'])){
    $forum_id_const = post_get_forum($_GET['p']);
}
if(!forum_exists($forum_id_const)){
    display_error("Error","Forum does not exist");
}


$current_user['permissions'][$forum_id_const] = permissions_to_string(user_get_permissions($current_user['uid'],$forum_id_const));

if(!has_permission($current_user['permissions'][$forum_id_const],"f_read_forum")){
    die("forum_denied");
}


/*
 * POST
 * a - action
 * p - post
 * Editor
 * report_msg - p6hjus
 * GET
 * edit
 * new
 * approve
 * viewreport
 * report
 * closereport
 * delete confirm=yes
 */

function Like_post($post,$user, $like){
    $result = _mysql_prepared_query(array(
        "query" => "SELECT COUNT(*) AS c FROM likes WHERE post_id=:pid AND user_id=:uid",
        "params" => array(
            ":pid" => $post,
            ":uid" => $user,
        )
    ));
    $has_liked = _mysql_result($result, 0);
    if($has_liked == "0"){
        if($like=='unlike'){
            die("unlike_denied");
        }else{
            _mysql_prepared_query(array(
                "query" => "INSERT INTO likes VALUES (:pid, :uid, :time)",
                "params" => array(
                    ":pid" => $post,
                    ":uid" => $user,
                    ":time" => time()
                )
            ));
            die("like_success");
        }
    }else{
        if($like=='unlike'){
            _mysql_prepared_query(array(
                "query" => "DELETE FROM likes WHERE post_id=:pid AND user_id=:uid",
                "params" => array(
                    ":pid" => $post,
                    ":uid" => $user,
                )
            ));
            die("unlike_success");
        }else{
            die("like_denied");
        }
    }
}


if(isset($_GET['a'])){
    $post = post_get_info($_GET['p']);
    $not_edit_locked = $post['edit_locked']=='0' ? "true": "false";
    switch($_GET['a']){
        case 'like' :
            if($current_user['uid'] == 1){die("like_denied_guest");} // allow guests to see likes but not to like themselves
            if(has_permission($current_user['permissions'][$forum_id_const],'f_like')){
                Like_post($_GET['p'],$current_user['uid'],$_GET['a']);
                die("like_success");
            }else{
                if($current_user['uid'] == 1){
                    die("like_denied_guest");
                }else{
                    die("like_denied");
                }
            }
            break;
        case 'unlike':
            if($current_user['uid'] == 1){die("unlike_denied_guest");}
            if(has_permission($current_user['permissions'][$forum_id_const],'f_like')){
                Like_post($_GET['p'],$current_user['uid'],$_GET['a']);
                die("like_success");
            }else{
                if($current_user['uid'] == 1){
                    die("unlike_denied_guest");
                }else{
                    die("unlike_denied");
                }
            }
            break;
        default:
            die("unknown_error");
            break;
    }
}

die("none");
