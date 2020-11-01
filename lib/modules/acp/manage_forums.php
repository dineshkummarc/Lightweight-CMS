<?php
class manage_forums{
    var $module_info = array(
        'title' => "Manage forums",
        'MODULES' => array(
            array('name' => 'manage_forums','permissions' => 'a_manage_forums'),
        )
    );
    
    function make_form($forums,$forum_data){
        $form = array(
            array(
                'method' => 'post',
                'url' => './acp.php?id='.$_GET['id'].'&a=manage_forums&mode=save&f='.$_GET['f']
            ),
            array(
                'name' => 'Forum type',
                'input_name' => 'forum_type',
                'type' => 'combo',
                'data' => array(
                    '0' => 'forum',
                    '1' => 'site',
                    '2' => 'gallery',
                    '3' => 'blog'
                ),
                'value' => $forum_data[0]['forum_type']
            ),
            array(
                'name' => 'Parent forum',
                'input_name' => 'parent_id',
                'type' => 'combo',
                'data' => $forums,
                'value' =>  $forum_data[0]['parent_id']
            ),
            array(
                'name' => 'Forum name',
                'input_name' => 'forum_name',
                'type' => 'input',
                'value' => $forum_data[0]['forum_name']
            ),
            array(
                'name' => 'comments forum',
                'input_name' => 'comment_id',
                'type' => 'combo',
                'data' => $forums,
                'value' => $forum_data[0]['comments']
            ),
            array(
                'name' => 'Copy permissions from forum',
                'input_name' => 'permissions_id',
                'description' => '',
                'type' => 'combo',
                'data' => $forums,
                'value' => '0'
            ),
            array(
                'name' => 'Forum password',
                'input_name' => 'forum_password',
                'type' => 'input',
                'value' => $forum_data[0]['forum_password']
            ),
            array(
                'name' => 'display on legend',
                'input_name' => 'display',
                'type' => 'checkbox',
                'value' => $forum_data[0]['display']
            ),
            array(
                'name' => 'description',
                'input_name' => 'description',
                'type' => 'text',
                'value' => $forum_data[0]['description']
            ),
            array(
                'name' => 'Google fragment',
                'input_name' => 'google_fragment',
                'type' => 'checkbox',
                'value' => $forum_data[0]['google_fragment']
            )
        );
        return $form;
    }
    
    function main($module){
        global $current_user, $language;
        $this->page_title = $language['module_titles']['manage_forums'];
        switch($module){
            case 'manage_forums':
                if(!isset($_GET['mode'])){$_GET['mode']= 'view';}
                switch($_GET['mode']){
                    case 'setorder':
                        _mysql_prepared_query(array(
                        "query" => "UPDATE forum SET display_order=:order WHERE forum_id=:fid",
                            "params" => array(
                                ":order" => $_POST['order'],
                                ":fid" => $_POST['fid']
                            )
                        ));
                        die();
                        break;
                    case 'view':
                        $this->template = "manage_forums";
                        $this->vars['forums'] = "";
                        $forums = forum_list_all();
                        $forums_js = array_to_js($forums, "forums", true);
                        for ($i = 0; $i < count($forums); $i++) {
                            $this->vars['forums'] .= "<tr>
      <td><a href=\"../?f=".$forums[$i]['forum_id']."\">".$forums[$i]['forum_name']."</a></td>
      <td>".$forums[$i]['description']."</td>
      <td><a href=\"#\" onclick=\"editpermissions(".$forums[$i]['forum_id'].");\">Edit permissions</a></td>
      <td><a href=\"./acp.php?id=10&a=manage_forums&mode=edit&f=".$forums[$i]['forum_id']."\">Edit</a></td>
      <td><a href=\"./acp.php?id=10&a=manage_forums&mode=delete&f=".$forums[$i]['forum_id']."\">Delete</a></td>
    </tr>";
                        }
                        $this->vars['forums_js'] = $forums_js;
                        break;
                    case 'edit':
                        $this->template = "manage_forums_edit";
                        $forum_combo = forum_list_to_combo();
                        $this->vars['parents'] = $forum_combo;
                        $this->vars['comments'] = $forum_combo;
                        $forums = array_to_key_value(forum_list_all(array('forum_id', 'forum_name'),true),'forum_id','forum_name');
                        if(isset($_GET['f']) && count(forum_get_info($_GET['f'])) == 1){
                            $forum_data = forum_get_info($_GET['f']);
                            $form = $this->make_form($forums,$forum_data);
                            $this->page_title = $language['module_titles']['manage_forums_edit'].$forum_data[0]['forum_name'] ;
                            $this->vars['form'] = build_form($form) ;
                        }else{
                            $forum_data = array(
                                    0 => Array(
                                        'forum_id' => 0,
                                        'parent_id' => 0,
                                        'forum_name' => '',
                                        'description' =>  '',
                                        'forum_type' => 0,
                                        'comments' => 0,
                                        'display' => 0,
                                        'display_order' => 0,
                                        'forum_password' => '',
                                        'google_fragment' => 0
                                    )
                            );    
                            $_GET['f'] = '0';
                            $form = $this->make_form($forums,$forum_data);
                            $this->vars['form'] = build_form($form) ;
                        }
                        
                        break;
                    case 'save':
                        //$_POST['comment_id'] = ch...
                        $this->template = "success_module";
                        if($_POST['display'] == ""){
                            $_POST['display'] = '0';
                        }
                        if (forum_exists($_GET['f'])) {
                            log_event('ADMINISTRATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "MANAGE groups", 'Edited forum named '. $_POST['forum_name']);
                            forum_edit($_GET['f'], $_POST['forum_type'], $_POST['parent_id'], $_POST['forum_name'], $_POST['description'],$_POST['comment_id'],$_POST['display'], $_POST['forum_password'], $_POST['google_fragment']);
                            $this->vars['SUCCESSMSG'] = $language['notifications']['forum_update'];
                        }else{
                            log_event('ADMINISTRATOR', $current_user['name'], $_SERVER['REMOTE_ADDR'], "MANAGE groups", 'Created forum named '. $_POST['forum_name']);
                            forum_add($_POST['forum_type'], $_POST['parent_id'], $_POST['forum_name'], $_POST['description'],$_POST['comment_id'],$_POST['display'],"0", $_POST['forum_password'], $_POST['google_fragment']);
                            $this->vars['SUCCESSMSG'] = $language['notifications']['forum_create'];
                        }
                        //redirect(3, "./acp.php?id=".$_GET['id']."&a=manage_forums" ); 
                        break;
                    case 'delete':
                        $this->vars['forum'] = forum_get_info($_GET['f']);
                        $this->page_title = $language['module_titles']['manage_forums_delete'].$this->vars['forum'][0]['forum_name'] ;
                        $this->template = "manage_forums_delete";
                        $this->vars['forums'] = forum_list_to_combo();
                        break;
                    case 'rm':
                        if(!isset($_POST['childs'])){
                           $this->template = "failure_module";
                           $this->vars['SUCCESSMSG'] = $language['notifications']['forum_action_not_set'];
                           redirect(3, "./acp.php?id=".$_GET['id']."&a=manage_forums" ); 
                        }else{
                            forum_delete($_GET['f'], $_POST['childs'],$_POST['parent_id']);
                            $this->template = "success_module";
                            $this->vars['SUCCESSMSG'] = $language['notifications']['forum_delete'];
                        }
                        break;
                    
                }
                break;
                
        }
    }
}