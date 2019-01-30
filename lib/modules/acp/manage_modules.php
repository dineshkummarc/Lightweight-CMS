<?php
class manage_modules{
    var $module_info = array(
        'title' => "Manage Modules",
        'MODULES' => array(
            array('name' => 'manage_acp_modules','permissions' => 'a_manage_modules'),
            array('name' => 'manage_mcp_modules','permissions' => 'a_manage_modules'),
            array('name' => 'manage_ucp_modules','permissions' => 'a_manage_modules')
        )
    );

    function main($module){
        global $current_user, $language;
        switch($module){
            case 'manage_acp_modules':
                if(isset($_GET['mid'])){
                    if($_GET['ma'] == "delete"){
                        log_event('ADMINISTRATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "MANAGE MODULE", 'Deleted acp module named '. module_get_name_by_id($_GET['mid']));
                        _mysql_query("DELETE FROM modules WHERE module_id=".$_GET['mid']." AND class='acp'");
                        die("success");
                    }elseif($_GET['ma'] == "edit"){
                        if($_GET['mid'] == 0){
                            log_event('ADMINISTRATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "MANAGE MODULE", 'Created acp module named '.$_GET['module_name']);
                            _mysql_query("INSERT INTO modules VALUES (NULL, '".$_GET['module_name']."', '".$_GET['module_enabled']."', '".$_GET['parent_module']."', '".$_GET['type']."', 'acp')");
                            $result = _mysql_query("SELECT MAX(module_id) AS id FROM ".modules);
                            die(_mysql_result($result,0)); //return id of new module
                        }else{
                            log_event('ADMINISTRATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "MANAGE MODULE", 'Edited acp module named '.$_GET['module_name']);
                            _mysql_query("UPDATE modules SET module_name='".$_GET['module_name']."', enabled=".$_GET['module_enabled'].", parent_id=".$_GET['parent_module'].", type=".$_GET['type']." WHERE module_id=".$_GET['mid']." AND class='acp'");
                            die("success");
                        }
                    }
                }
                $this->page_title = $language['module_titles']['manage_acp_modules'];
                $this->template = "manage_modules";
                if(!isset($_GET['pid'])){
                    $_GET['pid'] = 0;
                }else{
                    $_GET['pid'] = intval($_GET['pid']);
                }
                $modules = module_list_childs('acp',$_GET['pid'],true);
                $modules_js = array_to_js($modules,'ModuleList',true, true);

                $all_modules = module_list_all('acp');
                $all_modules[] = array(
                    "module_name" => '',
                    "module_id" => 0,
                    "enabled" => 0,
                    "parent_id" => 0,
                    "type" => 0
                );
                $all_modules_js = array_to_js($all_modules,'module_list_all',true, true);

                $this->vars=array(
                    'MODULES' => $modules_js,
                    'ALLMODULES' => $all_modules_js
                );
            break;
            case 'manage_ucp_modules':
                if(isset($_GET['mid'])){
                    if($_GET['ma'] == "delete"){
                        log_event('ADMINISTRATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "MANAGE MODULE", 'Deleted ucp module named '. module_get_name_by_id($_GET['mid']));
                        _mysql_query("DELETE FROM modules WHERE module_id=".$_GET['mid']." AND class='ucp'");
                        die("success");
                    }elseif($_GET['ma'] == "edit"){
                        if($_GET['mid'] == 0){
                            log_event('ADMINISTRATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "MANAGE MODULE", 'Created ucp module named '.$_GET['module_name']);
                            _mysql_query("INSERT INTO modules VALUES (NULL, '".$_GET['module_name']."', '".$_GET['module_enabled']."', '".$_GET['parent_module']."', '".$_GET['type']."', 'ucp')");
                            $result = _mysql_query("SELECT MAX(module_id) AS id FROM ".modules);
                            die(_mysql_result($result,0)); //return id of new module
                        }else{
                            log_event('ADMINISTRATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "MANAGE MODULE", 'Edited ucp module named '.$_GET['module_name']);
                            _mysql_query("UPDATE modules SET module_name='".$_GET['module_name']."', enabled=".$_GET['module_enabled'].", parent_id=".$_GET['parent_module'].", type=".$_GET['type']." WHERE module_id=".$_GET['mid']." AND class='ucp'");
                            die("success");
                        }
                    }
                }
                $this->page_title = $language['module_titles']['manage_ucp_modules'];
                $this->template = "manage_modules";
                if(!isset($_GET['pid'])){
                    $_GET['pid'] = 0;
                }else{
                    $_GET['pid'] = intval($_GET['pid']);
                }
                $modules = module_list_childs('ucp',$_GET['pid'],true);
                $modules_js = array_to_js($modules,'ModuleList',true, true);

                $all_modules = module_list_all('ucp');
                $all_modules[] = array(
                    "module_name" => '',
                    "module_id" => 0,
                    "enabled" => 0,
                    "parent_id" => 0,
                    "type" => 0
                );
                $all_modules_js = array_to_js($all_modules,'module_list_all',true, true);

                $this->vars=array(
                    'MODULES' => $modules_js,
                    'ALLMODULES' => $all_modules_js
                );
            break;
            case 'manage_mcp_modules':
                if(isset($_GET['mid'])){
                    if($_GET['ma'] == "delete"){
                        log_event('ADMINISTRATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "MANAGE MODULE", 'Deleted mcp module named '. module_get_name_by_id($_GET['mid']));
                        _mysql_query("DELETE FROM modules WHERE module_id=".$_GET['mid']." AND class='mcp'");
                        die("success");
                    }elseif($_GET['ma'] == "edit"){
                        if($_GET['mid'] == 0){
                            log_event('ADMINISTRATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "MANAGE MODULE", 'Created mcp module named '.$_GET['module_name']);
                            _mysql_query("INSERT INTO modules VALUES (NULL, '".$_GET['module_name']."', '".$_GET['module_enabled']."', '".$_GET['parent_module']."', '".$_GET['type']."', 'mcp')");
                            $result = _mysql_query("SELECT MAX(module_id) AS id FROM ".modules);
                            die(_mysql_result($result,0)); //return id of new module
                        }else{
                            log_event('ADMINISTRATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "MANAGE MODULE", 'Edited mcp module named '.$_GET['module_name']);
                            _mysql_query("UPDATE modules SET module_name='".$_GET['module_name']."', enabled=".$_GET['module_enabled'].", parent_id=".$_GET['parent_module'].", type=".$_GET['type']." WHERE module_id=".$_GET['mid']." AND class='mcp'");
                            die("success");
                        }
                    }
                }
                $this->page_title = $language['module_titles']['manage_mcp_modules'];
                $this->template = "manage_modules";
                if(!isset($_GET['pid'])){
                    $_GET['pid'] = 0;
                }else{
                    $_GET['pid'] = intval($_GET['pid']);
                }
                $modules = module_list_childs('mcp',$_GET['pid'],true);
                $modules_js = array_to_js($modules,'ModuleList',true, true);

                $all_modules = module_list_all('mcp');
                $all_modules[] = array(
                    "module_name" => '',
                    "module_id" => 0,
                    "enabled" => 0,
                    "parent_id" => 0,
                    "type" => 0
                );
                $all_modules_js = array_to_js($all_modules,'module_list_all',true, true);

                $this->vars=array(
                    'MODULES' => $modules_js,
                    'ALLMODULES' => $all_modules_js
                );
            break;
        }
    }
}