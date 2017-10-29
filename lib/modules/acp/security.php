<?php
class security{
    
    var $module_info = array(
        'title' => "bbcode",
        'MODULES' => array(
            array('name' => 'security','permissions' => 'a_manage_security_settings'),
        )
    );
    function main($module){
        global $language;
        $lng = array(
            'general_labels' => $language['setting_name'],
            'general_descriptions' => $language['setting_description'],
        );

        $lng_js = array_to_js($lng,"lang",true, true);
        
        
        
        switch($module){
            case 'security':
                if(isset($_GET['mode'])){
                    if($_GET['mode'] == "boardsettingssave"){
                        if(to_base_general("security")){
                            $this->template = "success_module";
                            $this->vars=array(
                                'SUCCESSMSG' => $language['notifications']['settings_security_update']." <br><br><a href='./acp.php?id=".$_GET['id']."&a=".$_GET['a']."' style='color: #EEEEEE;'><b>Click here to go back</b></a>"
                            );
                        }else{
                            $this->template = "failure_module";
                            $this->vars=array(
                                'SUCCESSMSG' => $language['notifications']['settings_security_update_fail']."<br><br><a href='./acp.php?id=".$_GET['id']."&a=".$_GET['a']."' style='color: #EEEEEE;'><b>Click here to go back</b></a>"
                            );
                        }
                    }
                    break;
                }
                $this->page_title = $language['module_titles']['security_settings'];
                $this->template = "board_settings";
                $permissions = get_table_contents("general", 'ALL'," WHERE readonly=0 AND class='security'");
                $js = array_to_js($permissions,"general",true, true);
                $this->vars=array(
                    'settings' => $js,
                    'LANGUAGE' => $lng_js,
                );
            break;
        }
    }
}

?>