<?php
class warn_user{
    var $module_info = array(
        'title' => "Post info",
        'MODULES' => array(
            array('name' => 'warn_user','permissions' => 'm_issue_warning'),
            array('name' => 'list_warn','permissions' => 'm_issue_warning')
        )
    );

    function main($module){
	global $current_user, $notification_back_link, $language;
        $this->page_title = $language['module_titles']['warn_user'];
        switch($module){
            case 'warn_user':
                if($_GET['mode'] == "delete"){
                    if(isset($_POST['warn'])){
                        $result = _mysql_query("SELECT warn.id, users.username,  users.user_id, points FROM warn
LEFT JOIN users
ON warn.user_id = users.user_id
WHERE  warn.id =  '".$_POST['warn']."'
");
                        $arr = _mysql_fetch_assoc($result);
                        if(is_array($arr)){
                            log_event('MODERATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "UNWARN USER", 'Removed '.$arr['points'].' warn from user <a href="../profile.php?uid='.$arr['user_id'].'">'.$arr['username'].'</a>');
                            _mysql_query("DELETE FROM warn WHERE id = '".$_POST['warn']."'");
                            _mysql_query("UPDATE users SET user_warn=user_warn-".$arr['points']." WHERE user_id='".$arr['user_id']."'");
                            $this->template = "success_module";
                            $this->vars=array(
                                'SUCCESSMSG' => $language['notifications']['warn_remove'].$notification_back_link
                            );
                            break;
                        }
                    }
                }
                if(isset($_GET['uid'])){
                    $uinfo = user_get_info_by_id($_GET['uid']);
                    if($uinfo){
                        if(isset($_POST['points'])){
                            $query = "INSERT INTO ".warn." VALUES (NULL, ".$_GET['uid'].", 0, ".time().", '".$_POST['reason']."', ".$_POST['points']." ,".$_POST['verbal'].")";
                            //SELECT post_id, 'time', message, points, type,  post_title  FROM warn, post WHERE $post_info[0]['user_id'] AND post.user_id = warn.user_id
                            $update = "UPDATE ".users." SET user_warn=user_warn+".$_POST['points']." WHERE user_id=".$_GET['uid'];
                            _mysql_query($query);
                            _mysql_query($update);
                            log_event('MODERATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "WARN USER", 'Warned user <a href="../profile.php?uid='.$_GET['uid'].'">'.$uinfo[0]['username'].'</a>');
                            $this->template = "success_module";
                            $this->vars=array(
                                'SUCCESSMSG' => $language['notifications']['warn_success'].'<br> <a href="'.build_url_relative(array('id','a')).'">back</a> '
                            );
                            break;
                        }
                    }else{
                        $this->template = "failure_module";
                        $this->vars=array(
                            'SUCCESSMSG' => $language['notifications']['warn_invalid_post']
                        );
                    }
                    break;
                }
                if(isset($_GET['p'])){
                    $post_info = post_get_info($_GET['p']);
                    if($post_info){
                        if(isset($_POST['points'])){
                            $query = "INSERT INTO warn VALUES (NULL, ".$post_info[0]['user_id'].", ".$post_info[0]['id'].", ".time().", '".$_POST['reason']."', ".$_POST['points']." ,".checkbox_to_int($_POST['verbal']).")";
                            //SELECT post_id, 'time', message, points, type,  post_title  FROM warn, post WHERE $post_info[0]['user_id'] AND post.user_id = warn.user_id
                            $update = "UPDATE ".users." SET user_warn=user_warn+".$_POST['points']." WHERE user_id=".$post_info[0]['user_id'];
                            log_event('MODERATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "WARN USER", 'Warned user <a href="../profile.php?uid='.$post_info[0]['user_id'].'">'.$post_info[0]['username'].'</a> for post <a href="'.$post_info[0]['id'].'">'.$post_info[0]['post_title'].'</a>');
                            _mysql_query($query);
                            _mysql_query($update);
                            $this->template = "success_module";
                            $this->vars=array(
                                'SUCCESSMSG' => $language['notifications']['warn_success']."<br> <a href=\"".$_SERVER['REQUEST_URI']."\">back</a> "
                            );
                            break;
                        }
                        //$columns = array("id","user_id","time","message","points","type","post_title");
                        $warn_js = array_to_js(get_table_contents("","","",false,"SELECT warn.*, COALESCE(post.post_title,'') AS post_title FROM warn LEFT JOIN post ON post.id = warn.post_id AND post.user_id=warn.user_id WHERE warn.user_id=".$post_info[0]['user_id'],array('time')),"warnings",true,true);
                        $post_info[0]['topic'] = topic_get_info($post_info[0]['topic_id']);
                        $post_info[0]['poster'] = user_get_info_by_id($post_info[0]['user_id']);
                        $tags= get_table_contents(bbcode,'ALL');
                        $attachment_html = "";
                        $attachments = post_get_attachments($post_info[0]['id'] );
                        $attachment_html .= "<br><br>";
                        for($j = 0; $j < count($attachments); $j++){
                            $attachment_html  .= '<div class="attachment"><a href="./lib/upload.php?a=download&file='.$attachments[$j]['id'].'">'.$attachments[$j]['file_name'].'</a><br>size: '.$attachments[$j]['size'].' bytes, downloaded '.$attachments[$j]['downloads'].' time(s)</div>';
                        }
                        $post_info[0]['data'] = parse_bbcode($post_info[0]['data'],bbcode_to_regex($tags,'bbcode','bbcode_html'),array(),true,true);
                        $post_info[0]['forum'] = forum_get_info($post_info[0]['forum_id']);
                        $post_info[0]['attach'] = $attachment_html;
                        $this->page_title = $language['module_titles']['warn_user'];
                        $this->template = "warn_post";
                        $this->vars=$post_info;
                        $this->vars['m_change_post_author']=strval(has_permission($current_user['permissions']['global'],'m_change_post_author'));
                        $this->vars['WARN']=$warn_js;
                    }else{
                        $this->template = "failure_module";
                        $this->vars=array(
                            'SUCCESSMSG' => $language['notifications']['warn_invalid_post']
                        );
                    }
                }else{
                    if(isset($_POST['username'])){
                        $uid = user_get_id_by_name($_POST['username']);
                        if($uid){
                            $warn_js = array_to_js(get_table_contents("","","",false,"SELECT ".warn.".*, COALESCE(post.post_title,'') AS post_title FROM warn LEFT JOIN post ON post.id = warn.post_id AND post.user_id=warn.user_id WHERE warn.user_id=".$uid, array('time')),"warnings",true,true);
                            $this->template = "warn_user";
                            $this->vars['WARN']=$warn_js;
                            $this->vars['USER']=user_get_info_by_id($uid);
                        }else{
                            $this->template = "failure_module";
                            $this->vars=array(
                                'SUCCESSMSG' => $language['notifications']['warn_user_not_found']." <br> <a href=\"".$_SERVER['REQUEST_URI']."\">back</a>"
                            );
                        }
                    }else{
                        $this->template = "select_user";
                        $this->vars=array(
                        );
                    }
                }
                break;
            case "list_warn":
                $warnings = get_table_contents("","","",false,"SELECT warn.*, COALESCE(post.post_title,'') AS post_title, users.username, users.user_warn FROM users,warn LEFT JOIN post ON post.id = warn.post_id AND post.user_id=warn.user_id WHERE users.user_id=warn.user_id",array("time"));
                $warn_js = array_to_js($warnings,"warnings",true,true);
                $this->template = "warn_list";
                $this->vars=array(
                    'WARN' => $warn_js
                );
                break;
        }
    }
}

?>