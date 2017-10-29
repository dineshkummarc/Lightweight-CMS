<?php
class logs{
    var $module_info = array(
        'TITLE' => "Logs",
        'MODULES' => array(
            array('NAME' => 'ADMIN_LOG','PERMISSIONS' => 'A_MANAGEMODULES'),
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

?>