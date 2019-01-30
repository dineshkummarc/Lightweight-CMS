<?php
class site_settings{
    var $module_info = array(
        'title' => "logs",
        'MODULES' => array(
            array('name' => 'manage_settings','permissions' => 'a_manage_board_settings'),
        )
    );
    
    function render_template_combo(){
        global $site_settings;
        $ret = '<select name="template">';
        $dirs = scandir("../theme/");
        for ($i = 0; $i < count($dirs); $i++) {
            if($dirs[$i] != "." && $dirs[$i] != ".." ){
                $selected = $site_settings['template'] == $dirs[$i] ? 'selected' : "";
                $ret .= '<option value="'.$dirs[$i].'" '.$selected.'>'.$dirs[$i].'</option>';
            }
            
        }
        $ret .= '</select>';
        return $ret;
    }
    
    function render_language_combo(){
        global $site_settings;
        $ret = '<select name="language">';
        $dirs = scandir("./lng");
        for ($i = 0; $i < count($dirs); $i++) {
            if($dirs[$i] != "." && $dirs[$i] != ".." ){
                $dirs[$i]  = StringTrimRight($dirs[$i], 4); //remove file extension
                $selected = $site_settings['language'] == $dirs[$i] ? 'selected' : "";
                $ret .= '<option value="'.$dirs[$i].'" '.$selected.'>'.$dirs[$i].'</option>';
            }
            
        }
        $ret .= '</select>';
        return $ret;
    }
    

    function main($module){
        global $language;

    $lng = array(
        'general_labels' => $language['setting_name'],
        'general_descriptions' => $language['setting_description'],
    );
    
    $lng_js = array_to_js($lng,"lang",true, true);

        switch($module){
            case 'manage_settings':
                if(isset($_GET['mode'])){
                    if($_GET['mode'] == "boardsettingssave"){
                        if(to_base_general("general")){
                            $this->template = "success_module";
                            $this->vars=array(
                                'SUCCESSMSG' => $language['notifications']['settings_board_update']."<br><br><a href='./acp.php?id=".$_GET['id']."&a=".$_GET['a']."' style='color: #EEEEEE;'><b>Click here to go back</b></a>"
                            );
                        }else{
                            $this->template = "failure_module";
                            $this->vars=array(
                                'SUCCESSMSG' => $language['notifications']['settings_board_update']."<br><br><a href='./acp.php?id=".$_GET['id']."&a=".$_GET['a']."' style='color: #EEEEEE;'><b>Click here to go back</b></a>"
                            );
                        }
                    }
                    break;
                }
                $this->page_title = $language['module_titles']['site_settings'];
                $this->template = "board_settings";
                $permissions = get_table_contents("general", 'ALL'," WHERE readonly=0 AND class='general'");
                $js = array_to_js($permissions,"general",true, true);
                $this->vars=array(
                    'settings' => $js,
                    'LANGUAGE' => $lng_js,
                    'TEMPLATES' => $this->render_template_combo(),
                    'LANGUAGE_COMBO' => $this->render_language_combo()
                );
            break;
        }
    }
}