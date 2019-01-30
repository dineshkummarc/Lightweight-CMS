<?php
class manage_groups{
    var $module_info = array(
        'title' => "logs",
        'MODULES' => array(
            array('name' => 'list_groups','permissions' => 'U_'),
            array('name' => 'manage_groups','permissions' => 'U_'),
        )
    );
    
    function reder_combo($type, $is_member, $gid, $pending){
        $ret = '<select name="'.$gid.'">';
        $ret .= '<option value="none">None</option>';
        if($type == "0" && !$is_member && !$pending){
            $ret .= '<option value="join">Join</option>';
        }elseif($type == "1" && !$is_member && !$pending){
            $ret .= '<option value="request">Request to join</option>';
        }elseif ((($type == "1" || $type == "0") && $is_member) || $pending ) {
            $ret .= '<option value="leave">Leave</option>';
        }
        $ret .= '</select>';
        return $ret;
    }
    
    function render_groups($groups){
        global $current_user;
        $ch_perm = has_permission($current_user['permissions']['global'], 'u_change_default_group');
        $rows = "<tr class='sortable_head'><td>Select</td><td>name</td><td>description</td><td>action</td><td>Status</td></tr>";
        $pending_groups = user_get_pending_groups($current_user['uid']);
        for ($i = 0; $i < count($groups); $i++) {
            $disabled = "";
            $option = "";
            $checked = "";
            $disable_button = "";
            $status = "Not joined";
            if(in_array($groups[$i]['id'],$current_user['groups'])){
                $status = "Joined";
            }elseif (in_array($groups[$i]['id'],$pending_groups)) {
                $status = "Pending";
            }
            if(!in_array($groups[$i]['id'],$current_user['groups'])||!$ch_perm){
                $disabled = " disabled";
            }
            if ($current_user['user']['user_default_group'] == $groups[$i]['id']) {
                $checked = " checked";
            }
            if(!$ch_perm){
                $disable_button = " disabled";
            }
            $combo = $this->reder_combo($groups[$i]['type'],in_array($groups[$i]['id'],$current_user['groups']),$groups[$i]['id'],$status=="Pending");
            if($groups[$i]['color'] == ""){
                $groups[$i]['color'] = "#000";
            }
            if($groups[$i]['type'] == "3"){
                if(in_array($groups[$i]['id'],$current_user['groups'])){
                    $rows .= '<tr><td><input type="radio" name="group" value="'.$groups[$i]['id'].'"'.$disabled.$checked.'></td><td><a href="../memberlist.php?g='.$groups[$i]['id'].'" style="color: '.$groups[$i]['color'] .'">'.$groups[$i]['name'].'</a></td><td>'.$groups[$i]['description'].'</td><td>'.$combo.'</td><td>'.$status.'</td></tr>';
                }
            }else{
                $rows .= '<tr><td><input type="radio" name="group" value="'.$groups[$i]['id'].'"'.$disabled.$checked.'></td><td><a href="../memberlist.php?g='.$groups[$i]['id'].'" style="color: '.$groups[$i]['color'] .'">'.$groups[$i]['name'].'</a></td><td>'.$groups[$i]['description'].'</td><td>'.$combo.'</td><td>'.$status.'</td></tr>';
            }
        }
        $rows .= '<tr><td colspan=2><button type="button" onclick="setDefault();" '.$disable_button.'>Set default group</button></td><td>&nbsp;</td><td><button onclick="sendRequests();" type="button">Go</button></td><td>&nbsp;</td></tr>';
        return '<table class="sortable">'.$rows."</table>";
    }
    
    
    function render_group_manager(){
        global $site_settings;
        $status_strings = array('Pending','Joined','leader');
        
        $user_list = get_table_contents("", "", "", False, "SELECT username, users.user_id, user_join_date, user_facebook, user_email, last_active, ranks.image, description, groups.name AS gname, ranks.name AS rname, user_show_mail, user_show_facebook, user_avatar, user_color, r2.image AS r2img, r2.name AS r2name, user_post_count, groups.rank AS grank, user_status FROM users
LEFT JOIN ranks
ON ranks.id = users.user_rank
LEFT JOIN groups
ON groups.id = users.user_default_group
LEFT JOIN ranks AS r2
ON groups.rank = r2.id
LEFT JOIN user_groups
ON user_groups.user_id = users.user_id
WHERE user_password != '' AND user_group_id = '".$_GET['g']."'", array("user_join_date","last_active"));
        $root = "..";
        for ($i = 0; $i < count($user_list); $i++) {

        $user_list[$i]['user_email'] = str_replace("@", " [at] ", $user_list[$i]['user_email']);
        $user_list[$i]['user_facebook'] = '<a href="https://www.facebook.com/'.$user_list[$i]['user_facebook'].'">'.$user_list[$i]['user_facebook'].'</a>';

        if($user_list[$i]['user_show_facebook'] != "1"){
            $user_list[$i]['user_facebook'] = "hidden";
        }
        if($user_list[$i]['user_show_mail'] != "1"){
            $user_list[$i]['user_email'] = "hidden";
        }
        if($user_list[$i]['image'] != ""){
            $user_list[$i]['user_rankimage']= '<img src="./ranks/'.$user_list[$i]['image'].'">';
        }else{
            $user_list[$i]['user_rankimage'] = "";
        }
        if($user_list[$i]['user_avatar']== ""){
            $user_list[$i]['user_avatar']= $root.'/theme/'.$site_settings['template']."/icons/default_profile.png";
        }
        $user_list[$i]['user_avatar'] = '<img style="max-width: 32px; max-height: 32px;" src="'.$user_list[$i]['user_avatar'].'">';

        $user_list[$i]['rank'] = "";
        if($user_list[$i]['rname'] != ""){
            $user_list[$i]['rank'] = $user_list[$i]['rname'];
        }else{
           if($user_list[$i]['grank'] > 0){
               $user_list[$i]['rank'] = $user_list[$i]['r2name'];
               if($user_list[$i]['r2img'] != ""){
                   $user_list[$i]['user_rankimage']= '<img src="'.$root.'/ranks/'.$user_list[$i]['r2img'].'">';
               }

           }else{
               if($user_list[$i]['description'] != ""){
                   $user_list[$i]['rank'] = $user_list[$i]['description'];
               }
           }
        }

        if($user_list[$i]['user_rankimage'] != ""){
            $user_list[$i]['rank'] = $user_list[$i]['user_rankimage']."<br>".$user_list[$i]['rank'];
        }

        if($user_list[$i]['user_color'] == ""){
            $user_list[$i]['user_color'] = "#000";
        }

        $select = '<td><input type="checkbox" name="'.$user_list[$i]['user_id'].'"></td>';


        $member_rows .= '<tr>'.$select.'<td>'.$user_list[$i]['user_avatar'].'</td><td><a style="color: '.$user_list[$i]['user_color'].'" href="'.$root.'/profile.php?uid='.$user_list[$i]['user_id'].'">'.$user_list[$i]['username'].'</a></td><td>'.$user_list[$i]['rank'].'</td><td>'.$user_list[$i]['user_post_count'].'</td><td>'.$user_list[$i]['user_join_date'].'</td><td>'.$user_list[$i]['last_active'].'</td><td>'.$status_strings[$user_list[$i]['user_status']].'</td></tr>';
    }
    return $member_rows;
}
    
    function is_action_allowed($groups, $group, $action){
        for ($i = 0; $i < count($groups); $i++) {
            if($groups[$i]['id'] == $group && $groups[$i]['type'] < 2){
                if($action == 'leave'){
                    return true;
                }elseif ($action == 'join' && $groups[$i]['type'] == "0") {
                    return true;
                }  elseif ($action == 'request' && $groups[$i]['type'] == "1") {
                    return true;
                }
            }
        }
        return false;
    }

    function main($module){
        global $current_user, $language;
        switch($module){
            case 'list_groups':
                if($_GET['mode'] == 'set_default'){
                    if (!in_array($_POST['group'], $current_user['groups'])) {
                        $this->template = "failure_module";
                        $this->vars=array(
                            'SUCCESSMSG' => $language['notifications']['user_set_default_fail']."<br> <a href=\"".$_SERVER['HTTP_REFERER']."\">back</a> "
                        );
                        log_event("USER", $current_user['name'], $_SERVER['REMOTE_ADDR'], 'MANAGE groups', "User tried to set default group he is not member of");
                        break;
                    }
                    if(has_permission($current_user['permissions']['global'], 'u_change_default_group')){
                        user_set_default_group($current_user['uid'], $_POST['group']);
                        $this->template = "success_module";
                        $this->vars=array(
                            'SUCCESSMSG' => $language['notifications']['user_set_default']."<br> <a href=\"".$_SERVER['HTTP_REFERER']."\">back</a> "
                        );
                        break;
                    }else{
                        $this->template = "failure_module";
                        $this->vars=array(
                            'SUCCESSMSG' => $language['notifications']['user_set_default_denied']."<br> <a href=\"".$_SERVER['HTTP_REFERER']."\">back</a> "
                        );
                        break;
                    }
                }elseif($_GET['mode'] == 'update'){
                    $groups = get_table_contents("groups", "ALL");
                    $action = StringTrimRight($_POST['action'], 1);
                    $actions = explode(";", $action);
                    $user_groups = user_get_groups($current_user['uid'],true); //Get also pending
                    for ($i = 0; $i < count($actions); $i++) {
                        $action_parts = explode(":", $actions[$i]);
                        if($action_parts[1] != "none"){
                            if($this->is_action_allowed($groups,$action_parts[0],$action_parts[1])){
                                if($action_parts[1] == "join" && !in_array($action_parts[0],$user_groups)){
                                    group_add_memberById($action_parts[0], $current_user['uid'], 1);
                                }elseif($action_parts[1] == "request" && !in_array($action_parts[0],$user_groups)) {
                                    group_add_memberById($action_parts[0], $current_user['uid'], 0);
                                }else{//leave
                                    group_remove_member($current_user['uid'], $action_parts[0]);
                                }
                            }else{
                                log_event('USER', $current_user['name'], $_SERVER['REMOTE_ADDR'], 'MANAGE groups', "User tried to join/leave closed or secret group");
                                $this->template = "failure_module";
                                $this->vars=array(
                                    'SUCCESSMSG' => $language['notifications']['user_join_fail']."<br> <a href=\"".$_SERVER['HTTP_REFERER']."\">back</a> "
                                );
                                break(2);
                            }
                        }
                    }
                    $this->template = "success_module";
                    $this->vars=array(
                        'SUCCESSMSG' => $language['notifications']['user_join']."<br> <a href=\"".$_SERVER['HTTP_REFERER']."\">back</a> "
                    );
                    break;
                }
                $groups = get_table_contents("groups", "ALL");
                $groups = group_remove_hidden_selective($groups);
                $this->page_title = $language['module_titles']['groups_view'];
                $this->template = "view_groups";
                $this->vars=array(
                    'TABLE' => $this->render_groups($groups),
                );
            break;
            case 'manage_groups':
                $goups = get_table_contents('', '', '', false, "SELECT * FROM user_groups, groups WHERE groups.id=user_groups.user_group_id AND user_status=2 AND user_id='".$current_user['uid']."'");
                
                if($goups == false){
                    $this->template = "failure_module";
                    $this->vars=array(
                        'SUCCESSMSG' => $language['notifications']['user_not_leader']."<br> <a href=\"".$_SERVER['HTTP_REFERER']."\">back</a> "
                    );
                    break;
                }
                if (isset($_GET['g'])) {
                    if(in_array($_GET['g'], array_copy_dimension($goups, 'user_group_id'))){
                        if(isset($_POST['users'])){
                            $_POST['users'] = StringTrimRight($_POST['users'], 1);
                            $users_list = explode(";", $_POST['users']);
                            if($_POST['action'] == "approve"){
                                group_set_member_status($users_list ,$_GET['g'],1);
                            }elseif($_POST['action'] == "remove"){
                                group_remove_member($users_list ,$_GET['g']);
                            }elseif($_POST['action'] == "promote"){
                                group_set_member_status($users_list,$_GET['g'],2);
                            }elseif($_POST['action'] == "demote"){
                                group_set_member_status($users_list ,$_GET['g'],1);
                            }
                            $this->page_title = $language['module_titles']['groups_manage'];
                            $this->template = "success_module";
                            $this->vars=array(
                                'SUCCESSMSG' => $language['notifications']['user_manage_group']."<br> <a href=\"".$_SERVER['HTTP_REFERER']."\">back</a> "
                            );
                            break;
                        }else{
                            $this->page_title = $language['module_titles']['groups_manage'];
                            $this->template = "manage_groups";
                            $this->vars=array(
                                'ROWS' => $this->render_group_manager(),
                                'GROUP_NAME' => $goups[0]['name'],
                                'GROUP_color' => $goups[0]['color']
                            );
                            break;
                        }
                    }else{
                        log_event('USER', $current_user['name'], $_SERVER['REMOTE_ADDR'], 'MANAGE groups', "User tried to manage group hes not leading");
                        $this->template = "failure_module";
                        $this->vars=array(
                            'SUCCESSMSG' => $language['notifications']['user_manage_group_fail']."<br> <a href=\"".$_SERVER['HTTP_REFERER']."\">back</a> "
                        );
                        break;
                    }
                }else{
                    $groups_combo = '<select name="g">'.group_list_to_combo($goups).'</select>';
                    $this->page_title = $language['module_titles']['groups_manage'];
                    $this->template = "manage_groups_select";
                    $this->vars=array(
                        'select' => $groups_combo,
                    );
                    break;
                }
        }
    }
}