<?php
class reorder{
    var $module_info = array(
        'title' => "logs",
        'MODULES' => array(
            array('name' => 'reorder','permissions' => 'm_reorder')
        )
    );

    function main($module){
        global $language;
        switch($module){
            case 'reorder':
                if (isset($_POST['id'])) {
                    topic_set_order($_POST['id'], $_POST['order']);
                    die("ok");         
                }
                $this->page_title = $language['module_titles']['reorder'];
                $this->template = "reorder";
                $topics = forum_get_allowed_topics($_GET['f']);
                $topics_js = array_to_js($topics, "topics", true, true);
                $this->vars=array(
                    'TOPICS' => $topics_js,
                );
            break;
        }
    }
}

?>