<?php
class view_logs{
    var $module_info = array(
        'title' => "logs",
        'MODULES' => array(
            array('name' => 'view_admin_logs','permissions' => 'a_view_logs'),
        )
    );

    function main($module){
        global $language;
        switch($module){
            case 'view_admin_logs':
                $this->page_title = $language['module_titles']['view_admin_log'];
                $this->template = "view_logs";
                $this->vars=array(
                    'ROWS' => render_logs('ADMINISTRATOR'),
                );
            break;
        }
    }
}

?>