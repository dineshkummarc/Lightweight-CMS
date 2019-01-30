<?php
class logs{
    var $module_info = array(
        'title' => "logs",
        'MODULES' => array(
            array('name' => 'ADMIN_LOG','permissions' => 'a_manage_modules'),
        )
    );

    function main($module){
        switch($module){
            case 'ADMIN_LOG':
                $this->page_title = "View admin logs";
                $this->template = "view_logs";
                $this->vars=array(
                    'VAR' => 'log',
                );
            break;
        }
    }
}