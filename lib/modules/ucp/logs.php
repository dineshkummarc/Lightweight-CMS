<?php
class logs{
    var $module_info = array(
        'title' => "logs",
        'MODULES' => array(
            array('name' => 'ADMIN_LOG','permissions' => 'a_manage_modules'),
        )
    );

    function main($module){
        global $language;
        switch($module){
            case 'ADMIN_LOG':
                $this->page_title = $language['module_titles']['view_admin_log'];
                $this->template = "view_logs";
                $this->vars=array(
                    'VAR' => 'log',
                );
            break;
        }
    }
}