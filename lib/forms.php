<?php
function form_add($action = ''){
    global $current_user;
    if($action == ""){
        $action  = $_GET['a'];
    }
    $query = "INSERT INTO forms (user_id,time,action) VALUES (".$current_user['uid'].",".time().", '".$action."')";
    _mysql_query($query);
    $id = _mysql_insert_id();
    _mysql_query("DELETE FROM forms WHERE time < ".time()-(7 * 24 * 60 * 60));//cleanup
    return $id;
}

function form_delete_by_id($id){
    _mysql_query("DELETE FROM forms WHERE id=".$id);
}

function form_delete_by_user_id($id){
    _mysql_query("DELETE FROM forms WHERE user_id=".$id);
}


function form_is_valid($id,$action)
{
    global $current_user;
    $res = _mysql_query("SELECT id FROM forms WHERE id=".$id." AND action='".$action."' AND user_id='".$current_user['uid']."'");
    if($res){
        return(_mysql_num_rows($res)>0);
    }
    return false;
}