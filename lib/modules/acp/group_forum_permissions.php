<?php
class group_forum_permissions{
    var $module_info = array(
        'title' => "logs",
        'MODULES' => array(
            array('name' => 'group_forum_permissions','permissions' => 'a_group_permissions'),
            array('name' => 'forum_permissions','permissions' => 'a_group_permissions'),
        )
    );

    function main($module){
        global $language;
        switch($module){
            case 'group_forum_permissions':
                $this->page_title = $language['module_titles']['group_forum_permissions'];
                $this->template = "group_forum_permissions";
                if(isset($_GET['mode'])){
                    if($_GET['mode'] == 'group_forum_permissions_manage'){
                        $this->template = "js";
                        if (isset($_POST["permissions"])){
                            if(group_set_permissions($_POST["gid"],$_POST["permissions"], $_POST["fid"])){
                                $this->template = "success_module";
                                $this->vars=array(
                                    'SUCCESSMSG' => $language['notifications']['permissions_update']." <br><br><a href='./acp.php?id=".$_GET['id']."&a=".$_GET['a']."' style='color: #EEEEEE;'><b>Click here to go back</b></a>"
                                );
                            }else{
                                $this->template = "failure_module";
                                $this->vars=array(
                                    'SUCCESSMSG' => $language['notifications']['permissions_update_fail']." <br><br><a href='./acp.php?id=".$_GET['id']."&a=".$_GET['a']."' style='color: #EEEEEE;'><b>Click here to go back</b></a>"
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
                                'group_permissions_js' => array_to_js(group_list_permissions($group_id,$_POST['forum_name']),'group_permissions'),
                                'permissions_table' => array_to_js($group_table,'permissions',true),
                                'Gid' =>$group_id
                            );
                        }
                        break;
                    }
                }
                $this->vars=array(
                    'GROUP_COMBO' => group_list_to_combo(),
                    'FORUM_COMBO' => forum_list_to_combo()
                );
            break;
            case 'forum_permissions':
                $this->page_title = $language['module_titles']['group_forum_permissions'];
                if(isset($_GET['mode'])){
                    if($_GET['mode']=="forum_permissions_manage"){
                        if (isset($_POST["permissions"])){
                            if(stristr($_POST["permissions"], "undefined")){
                                $arr = explode(":", $_POST["permissions"]);
                                _mysql_query("DELETE FROM group_permissions WHERE forum_id = '".$arr[0]."'");
                                die('<div style="background-color: #00AA30;color: #FFFFFF;padding:5px;">Done<br>No one has access now.</div>');
                            }
                            $sql = "SELECT permission_id FROM permissions WHERE (permission_class = 'moderator' OR permission_class = 'forum') AND founder = 0";
                            $valid =  get_table_contents(NULL,NULL,NULL,false,$sql); //rule out that user can extend his permissions with this
                            $str_valid = "";
                            for ($i = 0; $i < count($valid); $i++) {
                                $str_valid .= $valid[$i]['permission_id']."|";
                            }
                            $arr = explode(":", $_POST["permissions"]);
                            $arr[1] = explode(";", $arr[1]);
                            for($i = 0; $i < count($arr[1]); $i++){
                                $arr[1][$i] = explode("|", $arr[1][$i]);
                                _mysql_query("DELETE FROM group_permissions WHERE forum_id = '".$arr[0]."' AND group_id = '".$arr[1][$i][0]."'");
                                $ins = "INSERT INTO group_permissions VALUES ";
                                $data = "";
                                for ($j = 1; $j < count($arr[1][$i])-1; $j++) {
                                    if(stristr($str_valid, $arr[1][$i][$j]) ){
                                        $data .= "('".intval($arr[1][$i][0])."','".intval($arr[0])."','".intval($arr[1][$i][$j])."'),";
                                    } else {
                                        die('<div style="background-color: #AA0030;color: #FFFFFF">Invalid data:'.$arr[1][$i][$j].'</div>');
                                    }
                                }
                                _mysql_query($ins. StringTrimRight($data, 1));
                            }
                            die('<div style="background-color: #00AA30;color: #FFFFFF;padding:5px;">Done</div>');
                        }
                        $permissions = forum_list_permissions($_POST["forum_id"]);
                        for($i = 0;$i < count($permissions); $i++){
                            $permissions[$i]['has']= true;
                        }
                        $json = json_encode($permissions );
                        $this->template = "forum_permissions_manage";
                        $group_table = get_table_contents("permissions");
                        for ($i = 0; $i < count($group_table); $i++) {
                            $group_table[$i]['translated'] = $language['permissions'][$group_table[$i]['name']];
                        }
                        $this->vars=array(
                            'groups' => json_encode(groups_list(array('name', 'id'))),
                            'permissions_table' => array_to_js($group_table,'permissions',true),
                            'json' => $json,
                            'forum_name' => forum_get_name_by_id($_POST["forum_id"])
                        );
                    }
                }else{
                    $this->page_title = $language['module_titles']['select_group'];
                    $this->template = "forum_permissions";
                    $this->vars=array(
                        'FORUM_COMBO' => forum_list_to_combo()
                    );
                }
            break;
        }
    }
}

?>