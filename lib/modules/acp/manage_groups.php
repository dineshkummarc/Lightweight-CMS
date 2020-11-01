<?php
class manage_groups{
    var $module_info = array(
        'title' => "logs",
        'MODULES' => array(
            array('name' => 'manage_groups','permissions' => 'a_manage_groups'),
        )
    );

    function main($module){
        global $current_user, $language;
        $_POST['usernames'] = str_replace(array("\\r","\\n"), array("\r","\n"), $_POST['usernames']);
        if($_GET["gid"] > 0){
            $group_info = group_get_info_by_id($_GET["gid"]);
            if($group_info[0]['founder_manage'] == "1" && $current_user['is_founder'] == "0"){
                $this->template = "failure_module";
                $this->vars=array(
                    'SUCCESSMSG' => $language['notifications']['group_founder_error']." <br><br><a href='./acp.php?id=".$_GET['id']."&a=".$_GET['a']."' style='color: #EEEEEE;'><b>Click here to go back</b></a>"
                );
                return;
            }
        }
        switch($module){
            case 'manage_groups':
                if(isset($_GET['mode'])){
                    $users_list = $_POST['users'];
                    $users_list = str_replace("|", ",", $users_list);
                    switch($_GET['mode']){
                        case 'groupsettings':                          
                            $this->template = "group_edit";
                            $this->page_title = $language['module_titles']['groups_edit'];
                            $this->vars=array(
                                'GROUPINFO' => $group_info,
                                'RANKLISTCOMBO' => rank_list_to_combo()
                            );
                            break 2;
                        case 'groupupdate':
                            if(group_set_info_by_id($_GET["gid"])){
                                $this->template = "success_module";
                                $this->vars=array(
                                    'SUCCESSMSG' => $language['notifications']['group_updated'].' <br><br><a href="./acp.php?id='.$_GET['id']."&a=".$_GET['a'].'" style="color: #EEEEEE;"><b>Click here to go back</b></a>'
                                );
                            }else{
                                $this->template = "failure_module";
                                $this->vars=array(
                                    'SUCCESSMSG' => $language['notifications']['group_updated_fail']." <br><br><a href='./acp.php?id=".$_GET['id']."&a=".$_GET['a']."' style='color: #EEEEEE;'><b>Click here to go back</b></a>"
                                );
                            }
                            break 2;
                        case 'groupdelete':
                            if ($_POST["confirm"] == "yes"){
                                if(group_delete_by_id($_GET["gid"])){
                                    $this->template = "success_module";
                                    $this->vars=array(
                                        'SUCCESSMSG' => $language['notifications']['group_delete']." <br><br><a href='./acp.php?id=".$_GET['id']."&a=".$_GET['a']."' style='color: #EEEEEE;'><b>Manage other group</b></a><br>"
                                    );
                                }
                                else{
                                    $this->template = "failure_module";
                                    $this->vars=array(
                                        'SUCCESSMSG' => $language['notifications']['group_delete_fail']." <br><br><a href='./acp.php?id=".$_GET['id']."&a=".$_GET['a']."' style='color: #EEEEEE;'><b>Go back</b></a>"
                                    );
                                }
                            }else{
                                $this->template = "confirm_module";
                                $this->vars=array(
                                    'YES'   => "./acp.php?".$_SERVER[QUERY_STRING],
                                    'NO'    => "./acp.php?id=".$_GET['id']."&a=".$_GET['a'],
                                    'PROMT' => $language['notifications']['group_delete_confirm'].group_get_name_by_id($_GET["gid"])."?"
                                );
                            }
                            break 2;
                        case 'AddGroup':
                                $new_gid = group_add($_POST["group_name"]);
                                if($new_gid > 0){
                                    $this->template = "success_module";
                                    $this->vars=array(
                                        'SUCCESSMSG' => $language['notifications']['group_add']." <br><br><a href='./acp.php?id=".$_GET['id']."&a=".$_GET['a']."&mode=groupsettings&gid=".$new_gid ."' style='color: #EEEEEE;'><b>Edit group settings</b></a><br><a href='./acp.php?id=".$_GET['id']."&a=manage_permissions&mode=group_permissions_manage&gid=".$new_gid ."' style='color: #EEEEEE;'><b>Set up group permissions</b></a><br><a href='./acp.php?id=".$_GET['id']."&a=".$_GET['a']."' style='color: #EEEEEE;'><b>Go back</b></a>"
                                    );
                                }
                                else{
                                    $this->template = "failure_module";
                                    $this->vars=array(
                                        'SUCCESSMSG' => $language['notifications']['group_add_fail']." <br><br><a href='./acp.php?id=".$_GET['id']."&a=".$_GET['a']."' style='color: #EEEEEE;'><b>Go back</b></a>"
                                    );
                                }
                            break 2;
                        case "groupmembers":
                            $this->page_title = $language['module_titles']['groups_members'];
                            $this->template = "group_members";
                            $group_members = get_member_list($_GET['gid'],array('username','user_join_date','user_post_count','user_default_group'));
                            if ($group_members == null)
                            {
                                $group_members = 'var groupmembers = [];';
                            }else{
                                $group_members = array_to_js($group_members,'groupmembers',true);
                            }
                            $this->vars=array(
                                'GROUPMEMBERS' => $group_members
                            );
                            break 2;
                        case "addtogroup":
                            $status = 1;
                            $default = isset($_POST['Default']);
                            $_POST['usernames'] = str_replace("\r", "\n", $_POST['usernames']);
                            $users = explode("\n",$_POST['usernames']);
                            if(isset($_POST['leader'])){
                                $status = 2;
                            }
                            $users = array_unique($users);
                            if(group_add_member($_GET['gid'],$users , $status, $default)){
                                $this->template = "success_module";
                                $this->vars=array(
                                    'SUCCESSMSG' => $language['notifications']['group_add_user']." <br><br><a href='./acp.php?id=".$_GET['id']."&a=".$_GET['a']."' style='color: #EEEEEE;'><b>Click here to go back</b></a>"
                                );
                            }else{
                                $this->template = "failure_module";
                                $this->vars=array(
                                    'SUCCESSMSG' => $language['notifications']['group_add_user_fail']." <br><br><a href='./acp.php?id=".$_GET['id']."&a=".$_GET['a']."' style='color: #EEEEEE;'><b>Click here to go back</b></a>"
                                );
                            }
                            break 2;
                        case "selectedmembersmanage":
                            //$users_list = explode('|',$_POST['users']);
                            switch($_POST["action"]){
                                case "approve":
                                    $action_success = group_set_member_status($users_list ,$_GET['gid'],1);
                                    if($action_success){
                                        $this->template = "success_module";
                                        $this->vars=array(
                                            'SUCCESSMSG' => $action_success.' of '. count($users_list). $language['notifications']['group_approve'].' <br><br><a href="./acp.php?id='.$_GET['id'].'&a='.$_GET['a'].'">Go back</a><br><br><a href="./acp.php?id='.$_GET['id'].'&a='.$_GET['a'].'&mode=groupmembers&gid='.$_GET['gid'].'">Manage other members of this group</a>'
                                        );
                                    }else{
                                        $this->template = "failure_module";
                                        $this->vars=array(
                                            'SUCCESSMSG' => $language['notifications']['group_approve_fail'].' <br><br>Go back ./acp.php?id='.$_GET['id'].'&a='.$_GET['a']
                                        );
                                    }
                                    break;
                                case "disapprove":
                                    $action_success = group_set_member_status($users_list ,$_GET['gid'],0);
                                    if($action_success){
                                        $this->template = "success_module";
                                        $this->vars=array(
                                            'SUCCESSMSG' => $action_success.' of '. count($users_list). $language['notifications']['group_disapprove'].' <br><br><a href="./acp.php?id='.$_GET['id'].'&a='.$_GET['a'].'">Go back</a><br><br><a href="./acp.php?id='.$_GET['id'].'&a='.$_GET['a'].'&mode=groupmembers&gid='.$_GET['gid'].'">Manage other members of this group</a>'
                                        );
                                    }else{
                                        $this->template = "failure_module";
                                        $this->vars=array(
                                            'SUCCESSMSG' => $language['notifications']['group_disapprove_fail'].' <br><br>Go back ./acp.php?id='.$_GET['id'].'&a='.$_GET['a']
                                        );
                                    }
                                    break;
                                case "default":
                                    try{
                                        $action_success = group_set_default($users_list ,$_GET['gid']);
                                    }catch (Exception $e){
                                        $action_success = false;
                                        $exception = '<br>'.$e->getmessage();
                                    }
                                    if($action_success){
                                        $this->template = "success_module";
                                        $this->vars=array(
                                            'SUCCESSMSG' => sprintf($language['notifications']['group_set_default'], group_get_name_by_id($_GET['gid'])).' <br><br><a href="./acp.php?id='.$_GET['id'].'&a='.$_GET['a'].'">Go back</a><br><br><a href="./acp.php?id='.$_GET['id'].'&a='.$_GET['a'].'&mode=groupmembers&gid='.$_GET['gid'].'">Manage other members of this group</a>'
                                        );
                                    }else{
                                        $this->template = "failure_module";
                                        $this->vars=array(
                                            'SUCCESSMSG' => sprintf($language['notifications']['group_set_default_fail'], $exception).' <br><br>Go back ./acp.php?id='.$_GET['id'].'&a='.$_GET['a']
                                        );
                                    }

                                    break;
                                case "remove":
                                    try{
                                        $action_success = group_remove_member($users_list ,$_GET['gid']);
                                    }catch (Exception $e){
                                        $action_success = false;
                                        $exception = '<br>'.$e->getmessage();
                                    }

                                    if($action_success){
                                        $this->template = "success_module";
                                        $this->vars=array(
                                            'SUCCESSMSG' => sprintf($language['notifications']['group_remove'], count($users_list)).' <br><br><a href="./acp.php?id='.$_GET['id'].'&a='.$_GET['a'].'">Go back</a><br><br><a href="./acp.php?id='.$_GET['id'].'&a='.$_GET['a'].'&mode=groupmembers&gid='.$_GET['gid'].'">Manage other members of this group</a>'
                                        );
                                    }else{
                                        $this->template = "failure_module";
                                        $this->vars=array(
                                            'SUCCESSMSG' => sprintf($language['notifications']['group_remove_fail'], $exception).'<br><br>Go back ./acp.php?id='.$_GET['id'].'&a='.$_GET['a'].''
                                        );
                                    }
                                    break;
                                case "promote":
                                    $action_success = group_set_member_status($users_list,$_GET['gid'],2);
                                    if($action_success){
                                        $this->template = "success_module";
                                        $this->vars=array(
                                            'SUCCESSMSG' => sprintf($language['notifications']['group_set_status'], $action_success,count($users_list)).' <br><br><a href="./acp.php?id='.$_GET['id'].'&a='.$_GET['a'].'">Go back</a><br><br><a href="./acp.php?id='.$_GET['id'].'&a='.$_GET['a'].'&mode=groupmembers&gid='.$_GET['gid'].'">Manage other members of this group</a>'
                                        );
                                    }else{
                                        $this->template = "failure_module";
                                        $this->vars=array(
                                            'SUCCESSMSG' => $language['notifications']['group_set_status_fail'].' <br><br>Go back ./acp.php?id='.$_GET['id'].'&a='.$_GET['a']
                                        );
                                    }
                                    break;
                                case "demote":
                                    $action_success = group_set_member_status($users_list ,$_GET['gid'],1);
                                    if($action_success){
                                        $this->template = "success_module";
                                        $this->vars=array(
                                            'SUCCESSMSG' => sprintf($language['notifications']['group_demote'], $action_success,count($users_list)).' <br><br><a href="./acp.php?id='.$_GET['id'].'&a='.$_GET['a'].'">Go back</a><br><br><a href="./acp.php?id='.$_GET['id'].'&a='.$_GET['a'].'&mode=groupmembers&gid='.$_GET['gid'].'">Manage other members of this group</a>'
                                        );
                                    }else{
                                        $this->template = "failure_module";
                                        $this->vars=array(
                                            'SUCCESSMSG' => $language['notifications']['group_demote'].'<br><br>Go back ./acp.php?id='.$_GET['id'].'&a='.$_GET['a']
                                        );
                                    }
                                    break;
                                case "ban":
                                    break;
                                case "delete":
                                    break;
                            }
                            //~ $this->template = "success_module";
                            //~ $this->vars=array(
                                //~ 'SUCCESSMSG' => "hello"
                            //~ );
                            break 2;
                        default:
                            $this->template = "failure_module";
                            $this->vars=array(
                                'SUCCESSMSG' => $language['notifications']['invalid_mode']."<br><br><a href='./acp.php?id=".$_GET['id']."&a=".$_GET['a']."' style='color: #EEEEEE;'><b>Click here to go back</b></a>"
                            );
                            break 2;
                    }
                }
                $this->page_title = $language['module_titles']['groups_manage'];
                $this->template = "groups_manage";
                $this->vars=array(
                    'groupsTABLE' => groups_get_table_html()
                );
            break;
        }
    }
}