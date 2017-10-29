<?php

function search_append_keyword($keyword) {
    $ret = array();
    $ret['hashtag'] = "(UPPER(hashtags) LIKE UPPER('%".$keyword."%'))";
    $ret['title'] = "(UPPER(post_title) LIKE UPPER('%".$keyword."%'))";
    $ret['content'] = "(UPPER(data) LIKE UPPER('%".$keyword."%'))";
    $ret['end'] = "UPPER(hashtags) LIKE UPPER('%".$keyword."%') OR UPPER(post_title) LIKE UPPER('%".$keyword."%') OR UPPER(data) LIKE UPPER('%".$keyword."%')";
    return $ret;
}

function search_append_phrase($phrase) {
    $ret = array();
    $ret['hashtag'] = "(UPPER(hashtags) LIKE UPPER('%".$phrase."%'))";
    $ret['title'] = "(UPPER(post_title) LIKE UPPER('%".$phrase."%'))";
    $ret['content'] = "(UPPER(data) LIKE UPPER('%".$phrase."%'))";
    $ret['end'] = "UPPER(hashtags) LIKE UPPER('%".$phrase."%') OR UPPER(post_title) LIKE UPPER('%".$phrase."%') OR UPPER(data) LIKE UPPER('%".$phrase."%')";
    return $ret;
}

function search_append_user_id($uid) {
    $ret = array();
    $ret['cond'] = "AND post.user_id='".$uid."'";
    return $ret;
}

function search_append_user_name($name) {
    $ret = array();
    $ret['cond'] = "AND post.username='".$name."'";
    return $ret;
}

function search_append_forum($forum) {
    $ret = array();
    $ret['cond'] = "AND topic.forum_id='".$forum."'";
    return $ret;
}

function search_get_allowed_forums($type = "ALL") {
    $forum_links_list = get_allowed_forums(-1,true,$type );
    $comments = forum_list_all_comment_forums();
    $allowed_sql = "(";
    for ($i = 0; $i < count($forum_links_list); $i++) {
        if(!in_array($forum_links_list[$i]['forum_id'], $comments)){ //No search from comments
            $allowed_sql .= $forum_links_list[$i]['forum_id'].",";
        }
    }
    $allowed_sql = StringTrimRight($allowed_sql, 1);
    $allowed_sql .= ")";
    if($allowed_sql == ")"){
        $allowed_sql = "()";
    }
    return $allowed_sql;
}


function search_compile_gallery($parameters, $limit = "") {
    $allowed = search_get_allowed_forums(2);
  
    if($parameters['hashtag'] == " )" && $parameters['title'] == " )" && $parameters['content'] && " )"){
        $query = "SELECT post.time, post.username, post.user_id, post.post_title, post.hashtags, actual_name, post.id \n";
        $query .= "FROM attachments \n";
        $query .= "LEFT JOIN post\n";
        $query .= "ON attachments.post_id=post.id\n";
        $query .= "WHERE is_image = 1 AND is_pm = 0 AND post.forum_id IN ".$allowed."\n";
        $query .= $parameters['cond']." ORDER BY time DESC ";
    }else{
        $query = "SELECT post.time, post.username, post.user_id, post.post_title, post.hashtags, actual_name, post.id, \n";
        $query .= $parameters['hashtag']."*3 AS taghits, \n";
        $query .= $parameters['title']."*2 AS titlehits, \n";
        $query .= $parameters['content']." AS datahits \n";
        $query .= "FROM attachments \n";
        $query .= "LEFT JOIN post\n";
        $query .= "ON attachments.post_id=post.id\n";
        $query .= "WHERE is_image = 1 AND is_pm = 0 AND post.forum_id IN ".$allowed." AND \n";
        $query .= $parameters['end']."\n";
        $query .= $parameters['cond']." ORDER BY taghits DESC, titlehits DESC, datahits DESC, time DESC ";
    }
    $query .= $limit;
    $ret[0] = $query;
    return $ret;
}

function search_compile_site($parameters, $limit = "") {
    $allowed = search_get_allowed_forums(1);
    if($parameters['hashtag'] == " )" && $parameters['title'] == " )" && $parameters['content'] && " )"){
        $query = "SELECT *\n";
        $query .= "FROM post WHERE forum_id IN ".$allowed."\n";
        $query .= $parameters['cond']." ORDER BY time DESC ";
    }else{
        $query = "SELECT *, \n";
        $query .= $parameters['hashtag']."*3 AS taghits, \n";
        $query .= $parameters['title']."*2 AS titlehits, \n";
        $query .= $parameters['content']." AS datahits \n";
        $query .= "FROM post WHERE forum_id IN ".$allowed." AND \n";
        $query .= $parameters['end']."\n";
        $query .= $parameters['cond']." ORDER BY taghits DESC, titlehits DESC, datahits DESC, time DESC ";
    }
    $query .= $limit;
    $ret[0] = $query;
    return $ret;
}

function search_compile_mixed($parameters){
    $gal = search_compile_gallery($parameters, "LIMIT 0,8");
    $site = search_compile_site($parameters, "LIMIT 0,10");
    $ret[0] = $gal[0];
    $ret[1] = $site[0];
    return $ret;
}



function build_search($query, $forum_type) {
    $query = htmlspecialchars_decode($query);
    $parts = explode(" ", $query);
    //put quoted parts together
    $parts_merged = array();
    $pointer = 0;
    $in_quote = false;
    for ($i = 0; $i < count($parts); $i++) {
        if(stristr($parts[$i], '"')){
            $in_quote = !$in_quote;
        }
        $parts_merged[$pointer] .= $parts[$i]." ";
        if(!$in_quote){
            $parts_merged[$pointer] = StringTrimRight($parts_merged[$pointer], 1);
            $pointer++;
        }
    }
    
    $parts_merged = array_unique($parts_merged);
    if(($key = array_search("", $parts_merged)) !== false) {
        unset($parts_merged[$key]);
    }
    $keys = array_keys($parts_merged);
    
    
    $tokens = array();
    for ($i = 0; $i < count($parts_merged); $i++) {
        $token_parts = explode(":", $parts_merged[$keys[$i]]);
        if(stristr($parts_merged[$keys[$i]], '"')){
            $tokens[$i]['type'] = 'phrase';
            $tokens[$i]['data'] = str_replace('"', "", $parts_merged[$keys[$i]]);
        }elseif(count($token_parts) == 2){
            if(stristr($token_parts[0], 'user_id')){
                $tokens[$i]['type'] = 'user_id';
            }elseif(stristr($token_parts[0], 'username')){
                $tokens[$i]['type'] = 'username';
            }elseif(stristr($token_parts[0], 'forum')){
                $tokens[$i]['type'] = 'forum';
            }else{
                $tokens[$i]['type'] = 'unknown';
            }
            $tokens[$i]['data'] = $token_parts[1];
        }else{
            $tokens[$i]['type'] = 'keyword';
            $tokens[$i]['data'] = $parts_merged[$keys[$i]];
        }   
    }
    
    $and_parts = array();
    $and_count = 0;
    $phrase_parts = array();
    $phrase_count = 0;
    $keyword_parts = array();
    $keyword_count = 0;
    
    for ($i = 0; $i < count($tokens); $i++) {
        if($tokens[$i]['type'] == 'user_id'
            || $tokens[$i]['type'] == 'username'
            || $tokens[$i]['type'] == 'forum'){
            $and_parts[$and_count]['type'] = $tokens[$i]['type'];
            $and_parts[$and_count]['data'] = $tokens[$i]['data'];
            $and_count++;
        }elseif($tokens[$i]['type'] == 'phrase'){
            $phrase_parts[$phrase_count]['type'] = $tokens[$i]['type'];
            $phrase_parts[$phrase_count]['data'] = $tokens[$i]['data'];
            $phrase_count++;
        }else{
            $keyword_parts[$keyword_count]['type'] = $tokens[$i]['type'];
            $keyword_parts[$keyword_count]['data'] = $tokens[$i]['data'];
            $keyword_count++;
        }
    }
    $query = "";
    
    
    for ($i = 0; $i < count($keyword_parts); $i++) {
        search_append_keyword($keyword_parts[$i]['data']);
    }
    
    for ($i = 0; $i < count($phrase_parts); $i++) {
        search_append_phrase($phrase_parts[$i]['data']);
    }
    
    $search_query = array(
        'hashtag' => '( ',
        'title' => '( ',
        'content' => '( ',
        'end' => '( ',
        'cond' => ' '
    );
    
    //conds
    for ($i = 0; $i < count($and_parts); $i++) {
        if($and_parts[$i]['type'] == 'user_id'){
            $arr = search_append_user_id($and_parts[$i]['data']);
            $search_query['cond'] .= $arr['cond'] . " ";
        }elseif($and_parts[$i]['type'] == 'username'){
            $arr = search_append_user_name($and_parts[$i]['data']);
            $search_query['cond'] .= $arr['cond'] . " ";
        }elseif($and_parts[$i]['type'] == 'forum'){
            $arr = search_append_forum($and_parts[$i]['data']);
            $search_query['cond'] .= $arr['cond'] . " ";
        }
    }
    
    //keywords
    for ($i = 0; $i < count($keyword_parts); $i++) {
        $arr = search_append_keyword($keyword_parts[$i]['data']);
        _mysql_query("UPDATE hashtags SET hit_count=hit_count+1 WHERE tag='".$keyword_parts[$i]['data']."'");
        $search_query['hashtag'] .= $arr['hashtag']." + ";
        $search_query['title'] .= $arr['title']." + ";
        $search_query['content'] .= $arr['content']." + ";
        $search_query['end'] .= $arr['end']." OR ";
    }
    
    //phrases
    for ($i = 0; $i < count($phrase_parts); $i++) {
        $arr = search_append_phrase($phrase_parts[$i]['data']);
        $search_query['hashtag'] .= $arr['hashtag']." + ";
        $search_query['title'] .= $arr['title']." + ";
        $search_query['content'] .= $arr['content']." + ";
        $search_query['end'] .= $arr['end']." OR ";
    }
    
    
    $search_query['hashtag'] = StringTrimRight($search_query['hashtag'], 2) . " )";
    $search_query['title'] = StringTrimRight($search_query['title'], 2) ." )";
    $search_query['content'] = StringTrimRight($search_query['content'], 2) . " )";
    $search_query['end'] = StringTrimRight($search_query['end'], 3) . " )";
    $search_query['cond'] .= " ";
    
    $queries = array();
    
    if($forum_type == 2){
        $queries = search_compile_gallery($search_query);
    }elseif($forum_type == 1){
        $queries = search_compile_site($search_query);
    }else{
        $queries = search_compile_mixed($search_query);
    }
    
    return $queries;
}

function display_search($queries, $forum_type) {
    global $thumbnails, $topic_data, $site_settings, $language, $tags, $root_dir, $acp_action;
    $search_results = get_table_contents("","","",false,$queries[0]);
    //display results
    if($forum_type == 1){ //site
        if(count($search_results) == 0){
            $topic_data  = $language['notifications']['posts_not_found'];
        }else{
            $topic_data = display_topic($search_results,$tags,true);
        }
        $acp_action = "./theme/".$site_settings['template']."/view_gallery.html";
    }elseif($forum_type == 2){ // gallery
        render_thumbs($search_results);
        if(count($search_results) == 0){
            $topic_data  = "No posts were found";
        }
        $acp_action = "./theme/".$site_settings['template']."/view_gallery_thumbs.html";
    }  else {// not set
        $acp_action = "./theme/".$site_settings['template']."/view_gallery_thumbs.html";
        $thumbs = $search_results;
        render_thumbs($thumbs);
        if($thumbnails != ""){
            $thumbnails = '<h2>images:</h2><div>'.$thumbnails.'</div><div style="clear:both;"></div><br><div style="text-align: center; font-size: 20px"><a href="'.$root_dir.'/?a=search&q='.$_GET['q'].'&type=2">More results</a></div><br>';
        }
        $thumbnails = '<div style="text-align: center; font-size: 20px"><form name="input" action="'.$root_dir.'/" method="get"><input type="hidden" name="a" value="search" /><input type="hidden" name="q" value="'.$_GET['q'].'" />  Search in: <input type="radio" name="type" value="2">images <input type="radio" name="type" value="1">Articles <input type="submit" value="Go"></form> <br></div>'.$thumbnails;
        
        
        $results = get_table_contents("","","",false,$queries[1], array('time'));
        $results_tmp;
        $i = 0;
        $articles = "Not found";
        if($results != null){
            foreach ($results as $key => $value) {
                $results_tmp[$i] = $value;
                $i++;
            }
            $articles = display_topic($results_tmp,$tags,true);
        }
        if($articles != ""){
            $thumbnails .=  '<h2>Articles:</h2><br>'.$articles;
            $thumbnails .= '<br><div style="text-align: center; font-size: 20px"><a href="'.$root_dir.'/?a=search&q='.$_GET['q'].'&type=1">More results</a></div>';
        }
    }
}

$TYPE = $_GET['type'] == '' || !isset($_GET['type']) ? 'ALL' : $_GET['type'];
$queries = build_search($_GET['q'], $TYPE);
display_search($queries, $TYPE);


?>
