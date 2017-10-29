<?php
class approval{
    var $module_info = array(
        'title' => "Posts waiting for approval",
        'MODULES' => array(
            array('name' => 'approval','permissions' => 'm_approve_posts'),
        )
    );

    function main($module){
        global $language;
        switch($module){
            case 'approval':
                
                $data = get_table_contents('post', 'ALL', ' WHERE is_approved = 0 ');
                $rows = "";
                for($i = 0; $i < count($data); $i++){
                    $approve = '<a href="../?p='.$data[$i]['id'].'&a=approve">Approve</a>';
                    $rows .= '<tr><td><a href="../?p='.$data[$i]['id'].'">'.$data[$i]['post_title'].'</a></td><td>'.$data[$i]['time'].'</td><td>'.$data[$i]['data'].'</td><td><a href="../profile.php?uid='.$data[$i]['user_id'].'">'.$data[$i]['username'].'</a></td><td>'.$approve.'</td></tr>';
                }
                $this->page_title = $language['module_titles']['approval'];
                $this->template = "approve";
                $this->vars=array(
                    'ROWS' => $rows,
                );
            break;
        }
    }
}

?>