<?php
class account_settings{
    var $module_info = array(
        'title' => "Account Setings",
        'MODULES' => array(
            array('name' => 'account_settings','permissions' => 'u_view_profile'),
        )
    );
    
 function addMissingParams(){
     if(!isset($_POST['show_facebook'])){
         $_POST['show_facebook'] = '0';
     }
 }

function update_current_user(){
    $error = 0;
    global $current_user;
    if(!has_permission($current_user['permissions']['global'], "u_edit_own_profile")){
        return;
    }

    if (!user_validate_name($_POST['username'])){return 8;}
    $change_pass = ($_POST['password'] == $_POST['password_confirm'] && $_POST['password'] != '');
    $password_mismatch = ($_POST['password'] != $_POST['password_confirm'] && $_POST['password'] != '');
    $change_mail = user_validate_email($_POST['email']);
    $cover = secure_url($_POST['cover']);
    $change_msn = secure_facebook($_POST['facebook']);
    if($password_mismatch){$error = 1;}
    
    if(!has_permission($current_user['permissions']['global'], "u_use_sig")){
        $_POST['signature'] = "";
    }
    if(!has_permission($current_user['permissions']['global'], "u_can_use_avatar")){
        $_POST['avatar'] = "";
    }

    $this->addMissingParams();
    
    $cmd = 'UPDATE users';
    $update = 'SET user_avatar=\''.$_POST['avatar'].'\', user_show_facebook=\''.$_POST['show_facebook'].'\', user_show_mail=\''.$_POST['show_email'].'\', user_signature=\''.$_POST['signature'].'\', '." cover='".$cover."', about='".$_POST['about']."', cover_h_offset='".$_POST['cover_h_offset']."', ";
    $cmd_end = 'WHERE user_id='.$current_user['uid']; 
    if($change_pass){
        $salt = random_string(10);
        $update .= 'user_password=\''.md5($_POST['password'].$salt).'\', ';
        $update .= 'salt=\''.$salt.'\', ';   
    }
    if($change_mail){$update .= 'user_email=\''.$_POST['email'].'\', ';}else{$error +=16;}
    if($change_msn){$update .= 'user_facebook=\''.$_POST['facebook'].'\', ';}
    $update = StringTrimRight($update,2);
    $sql=$cmd."\n".$update."\n".$cmd_end;
    $result = _mysql_query($sql);
    if(!$result){$error += 2;}

    return $error;
}
    function main($module){
        global $current_user, $language;
        switch($module){
            case 'account_settings':
                if(isset($_GET['mode'])){
                    if($_GET['mode'] == 'updateuser'){
                        $ret = $this->update_current_user();
                        $this->template = "success_module";
                        $this->vars=array(
                            'SUCCESSMSG' => $language['notifications']['user_update']."<br> <a href=\"".$_SERVER['HTTP_REFERER']."\">back</a> "
                        );
                        break;
                    }
                }
                $selected_user = array();

                $selected_user = user_get_info_by_id($current_user['uid']);
                $selected_user = $selected_user[0];
                $selected_user['user_show_facebook'] = int_to_checked($selected_user['user_show_facebook']) ;
                $selected_user['user_show_mail'] = int_to_checked($selected_user['user_show_mail']);
                $selected_user['user_founder'] = int_to_checked($selected_user['user_founder']);

                $this->page_title = $language['module_titles']['account_settings'];
                $this->template = "users_manage";
                $this->vars=array(
                    'VAR' => 'log',
                    'SELECTEDUSER' => $selected_user,
                    'ALLOW_SIG' => has_permission($current_user['permissions']['global'], "u_use_sig"),
                    'ALLOW_AVATAR' => has_permission($current_user['permissions']['global'], "u_can_use_avatar"),
                    'ALLOW_EDIT' => has_permission($current_user['permissions']['global'], "u_edit_own_profile")
                );
            break;
        }
    }
}

?>