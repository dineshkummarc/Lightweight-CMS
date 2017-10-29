<?php
class priv_msg{
    var $module_info = array(
        'title' => "Private messages",
        'MODULES' => array(
            array('name' => 'COMPOSE','permissions' => 'u_send_priv_msg'),
            array('name' => 'RECV','permissions' => 'u_recv_priv_msg')
        )
    );

    function message_get_recipients($id){
        $result = _mysql_query("SELECT DISTINCT receiver,username FROM privmsg_to,users WHERE message=".$id." AND receiver=users.user_id");
        $ret = array();
        while ($row = _mysql_fetch_assoc($result)) {
            $ret[] = array($row["receiver"],$row["username"]);
        }
        return $ret;
    }
    function message_get_messages($id){
        $result = _mysql_query("SELECT DISTINCT msg_id FROM privmsg_to WHERE message=".$id);
        $ret = array();
        while ($row = _mysql_fetch_assoc($result)) {
            $ret[] = $row["msg_id"];
        }
        return $ret;
    }

    function generate_message_list($messages, $msg_id){
        global $current_user;
        $ret = "";
        for($i = 0;$i < count($messages); $i++){
            $res = _mysql_query("SELECT post_title FROM privmsg WHERE privmsg.id=".$messages[$i]['message']);
            $messages[$i]['post_title'] = _mysql_result($res,0,0);
            if($messages[$i]['read_time']=='Never' && $msg_id !=$messages[$i]['message']){
                $title = '<a href="./ucp.php?&a=RECV&msg_id='.$messages[$i]['message'].'"><b>'.$messages[$i]['post_title'].'</b><span class="message_time"> on '.$messages[$i]['time'].'</span></a><br>('.$messages[$i]['unread'].' new messages)';
            }else{
                $title = '<a href="./ucp.php?&a=RECV&msg_id='.$messages[$i]['message'].'">'.$messages[$i]['post_title'].'<span class="message_time"> on '.$messages[$i]['time']."</span></a>";
            }
            $recipients = "";
            for($j = 0; $j < count($messages[$i]['recipients']); $j++){
                if($messages[$i]['recipients'][$j][0] != $current_user['uid']){
                    $recipients .= $messages[$i]['recipients'][$j][1].", ";
                }
            }
            $ret .= '<div class="pmtitle">'.$title.'<br>'.StringTrimRight($recipients,2)."</div>\n";
        }
        return $ret;
    }

    function generate_message_content($messages,$tags){
        if(count($messages) == 0){return "";}
        $ret = "<h3>".$messages[0]['post_title']."</h3>";
        for($i=0;$i<count($messages);$i++){
            $attachments = post_get_attachments($messages[$i]['id'],1);
            $attachment_html = "<br><br>";
            for($j = 0; $j < count($attachments); $j++){
                $attachment_html  .= '<div class="attachment"><a href="./upload.php?a=download&file='.$attachments[$j]['id'].'">'.$attachments[$j]['file_name'].'</a><br>size: '.$attachments[$j]['size'].' bytes, downloaded '.$attachments[$j]['downloads'].' time(s)</div>';
            }
            if($attachment_html == "<br><br>"){
                $attachment_html = "";
            }
            $ret .= '<div class="msg_body">By '.$messages[$i]['username'].'<span class="message_time"> on '.$messages[$i]['time']."</span><br><br>".parse_bbcode($messages[$i]['data'],bbcode_to_regex($tags,'bbcode','bbcode_html'))."</div>\n".$attachment_html."<br>";

        }
        return $ret;
    }

    function main($module){
        global $current_user, $language;
        $allow_attachment = 'true';

        switch($module){
            case 'COMPOSE':
                $tags= get_table_contents(bbcode,'ALL');
                $tagsE= get_table_contents(bbcode,array('bbcode_hint','bbcode'),'WHERE bbcode_show=1');
                $len = count($tagsE);
                $code= 'bbcode["'.$len.'"] = [];
                bbcode["'.$len.'"]["bbcode_hint"] = "code";
                bbcode["'.$len.'"]["bbcode"] = "[code]{text}[/code]";';
                $tags_js = array_to_js($tagsE,'bbcode',true, true);
                $tags_js.=$code;

                $this->page_title = $language['module_titles']['new_pm'];
                $this->template = "compose";

                switch($_GET['mode']){
                    case "preview":
                        if(form_is_valid($_GET['form'],'postmessage')){
                            $res = _mysql_query("INSERT INTO ".drafts." VALUES (NULL, ".$current_user['uid'].", '".$_POST['Editor']."', 0, '".$_POST['title']."', 0)");
                            $id = _mysql_insert_id();
                            $result = _mysql_query("SELECT data, title FROM ".drafts." WHERE id=".$id);
                            _mysql_query("DELETE FROM ".drafts." WHERE id=".$id." AND ByUser=0");
                            $_POST['Editor'] = _mysql_result($result,0);
                            $_POST['title'] = _mysql_result($result,0,1);
                            $topic_data = parse_bbcode($_POST['Editor'],bbcode_to_regex($tags,'bbcode','bbcode_html'),array(),true,true);
                            $_POST['msg_to'] = decode_input($_POST['msg_to']);
                            $this->vars=array(
                                'EDITOR' => $_POST['Editor'],
                                'title' => $_POST['title'],
                                'PARSED' => $topic_data,
                                'TAGS' => $tags_js,
                                'FORM' => $form_id,
                                'RECV' => $_POST['msg_to'],
                                'ALLOW_ATTACHMENT' => $allow_attachment
                            );
                            break;
                        }else{
                            $this->template = "failure_module";
                            $this->vars=array(
                                'SUCCESSMSG' => $language['notifications']['form']."<br> <a href=\"".$_SERVER['HTTP_REFERER']."\">back</a> "
                            );
                            break;
                        }
                        break;
                    case "postmessage":
                        if(form_is_valid($_GET['form'],'postmessage')){


                            if(isset($_GET['msg_id']) && $_GET['msg_id'] == '' || !isset($_GET['msg_id']) ){
                                if(!isset($_POST['title']) || strlen($_POST['title']) < 3 ){
                                    $this->template = "failure_module";
                                    $this->vars=array(
                                        'SUCCESSMSG' => $language['notifications']['title_len']."<br> <a href=\"".$_SERVER['HTTP_REFERER']."\">back</a> "
                                    );
                                    break;
                                }
                            }
                            $new_id = "(SELECT AUTO_INCREMENT FROM information_schema.tables WHERE TABLE_NAME = '".privmsg."')";
                            if(isset($_GET['msg_id'])){
                                $_POST['title'] = '';
                                $new_id = $_GET['msg_id'];
                            }
                            $query = "INSERT INTO ".privmsg." SELECT NULL, ".$new_id ." ,'".$_SERVER['REMOTE_ADDR']."',".time().",1,0,'',0,'".$_POST['Editor']."','".$_POST['title']."',".$current_user['uid'].";";
                            $res = _mysql_query($query );
                            $id = _mysql_insert_id();
                            $msg_id = $id;
                            $add_count = 0;
                            if(isset($_GET['msg_id'])){
                                $msg_id = $_GET['msg_id'];
                                $original_recipients = $this->message_get_recipients($_GET['msg_id']);
                                $original_recipients_list = array_copy_dimension($original_recipients,0);
                            }else{
                                $original_recipients = array();
                                $original_recipients_list = array();
                            }
                            $recipients = explode("\n",decode_input($_POST['msg_to']));


                            for($i = 0; $i < count($recipients);$i++){
                                $recipients[$i] = str_replace(array("\n","\r"),"",$recipients[$i]);
                                $uid = user_get_id_by_name($recipients[$i]);
                                if($uid > 1 && !array_search($uid,$original_recipients_list)){
                                    if(!isset($_GET['msg_id'])){
                                        _mysql_query("INSERT INTO ".privmsg_to." VALUES (NULL, '".$id."','".$uid."',0,'".$msg_id."')");
                                    }else{
                                        $msglist = $this->message_get_messages($msg_id);
                                        for($j = 0; $j < count($msglist); $j++){
                                            _mysql_query("INSERT INTO ".privmsg_to." VALUES (NULL, '".$msglist[$j]."','".$uid."',0,'".$msg_id."')");
                                        }
                                    }
                                    $add_count++;
                                }
                            }
                            if(isset($_GET['msg_id'])){
                                for($i =0; $i < count($original_recipients);$i++){
                                    $time = "0";
                                    if($original_recipients[$i][0] == $current_user['uid']){
                                        $time = time();
                                    }
                                    _mysql_query("INSERT INTO ".privmsg_to." VALUES (NULL, '".$id."','".$original_recipients[$i][0]."',".$time.",'".$msg_id."')");
                                }
                                if($add_count > 0){
                                    $added_msg = "Added ".$add_count." users(s) to conversation";
                                    _mysql_query("UPDATE ".privmsg." SET data=CONCAT('".$added_msg."',data) WHERE id=".$id);
                                }
                            }else{
                                _mysql_query("INSERT INTO ".privmsg_to." VALUES (NULL, '".$id."','".$current_user['uid']."',".time().", '".$msg_id."')");
                            }
                            _mysql_query("UPDATE ".attachments." SET post_id=".$id." WHERE form=".$_GET['form']);
                            $this->template = "success_module";
                            $this->vars=array(
                                'SUCCESSMSG' => $language['notifications']['pm_success']."<br> <a href=\"".$_SERVER['HTTP_REFERER']."\">back</a> "
                            );
                        }else{
                            $this->template = "failure_module";
                            $this->vars=array(
                                'SUCCESSMSG' => $language['notifications']['form']."<br> <a href=\"".$_SERVER['HTTP_REFERER']."\">back</a> "
                            );
                            break;
                        }
                        break;
                    default:
                        $form_id = form_add('postmessage');
                        $this->vars=array(
                            'TAGS' => $tags_js,
                            'FORM' => $form_id,
                            'ALLOW_ATTACHMENT' => $allow_attachment
                        );
                        break;
                }
            break;
            case 'RECV':
                if($_GET['mode']=='getcount'){
                    $res = _mysql_query("SELECT count(*) FROM ".privmsg_to." WHERE receiver=".$current_user['uid']." AND read_time=0");
                    die( _mysql_result($res,0) );
                }
                $tags= get_table_contents(bbcode,'ALL');
                if($_GET['mode']=='leave'){
                    _mysql_query("DELETE FROM ".privmsg_to." WHERE message='".$_GET['msg_id']."' AND receiver='".$current_user['uid']."' ");
                    $this->template = "success_module";
                    $this->vars=array(
                        'SUCCESSMSG' => $language['notifications']['success']."<br> <a href=\"".$_SERVER['HTTP_REFERER']."\">back</a> "
                    );
                    break;
                }

                $message_title = "";
                $message_body = "";
                $query="SELECT *, (SELECT count(*) FROM ".privmsg_to." WHERE privmsg_to.message=t1.message AND privmsg_to.receiver=t1.receiver AND privmsg_to.read_time=0) AS unread FROM (SELECT privmsg_to.msg_id,message, post_title, time, privmsg_to.read_time,receiver FROM privmsg_to,privmsg WHERE privmsg_to.msg_id=privmsg.id AND receiver=".$current_user['uid']." ORDER BY read_time ASC, time DESC) AS t1 GROUP BY message ORDER BY read_time ASC, time DESC";
                $res = get_table_contents("","","",false,$query);
                if(count($res)>0){
                    for($i = 0;$i < count($res); $i++){
                        $res[$i]['recipients'] = $this->message_get_recipients($res[$i]['message']);
                    }
                    $msg_id = $res[0]['message'];
                    if(isset($_GET['msg_id'])){
                        $msg_id = $_GET['msg_id'];
                    }
                    $message_found = false;
                    for($i = 0;$i < count($res); $i++){
                        if($res[$i]['message'] == $msg_id){
                            $message_found = true;
                        }
                    }
                    if($message_found){
                        _mysql_query("UPDATE ".privmsg_to." SET read_time=".time()." WHERE receiver=".$current_user['uid']." AND read_time=0 AND message=".$msg_id);
                        $messages = get_table_contents("","","",false,"SELECT privmsg.id,time,post_title,sender,data,username FROM ".privmsg.",".privmsg_to.",".users." WHERE user_id=sender AND privmsg.msg_id=".$msg_id." AND privmsg.id=privmsg_to.msg_id AND receiver=".$current_user['uid']." ORDER BY time ASC");
                        $message_body= $this->generate_message_content($messages,$tags);
                    }else{
                        $message_body = "You are not authorized to read this message.";
                    }
                }
                $message_title= $this->generate_message_list($res,$msg_id);
                //SELECT privmsg.msg_id,post_title FROM privmsg_to,privmsg WHERE  privmsg_to.msg_id=privmsg.id AND receiver=38 ORDER BY 'Read' ASC, time DESC
                //Netsky - We Can Only Live Today (Puppy) - VLC meediaesitaja
                $this->template = "read_messages";
                $res = _mysql_query("SELECT count(*) FROM ".privmsg_to." WHERE receiver=".$current_user['uid']." AND read_time=0");
                $total_unread = _mysql_result($res,0);
                $this->vars=array(
                    'SUCCESSMSG' => $language['notifications']['form']."<br> <a href=\"".$_SERVER['HTTP_REFERER']."\">back</a> ",
                    'messageS' => $message_title,
                    'msg_id' => $msg_id ,
                    'message' => $message_body,
                    'FORM' => form_add('postmessage'),
                    'UNREAD' => $total_unread,
                    'ALLOW_ATTACHMENT' => $allow_attachment

                );
                break;
        }
    }
}

?>