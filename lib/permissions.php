<?Php
//Constants
$PERMISSION_LIST = permissions_list();
$PERMISSION_TO_STRING = permission_get_names();
function my_if($cond)
{
    return $cond;
}

function has_permission($user_permissions,$permissions_to_check = ''){
    global $PERMISSION_LIST;
    if($user_permissions==NULL){return false;}
    foreach ($PERMISSION_LIST as $permission) {
        if (array_search($permission['name'], $user_permissions) !== false){
            $permissions_to_check = str_replace($permission['name'], "true" , $permissions_to_check);
        }else{
            $permissions_to_check = str_replace($permission['name'], "false" , $permissions_to_check);
        }

    }


    return eval("return(".$permissions_to_check.");");
}

function has_permission_class($user_permissions,$permission_class){
    if($user_permissions==NULL){return false;}
    foreach ($user_permissions as $permission) {
        if(stringleft($permission,2) == $permission_class){
            return true;
        }
    }
    return false;
}

function permission_get_names() {
    $permissions = get_table_contents("permissions", 'ALL');
    for ($i = 0; $i < count($permissions); $i++) {
        $ret[$permissions[$i]['permission_id']] = $permissions[$i]['name'];
    }
    return $ret;
}

function permissions_to_string($permissions)
{
    global $PERMISSION_TO_STRING ;
    if(!$PERMISSION_TO_STRING){
        $PERMISSION_TO_STRING = permission_get_names();
    }
    $ret = array();
    for($i = 0; $i < count($permissions); $i++)
    {
        $ret[] = $PERMISSION_TO_STRING[$permissions[$i]];
    }
    return $ret;
}


/*  #FUNCTION# ;===============================================================================

name...........: permissions_list
description ...:
Syntax.........: permissions_list([$fields = 'ALL'])
Parameters ....: $fields - [Optional]
Author ........:
Modified.......:
Remarks .......:
Related .......:
Parameters ....:
Link ..........:
Example .......:
;==========================================================================================*/
function permissions_list($fields = 'ALL'){
	return get_table_contents("permissions",$fields);
}
?>