<?php

//~ function forum_get_actions($id){
    //~ global $current_user;
    //~ if(has_permission($current_user['permissions'][1],"f_start_new")){
        //~ return ' <a href="./index.php?p=0&a=new">new</a>';
    //~ }
    //~ return '';
//~ }
//CODE 

global $galleries, $render_albums;

if(isset($_GET['a'])){
    do_action();
}else{
    if($_GET['id']=='0'){
        $topics = get_table_contents("","","",false,"SELECT topic_id FROM topic WHERE forum_id=".$forum_id_const." AND is_approved=1 ORDER BY type DESC, last_post_time DESC LIMIT 0,1");
        $_GET['id']=$topics[0]['topic_id'];
    }
    if(isset($_GET['p'])){
        $NO_id = false;
        $_GET['id'] = post_get_topic($_GET['p']);
    }
    if($NO_id){
        $query = "SELECT DISTINCT topic.topic_id, topic.forum_id,post.time,post.username, post.user_id, post.post_title, post.hashtags, actual_name, post_id, post.id\n FROM topic,post,attachments WHERE\n topic.forum_id=".$forum_id_const." AND topic.first_post_id=post.id AND attachments.post_id=post.id AND is_image=1 GROUP BY topic.topic_id ORDER BY type DESC, last_post_time DESC";
        $thumbs = get_table_contents("","","",false,$query);
        render_thumbs($thumbs);
        $children = forum_get_child_list_by_type($forum_id_const, 2);
        if(count($children) > 0) {
            $albums = forum_get_info($children);
            $albums_list = array_copy_dimension($albums, last_post_id);
            $query = "SELECT DISTINCT topic.topic_id, topic.forum_id,post.time,post.username, post.user_id, post.post_title, post.hashtags, actual_name, post_id, post.id\n FROM topic,post,attachments WHERE\n post.id IN (" . implode(",", $albums_list) . ") AND topic.first_post_id=post.id AND attachments.post_id=post.id AND is_image=1 GROUP BY topic.topic_id ORDER BY type DESC, last_post_time DESC";
            $album_thumbs = get_table_contents("", "", "", false, $query);
            render_galleries($album_thumbs);
        }
    }else{//id set
        //$parent = forum_get_parent($forum_id_const);
        if($_GET['id']=='0'){
            $topics = get_table_contents("","","",true,"SELECT topic_id FROM topic WHERE forum_id=".$forum_id_const." AND is_approved=1 ORDER BY type DESC, last_post_time DESC LIMIT 0,1");
            $_GET['id']=$topics[0]['topic_id'];
        }

        $mod_tools = get_mod_tools();
        $attachment_list = "var AttachmentList = [];";

        $CURRENT_TOPIC = topic_get_info($_GET['id']);
        $topic_content = topic_get_data($_GET['id']);
        $acp_action = "./theme/".$site_settings['template']."/view_gallery.html";
        $CURRENT_TOPIC = topic_get_info($_GET['id']);
        //~ $TOPICtitle
        $topic_content = topic_get_data($_GET['id']);

        if($_GET['id'] == 0){
            $topic_data  = "There are no topics or posts in this forum.";
        }else{
            $topic_data = display_topic($topic_content,$tags,$CURRENT_TOPIC);
        }

        $FORUM_ACTIONS = forum_get_actions($forum_id_const,$_GET['id']);
    }
}

function get_navigation_images($fourm_id, $time) {
   $prevnext = get_table_contents('', '', '', false,
            "SELECT * FROM ((
                SELECT post.id, actual_name, post.post_title, post.time
                FROM topic, post, attachments
                WHERE topic.forum_id = ".$fourm_id."
                AND topic.first_post_id = post.id
                AND post.id = attachments.post_id
                AND is_image =1
                AND post.time < ".$time."
                GROUP BY post.id
                ORDER BY type DESC, last_post_time DESC
                LIMIT 0 , 2
                )
                UNION (

                SELECT post.id, actual_name, post.post_title, post.time
                FROM topic, post, attachments
                WHERE topic.forum_id = ".$fourm_id."
                AND topic.first_post_id = post.id
                AND post.id = attachments.post_id
                AND is_image =1
                AND post.time > ".$time."
                GROUP BY post.id
                ORDER BY type DESC, last_post_time ASC
                LIMIT 0 , 2
              )) AS a
            ORDER BY time ASC"
    );    
    
    $html = '<div id="navigation_thumbnails" style="text-align: center;"><span>';
    for($i = count($prevnext) -1; $i > -1 ; $i--){
        $html .=  '<div class="thumb_container_small"><a href="./?p='.$prevnext[$i]['id'].'"><img src="./images/small/'.$prevnext[$i]['actual_name'].'"></a></div>';
    }
    return $html."</div></span>";
}


function display_topic($posts,$tags,$topic,$no_permissions = false){ 
    global $forum_id_const, $current_user, $forum_info, $site_settings, $BACK_TO_GALLERY, $root_dir, $image_width, $image_height;
    $ret = "";
    topic_inc_views($topic[0]['topic_id']);
    $thumbs = get_navigation_images($posts[0]['forum_id'],$posts[0]['time_timestamp']);
    $image_container = load_template_file($root_dir."/theme/".$site_settings['template']."/view_image.html");
    $image_container = template_replace($image_container,array());
    for($i = 0; $i < count($posts); $i++){
        if($posts[$i]['is_approved']==1 || has_permission(array_merge($current_user['permissions']['global'],$current_user['permissions'][$forum_id_const]) ,"m_approve_posts")){
            if($no_permissions){
                $actions = "";
            }else{
                if($_GET['a'] == "search"){
                    $actions = "";
                }else{
                    $actions = topic_get_post_actions($posts[$i],true, false, $topic);
                }
            }
            if($i == 0){
                hastag_inc_hit_count($posts[$i]['hashtags'],$posts[$i]['forum_id']);
            }
            
            $posts[$i]['data'] = decode_input($posts[$i]['data'] );
            if($site_settings['allow_bbcode'] == "1" && $posts[$i]['bbcode'] == "1"){
                $posts[$i]['data'] = parse_bbcode($posts[$i]['data'],bbcode_to_regex($tags,'bbcode','bbcode_html'),array(),true,true);
            }
            
            $attachments_tmp = "";
            $image = "";
            $exif_tmp = "";
            if(has_permission($current_user['permissions'][$forum_id_const],'f_can_download')){ //<-- HERE
                $attachments = post_get_attachments($posts[$i]['id']);
                $attach = render_attachment($attachments,$posts[$i],true, $i == 0);
                $attachments_tmp = $attach['attachments'];
                $exif_tmp = $attach['exif'];
                $image = $attach['image'];
            }
            $image = str_ireplace("{ALT}", $posts[$i]['hashtags'], $image);
            $posts[$i]['data'].=$attachments_tmp ;
            $share = "";
            if(has_permission($current_user['permissions'][$forum_id_const] ,"f_share")){
                $share = '<span class="share '.$posts[$i]['id'].'"></span>';
            }
            $like = "";
            if(has_permission($current_user['permissions'][$forum_id_const] ,"f_like")){
                $like = '<span>'.render_likes($posts[$i]['id']).'</span>';
            }
            $class = '';
            if($posts[$i]['solved']){
                //$class = ' solved';
                $posts[$i]['post_title'] = '<img src="./theme/'.$site_settings['template'].'/icons/check.png" class="solved icon24">'.$posts[$i]['post_title'];
            }
            $back_link = ($i == 0 && $_GET['a'] != "search") ? $BACK_TO_GALLERY : '<div style="font-size: 20px;"><br></div>';
            $replacements = array(
                '{class}' => $class,
                '{post_id}' => $posts[$i]['id'],
                '{post_title}' => $posts[$i]['post_title'],
                '{actions}' => $actions,
                '{back_link}' => $back_link,
                '{username}' => '<a href="./profile.php?uid='.$posts[$i]['user_id'].'">'.$posts[$i]['username'].'</a>',
                '{post_time}' => $posts[$i]['time'],
                '{image}' => $image,
                '{thumbs}' => $thumbs,
                '{exif}' => $exif_tmp,
                '{content}' => $posts[$i]['data'],
                '{hashtags}' => $posts[$i]['hashtags'],
                '{hashtags_rendered}' => render_hashtags($posts[$i]['hashtags']),
                '{share}' => $share,
                '{like}' => $like  
            );
            $image_container_tmp = strtr($image_container, $replacements);
            
            
            $ret .= $image_container_tmp;
            $thumbs = "";
        }
    }
    return $ret;
}


function preview($a = ""){
    global $editor,$action,$language,$current_user,$post_title,$topic_data,$attachment_list,$acp_action,$allow_attachment,$notification,$tags,$site_settings, $notification_back_link;
    if($a != ""){
        $action = $a;
    }
    $editor = post_get_info($_GET['p']);
    if(form_is_valid($_GET['form'],$action)){
        $res = _mysql_query("INSERT INTO drafts VALUES (NULL, ".$current_user['uid'].", '".$_POST['Editor']."', ".$_GET['p'].", '".$_POST['title']."', 0)");
        $id = _mysql_insert_id ();
        $result = _mysql_query("SELECT data, title FROM drafts WHERE id=".$id);
        _mysql_query("DELETE FROM drafts WHERE id=".$id." AND ByUser=0");
        //~ $_POST['Editor'] = decode_input(_mysql_result($result,0));
        $_POST['Editor'] = _mysql_result($result,0);
        $post_title = _mysql_result($result,0,1);
        //file_put_contents("char.txt",$_POST['Editor']);
        //die('<h2 style="margin: 0px 0px 1em 0px; padding: 0px;">'.$post_title.'</h2>'.parse_bbcode($_POST['Editor'],bbcode_to_regex($tags,'bbcode','bbcode_html'),array(),true,true));
        //~ die('<h2 style="margin: 0px 0px 1em 0px; padding: 0px;">'.$post_title.'</h2>'.parse_bbcode($_POST['Editor'],bbcode_to_regex($tags,'bbcode','bbcode_html')));
        $editor[0]['data'] = $_POST['Editor'];
        $editor[0]['post_title'] = decode_input($_POST['title']);
        $title_warning = "";
        if(strlen($editor[0]['post_title']) < 3){
            $title_warning = "<b style=\"color: #ff0000\">ERROR: title too short</b>";
        }
        $topic_data = $title_warning.'<h2 style="margin: 0px 0px 1em 0px; padding: 0px;">'.$post_title.'</h2>'. parse_bbcode($_POST['Editor'],bbcode_to_regex($tags,'bbcode','bbcode_html'),array(),true,true);
        //~ $acp_action = "./theme/".$site_settings['template']."/view_gallery.html";
        $acp_action = "./theme/".$site_settings['template']."/ajaxpost.html";
        $post_where = "";
        if($_GET['p'] > 0 ){
            $post_where = "post_id=".$_GET['p']." OR";
        }
        $attachment_list = array_copy_dimension(get_table_contents(attachments,"id", " WHERE ".$post_where ." form=".$_GET['form'] ),'id');
        $attachment_list = array_to_js($attachment_list,"AttachmentList");
    }else{
        $acp_action = "./theme/".$site_settings['template']."/ucp/failure_module.html";
        $notification = $language['notifications']['form'].$notification_back_link;
    }
}