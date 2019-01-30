<?php
class manage_ranks{
    var $module_info = array(
        'title' => "logs",
        'MODULES' => array(
            array('name' => 'manage_ranks','permissions' => 'a_manage_ranks'),
        )
    );

    function main($module){
        global $language;
        switch($module){
            case 'manage_ranks':
                if($_GET['mode'] == "alter_rank"){
                    alter_rank();
                    $this->template = "success_module";
                    $this->vars=array(
                        'SUCCESSMSG' => $language['notifications']['rank_create']."<br> <a href=\"".$_SERVER['HTTP_REFERER']."\">back</a> "
                    );
                }elseif($_GET['mode'] == "delete_rank"){
                    delete_rank();
                    $this->template = "success_module";
                    $this->vars=array(
                        'SUCCESSMSG' => $language['notifications']['rank_delete']."<br> <a href=\"".$_SERVER['HTTP_REFERER']."\">back</a> "
                    );
                }else{
                    $this->page_title = $language['module_titles']['ranks_manage'];
                    $this->template = "manage_ranks";
                    $ranksJS = array_to_js(get_table_contents("RANKS"),'ranks',true);alter_rank();
                    $rankimagesJS = array_to_js(get_directory_file_list('../ranks','jpg|png|gif|bmp'),'rankimages');
                    $this->vars=array(
                        'RANKS_JS' => $ranksJS,
                        'RANKS_IMAGES_JS' => $rankimagesJS,
                    );
                }
            break;
        }
    }
}