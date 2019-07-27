<?php
function form_add($action = ''){
    global $current_user;
    if($action == ""){
        $action  = $_GET['a'];
    }
    _mysql_prepared_query(array(
        "query" => "INSERT INTO forms (user_id,time,action) VALUES (:uid, :time, :action)",
        "params" => array(
            ":uid" => $current_user['uid'],
            ":time" => time(),
            ":action" => $action
        )
    ));
    $id = _mysql_insert_id();
    _mysql_prepared_query(array(
        "query" => "DELETE FROM forms WHERE time < :time",
        "params" => array(
            ":time" => time()-(7 * 24 * 60 * 60)
        )
    ));
    return $id;
}

function form_delete_by_id($id){
    _mysql_prepared_query(array(
        "query" => "DELETE FROM forms WHERE id=:id",
        "params" => array(
            ":id" => $id
        )
    ));
}

function form_delete_by_user_id($id){
    _mysql_prepared_query(array(
        "query" => "DELETE FROM forms WHERE user_id=:uid",
        "params" => array(
            ":uid" => $id
        )
    ), true);
}


function form_is_valid($id, $action)
{
    global $current_user;
    $res = _mysql_prepared_query(array(
        "query" => "SELECT id FROM forms WHERE id=".$id." AND action='".$action."' AND user_id=:uid",
        "params" => array(
            ":uid" => $current_user['uid']
        )
    ));
    if($res){
        return(_mysql_num_rows($res)>0);
    }
    return false;
}