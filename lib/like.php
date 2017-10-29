<?php
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
 * *************
 */

/*if(isset($_POST['Editor'])){
    if(!has_permission($current_user['permissions'][$comments],"f_can_reply")){
        die("You do not have permission post comments");
    }else{
        $sql = "INSERT INTO post VALUES (NULL,0,$comments,'".$_SERVER['REMOTE_ADDR']."', '".time()."',0,0,'','".$current_user['uid']."',0,0,'',0,0, '".$_POST['Editor']."', '".$_GET['id']."', 1)";
        _mysql_query($sql);
        die("New comment has been posted");
    }
}*/

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
    $result = _mysql_query("SELECT COUNT(*) AS c FROM likes WHERE post_id='".$post."' AND user_id='".$user."'");
    $has_liked = _mysql_result($result, 0);
    if($has_liked == "0"){
        if($like=='unlike'){
            die("unlike_denied");
        }else{
            _mysql_query("INSERT INTO likes VALUES ('".$post."','".$user."','".time()."')");
            die("like_success");
        }
    }else{
        if($like=='unlike'){
            _mysql_query("DELETE FROM likes WHERE post_id='".$post."' AND user_id='".$user."'");
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

?> 
