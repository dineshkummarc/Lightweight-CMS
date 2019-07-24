<?php
class sessions{
    var $module_info = array(
        'title' => "sessions",
        'MODULES' => array(
            array('name' => 'sessions','permissions' => 'true'),
        )
    );

    /*
     * We are generating this table in PHP and not in JS to make security independent from JS
     */
    function sessions_html($sessions){
            $array = explode("\n",$_COOKIE['Session']);
            $_sid = secure_input($array[0]);
        $table = '<table class="sortable"><tbody>'
                . '<tr class="sortable_head"><td>Select</td><td>Session id</td><td>ip address</td><td>Browser info</td><td>started</td><td>Expires</td><td>hidden</td><tr>';
        for ($i = 0; $i < count($sessions); $i++) {
            $yesno = $sessions[$i]['hide'] ==  '0'? 'No' : 'Yes';
            $sid= $_sid ==  $sessions[$i]['session_id'] ? '<b>'.$sessions[$i]['session_id'].' (current session)</b>': $sessions[$i]['session_id'];
            $table .= '<tr>'.'<td><input type="checkbox" name="sid:'.$sessions[$i]['session_id'].'" value="'.$sessions[$i]['session_id'].'"></td>'.'<td>'.$sid.'</td>'.'<td>'.$sessions[$i]['ip_address'].'</td>'.'<td>'.$sessions[$i]['user_agent'].'</td>'.'<td>'.$sessions[$i]['start'].'</td>'.'<td>'.$sessions[$i]['End'].'</td>'.'<td>'.$yesno .'</td>'.'</tr>';
        }
        $table .= '</tbody></table>';
        return $table;
    }
    
    function main($module){
        global $current_user, $language;
        switch($module){
            case 'sessions':
                if($_GET['mode']=='update'){
                    $sql = "";
                    $list = [];
                    for ($i = 0; $i < count($_POST['sid']); $i++) {
                        if ($_POST['sid'][$i] != 'all') {
                            $list[] = $_POST['sid'][$i];
                        }
                    }
                    _mysql_prepared_query(array(
                        "query" => "DELETE FROM sessions WHERE user_id = :uid AND session_id IN(:list)",
                        "params" => array(
                            ":uid" => $current_user['uid'],
                            ":list" => $list
                        )
                    ));
                    _mysql_prepared_query(array(
                        "query" => "DELETE FROM forum_session WHERE user_id = :uid AND session_id IN(:list)",
                        "params" => array(
                            ":uid" => $current_user['uid'],
                            ":list" => $list
                        )
                    ));
                    $this->template = "success_module";
                    $this->vars=array(
                        'SUCCESSMSG' => $language['notifications']['session_remove'].'<br><a href="./ucp.php?id='.$_GET['id'].'&a=sessions">Go back</a>'
                    );
                    
                }else if($_GET['mode']=="notthis"){
                    $array = explode("\n",$_COOKIE['Session']);
                    $_sid = secure_input($array[0]);
                    _mysql_prepared_query(array(
                        "query" => "DELETE FROM sessions WHERE user_id = :uid AND session_id != :sid",
                        "params" => array(
                            ":uid" => $current_user['uid'],
                            ":sid" => $_sid
                        )
                    ));
                    _mysql_prepared_query(array(
                        "query" => "DELETE FROM  forum_session WHERE user_id = :uid AND session_id != :sid",
                        "params" => array(
                            ":uid" => $current_user['uid'],
                            ":sid" => $_sid
                        )
                    ));
                    $this->template = "success_module";
                    $this->vars=array(
                        'SUCCESSMSG' => $language['notifications']['session_remove_success'].'<br><a href="./ucp.php?id='.$_GET['id'].'&a=sessions">Go bavk</a>'
                    );
                }
                else{
                    $sessions = get_table_contents("sessions",'ALL'," WHERE user_id='".$current_user['uid']."' ORDER BY start ASC");
                    $this->page_title = $language['module_titles']['view_sessions'];
                    $this->template = "sessions";
                    $this->vars=array(
                        'TABLE' => $this->sessions_html($sessions)
                    );
                }
            break;
        }
    }
}