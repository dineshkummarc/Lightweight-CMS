<?php
class reported_posts{
    var $module_info = array(
        'title' => "reported Posts",
        'MODULES' => array(
            array('name' => 'reported_posts','permissions' => 'm_close_reports'),
        )
    );

    function main($module){
        global $language;
        switch($module){
            case 'reported_posts':
                
                $data = get_table_contents('', array(), '', false, 
                        "SELECT report.*, u1.username AS Reporername, IF(close_time = 0, '', u2.username) AS closername, post_title  FROM report
LEFT JOIN users AS u1
ON reporter=u1.user_id
LEFT JOIN users AS u2
ON closer = u2.user_id
LEFT JOIN post
ON post_id = post.id",
                        array('time','close_time')
                        );
                $rows = "";
                for($i = 0; $i < count($data); $i++){
                    $close = "Closed";
                    if ($data[$i]['close_time_timestamp'] == "0") {
                        $close = '<a href="../?p='.$data[$i]['post_id'].'&a=closereport">Close</a>';
                    }
                    $rows .= '<tr><td><a href="../?p='.$data[$i]['post_id'].'">'.$data[$i]['post_title'].'</a></td><td>'.$data[$i]['time'].'</td><td>'.$data[$i]['close_time'].'</td><td><a href="../profile.php?uid='.$data[$i]['reporter'].'">'.$data[$i]['Reporername'].'</a></td><td><a href="../profile.php?uid='.$data[$i]['closer'].'">'.$data[$i]['closername'].'</a></td><td>'.$data[$i]['message'].'</td><td>'.$close.'</td></tr>';
                }
                
                $this->page_title = $language['module_titles']['view_report'];
                $this->template = "manage_reports";
                $this->vars=array(
                    'ROWS' => $rows,
                );
            break;
        }
    }
}