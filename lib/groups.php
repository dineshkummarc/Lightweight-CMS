<?php

function groups_list($fields = 'ALL') {
    return get_table_contents("groups", $fields);
}

function group_add($group_name) {
    global $current_user;
    log_event('ADMINISTRATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "MANAGE groups", 'Created group named '. $group_name);
    $query = "INSERT INTO groups VALUES (NULL, '0', '0', '0', '0', '', '', '" . $group_name . "');";
    $result = _mysql_query($query);
    $gid = 0;
    $query = "SELECT MAX(id) FROM groups";
    $result = _mysql_query($query);
    $new_group_id = _mysql_result($result, 0);
    if ($result) {
        $gid = $new_group_id;
    }
    return $gid;
}


function group_get_name_by_id($group_id) {
    $group_info = group_get_info_by_id($group_id);
    return $group_info[0]['name'];
}


function group_get_info_by_id($group_id) {
    return get_table_contents("groups", 'ALL', " where id = " .$group_id);
}


function group_list_to_combo($group_list = 0) {
    if ($group_list == 0) {
        $group_list = groups_list(array('name', 'id'));
    }
    return array_to_combo($group_list, 'name', 'id');
}



function ranks_get_by_id($rid)
{
    $ret = get_table_contents("ranks",'ALL'," WHERE id = '".$rid."'");
    return $ret;
}

function ranks_list($fields = 'ALL') {
    return get_table_contents(ranks, $fields);
}

function rank_list_to_combo($rank_list = 0) {
    if ($rank_list == 0) {
        $rank_list = ranks_list(array('name', 'id'));
    }
    return array_to_combo($rank_list, 'name', 'id');
}

function group_list_permissions($group_id, $forum_id = 0, $extended = false) {
    $cond = 'group_id = ' . $group_id;
    if(is_array($group_id)){
        $cond = 'group_id IN('.implode(", ", $group_id).')';
    }
    if ($extended) {
        $ret = get_table_contents("group_permissions", 'ALL', ' WHERE '.$cond.' AND forum_id = ' . $forum_id);
        if(!is_array($ret)){$ret = array();}
        return $ret;
    } else {
        $ret = array_copy_dimension(get_table_contents("group_permissions", 'ALL', ' WHERE '.$cond.' AND forum_id = ' . $forum_id), 'permission_id');
        if(!is_array($ret)){$ret = array();}
        return $ret;
    }
}

function groups_get_table() {
    $groups = groups_list();
    for ($i = 0; $i < count($groups); $i++) {
        $groups[$i]['COUNT'] = group_get_member_count($groups[$i]['id']);
    }
    return $groups;
}

function groups_get_table_html() {
    $groups = groups_get_table();
    $str = "";
    for ($i = 0; $i < count($groups); $i++) {
        $str .=
                '<tr>
<td class="GroupsTable">' . $groups[$i]['name'] . '</td>
<td class="GroupsTable">' . $groups[$i]['COUNT'] . '</td>
<td class="GroupsTable"><a href="acp.php?id=' . $_GET['id'] . '&a='.$_GET['a']. '&mode=groupmembers&gid=' . $groups[$i]['id'] . '">Members</a></td>
<td class="GroupsTable"><a href="acp.php?id=' . $_GET['id'] . '&a='.$_GET['a']. '&mode=groupsettings&gid=' . $groups[$i]['id'] . '">settings</a></td>
<td class="GroupsTable"><a href="acp.php?id=' . $_GET['id'] . '&a=manage_permissions&mode=group_permissions_manage&gid=' . $groups[$i]['id'] . '">Permissions</a></td>
<td class="GroupsTable"><a href="acp.php?id=' . $_GET['id'] . '&a='.$_GET['a']. '&mode=groupdelete&gid=' . $groups[$i]['id'] . '">Delete</a></td>
</tr>';
    }
    return $str;
}

function group_get_member_count($group_id) {
    $query = 'SELECT COUNT(*) FROM user_groups WHERE user_group_id=\'' .$group_id . '\';';
    $result = _mysql_query($query);
    return _mysql_result($result, 0);
}

function group_get_member_list_by_id($gid, $fields = 'ALL') {
    return get_table_contents("user_groups", $fields, ' WHERE user_group_id = ' . $gid);
}

function get_member_list($gid) {
    $group_members = get_table_contents("", "", "", false, "SELECT users.user_id, username, user_join_date, user_post_count, user_default_group, user_status  FROM user_groups
LEFT JOIN users
ON users.user_id = user_groups.user_id
WHERE user_group_id = '".$gid."' AND users.user_id IS NOT NULL",array("user_join_date"));
    return $group_members;
}

//0 - Dis_approved
//1 - Approved (default)
//2 - leader
function group_set_member_status($member, $gid, $status = 1) {
    if (!is_array($member)) {
        $member = array($member);
    }
    
    $sql = 'UPDATE user_groups SET user_status=' . $status . ' WHERE user_id IN (' . implode(',', $member) . ') AND user_group_id = ' . $gid;
    $result = _mysql_query($sql);
    if ($result) {
        return count($member);
    }
    return false;
}

function group_get_member_status($member, $gid) {
    if (is_array($member)) {
        $member = implode(',', $member);
    }
    $status = get_table_contents("user_groups", array('user_id', 'user_status'), ' WHERE user_id IN (' . $member . ') AND user_group_id = ' . $gid);
    return $status;
}

function group_remove_member($member, $gid) {
    if (!is_array($member)) {
        $member = array($member);
    }
    $member_s = implode(',', $member);
    $sql = 'DELETE FROM user_groups WHERE user_id IN (' . $member_s . ') AND user_group_id=' . $gid;
    $result = _mysql_query($sql);
    if (!$result) {
        return false;
    }
    for ($i = 0; $i < count($member); $i++) {
        try {// In case user have no groups left.
            $last_group = user_get_last_group($member[$i]);
        } catch (Exception $e) {

        }
        if (isset($last_group)) {
            group_set_default($member[$i], $last_group);
        }
    }
    return true;
}

function group_set_default($member, $gid) {
    if (is_array($member)) {
        $member = implode(',', $member);
    }
    $sql = "UPDATE users SET user_default_group='" . $gid . "' WHERE user_id IN (" . $member . ")";
    $result = _mysql_query($sql);
    if (!$result) {
        throw new Exception('Failed to set default group');
    }
    $group_info = group_get_info_by_id($gid);
    //Success? Update colors as well
    $sql = "UPDATE users SET user_color ='" . $group_info[0]['color'] . "' WHERE user_id IN (" . $member . ")";
    _mysql_query($sql);
    return $result;
}


function group_add_member($group_id, $members, $Status, $make_default = false) {
    if (!is_array($members)) {
        $members = explode("\n", $members);
    }
    $members_ids = user_get_id_by_name($members);
    $members_ids_one_dimensional = array_copy_dimension($members_ids, 'user_id');
    $query = 'SELECT user_id FROM user_groups WHERE user_id IN (' . implode(',', $members_ids_one_dimensional) . ') AND user_group_id = ' .$group_id;
    $result = _mysql_query($query);
    if ($result) {
        $rows = _mysql_num_rows($result);
        for ($i = 0; $i < $rows; $i++) {
            $ids[] = _mysql_result($result, $i);
        }
        $members_ids_one_dimensional = array_remove_value($members_ids_one_dimensional, $ids);
    }
    if (!$members_ids_one_dimensional) {
        throw new Exception('User is already in that group.');
    }
    $insert_str = 'INSERT INTO user_groups VALUES ';
    for ($i = 0; $i < count($members_ids_one_dimensional); $i++) {
        $insert_str .='(' . $members_ids_one_dimensional[$i] . ',' .$group_id . ',' . $Status . '),';
    }
    $insert_str = StringTrimRight($insert_str, 1);

    if ($make_default) {
        group_set_default($members_ids_one_dimensional,$group_id);
    }

    return _mysql_query($insert_str);
}

function group_add_memberById($group, $member, $status) {
    _mysql_query("INSERT INTO user_groups VALUES ('".$member."','".$group."','".$status."')");
}

function array_remove_value($array, $value) {
    if (!is_array($value)) {
        $value = array($value);
    }
    for ($i = 0; $i < count($array); $i++) {
        $not_found = true;
        for ($j = 0; $j < count($value); $j++) {
            if ($array[$i] == $value[$j]) {
                $not_found = false;
                break;
            }
        }
        if ($not_found) {
            $new_arr[] = $array[$i];
        }
    }
    if (!is_array($new_arr)) {
        $new_arr = array();
    }
    return $new_arr;
}

function group_set_info_by_id($gid)
{
    global $current_user;
    log_event('ADMINISTRATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "MANAGE groups", 'Updated group info for '. $_POST['name']);
    if ($gid==0 || $gid == ""){return false;}
    $table_columns = DBGetColumnsList("groups");
    $tobase = 'UPDATE groups SET ';
    for($i = 1;$i < count($table_columns);$i++) 
    {
            $data = $_POST[$table_columns[$i]];
            if(isset($data)){
                $tobase .= $table_columns[$i]." = '".$data."',";
            }

    }
    $tobase = StringTrimRight($tobase,1);
    $tobase .= ' WHERE id='.$gid;
    return _mysql_query($tobase);
}

function group_remove_hidden_selective($groups){
    global $current_user;
    $my_groups = user_get_groups_full($current_user['uid'], false);
    for($i = 0;$i < count($groups); $i++){
       if($groups[$i]['type'] == "3"){//secret
           $contains = false;
           for($j = 0;$j < count($my_groups); $j++){
               if($my_groups[$j]['id'] == $groups[$i]['id']){
                   $contains = true;
               }
           }
           if(!$contains){
               unset($groups[$i]);
           }
       }
    }
    $groups = array_values($groups);
    return $groups;
}