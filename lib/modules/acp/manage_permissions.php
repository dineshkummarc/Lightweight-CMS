<?php
class manage_permissions{
    var $module_info = array(
        'title' => "logs",
        'MODULES' => array(
            array('name' => 'manage_permissions','permissions' => 'a_group_permissions'),
        )
    );

    function main($module){
        global $language;
        switch($module){
            case 'manage_permissions':
                $this->page_title = $language['module_titles']['manage_permissions'];
                $this->template = "group_permissions";
                if(isset($_GET['mode'])){
                    if($_GET['mode'] == 'group_permissions_manage'){
                        $this->template = "js";
                        if (isset($_POST["permissions"])){
                            if(group_set_permissions($_POST["gid"],$_POST["permissions"])){
                                $this->template = "success_module";
                                $this->vars=array(
                                    'SUCCESSMSG' => $language['notifications']['group_permission_update']." <br><br><a href='./acp.php?id=".$_GET['id']."&a=".$_GET['a']."' style='color: #EEEEEE;'><b>Click here to go back</b></a>"
                                );
                            }else{
                                $this->template = "failure_module";
                                $this->vars=array(
                                    'SUCCESSMSG' => $language['notifications']['group_permission_update_fail']." <br><br><a href='./acp.php?id=".$_GET['id']."&a=".$_GET['a']."' style='color: #EEEEEE;'><b>Click here to go back</b></a>"
                                );
                            }
                        }else{
                            if (isset($_POST["group_name"])){
                               $group_id= $_POST["group_name"];
                            }elseif(isset($_GET["gid"])){
                               $group_id= $_GET["gid"];
                            }
                            $group_table = get_table_contents("permissions");
                            for ($i = 0; $i < count($group_table); $i++) {
                                $group_table[$i]['translated'] = $language['permissions'][$group_table[$i]['name']];
                            }
                            $this->vars=array(
                                'group_permissions_js' => array_to_js(group_list_permissions($group_id),'group_permissions'),
                                'permissions_table' => array_to_js($group_table,'permissions',true),
                                'Gid' =>$group_id,
                                'group_name' => group_get_name_by_id($group_id)
                            );
                        }
                        break;
                    }
                }
                $this->vars=array(
                    'GROUP_COMBO' => group_list_to_combo()
                );
            break;
        }
    }
}