<?php
class manage_comments{
    var $module_info = array(
        'title' => "Manage comments",
        'MODULES' => array(
            array('name' => 'manage_comments','permissions' => 'm_manage_comments'),
        )
    );

    function main($module){
        global $language;
        switch($module){
            case 'manage_comments':
                
                $data = get_table_contents('', array(), '', false, 
                        "SELECT post.*, topic.topic_id AS tid, topic.forum_id AS fid, topic.title AS tt, forum.forum_name, users.username FROM post, topic, forum,  users WHERE post.forum_id IN (SELECT forum_id FROM forum WHERE forum_id IN (SELECT comments FROM forum WHERE comments > 0)) AND post_title*0 != post_title AND post_title=topic.topic_id AND topic.forum_id = forum.forum_id AND topic.Poster = users.user_id",
                        array('time')
                        );
                $rows = "";
                for($i = 0; $i < count($data); $i++){
                    $rows .= '<tr><td>'.($data[$i]['is_approved'] == "1" ? "yes" : "no").'</td><td>'.$data[$i]['username'].'</td><td><a href="../?f='.$data[$i]['fid'].'">'.$data[$i]['forum_name'].'</a></td><td><a href="../?id='.$data[$i]['tid'].'">'.$data[$i]['tt'].'</a></td><td>'.$data[$i]['data'].'</td><td><span onclick="view('.$data[$i]['id'].')">view</span></td><td><span onclick="removecomment('.$data[$i]['tid'].' ,'.$data[$i]['id'].')">remove</span></td><td>'.$data[$i]['time'].'</td></tr>';
                }
                
                $this->page_title = $language['module_titles']['manage_comments'];
                $this->template = "manage_comments";
                $this->vars=array(
                    'ROWS' => $rows,
                );
            break;
        }
    }
}

?>