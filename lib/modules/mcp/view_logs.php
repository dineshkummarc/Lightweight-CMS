<?php
class view_logs{
    var $module_info = array(
        'title' => "logs",
        'MODULES' => array(
            array('name' => 'view_user_logs','permissions' => 'm_view_logs'),
            array('name' => 'view_mod_logs','permissions' => 'm_view_logs'),
        )
    );

    function main($module){
        global $language;
        switch($module){
            case 'view_user_logs':
                $this->page_title = $language['module_titles']['view_user_logs'];
                $this->template = "view_logs";
                $this->vars=array(
                    'ROWS' => render_logs('USER'),
                );
            break;
            case 'view_mod_logs':
                $this->page_title = $language['module_titles']['view_user_logs'];
                $this->template = "view_logs";
                $this->vars=array(
                    'ROWS' => render_logs('MODERATOR'),
                );
            break;
        }
    }
}

?>