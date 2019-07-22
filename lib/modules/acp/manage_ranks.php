<?php

class manage_ranks
{
    var $module_info = array(
        'title' => "logs",
        'MODULES' => array(
            array('name' => 'manage_ranks', 'permissions' => 'a_manage_ranks'),
        )
    );

    function delete_rank() {
        $_POST['id'] = abs(intval($_POST['id']));
        return _mysql_prepared_query(array(
            "query" => 'DELETE FROM ranks WHERE id = :rid',
            "params" => array(
                ":rid" => $_POST['id']
            )
        ));
    }

    function alter_rank() {
        if ($_POST['image'] != "" && !validate_file_name($_POST['image'], 'jpg|png|gif|bmp')) {
            return false;
        }
        if ($_POST['name'] == '') {
            return false;
        }
        if ($_POST['id'] > 0) {
            $sql = "UPDATE ranks SET image=:image, name=:name, special=:special, required_posts=:required_posts WHERE id=:rid";
        } else {
            $sql = "INSERT INTO ranks VALUES (NULL, :image, :name, :special, :required_posts);";
        }
        $result = _mysql_prepared_query(array(
            "query" => $sql,
            "params" => array(
                ":image" => $_POST['image'],
                ":name" => $_POST['name'],
                ":special" => $_POST['special'],
                ":required_posts" => $_POST['required_posts'],
                ":rid" => $_POST['id']
            )
        ));
        return $result;
    }

    function main($module) {
        global $language;
        switch ($module) {
            case 'manage_ranks':
                if ($_GET['mode'] == "alter_rank") {
                    $this->alter_rank();
                    $this->template = "success_module";
                    $this->vars = array(
                        'SUCCESSMSG' => $language['notifications']['rank_create'] . "<br> <a href=\"" . $_SERVER['HTTP_REFERER'] . "\">back</a> "
                    );
                } elseif ($_GET['mode'] == "delete_rank") {
                    $this->delete_rank();
                    $this->template = "success_module";
                    $this->vars = array(
                        'SUCCESSMSG' => $language['notifications']['rank_delete'] . "<br> <a href=\"" . $_SERVER['HTTP_REFERER'] . "\">back</a> "
                    );
                } else {
                    $this->page_title = $language['module_titles']['ranks_manage'];
                    $this->template = "manage_ranks";
                    $ranksJS = array_to_js(get_table_contents("RANKS"), 'ranks', true);
                    $this->alter_rank();
                    $rankimagesJS = array_to_js(get_directory_file_list('../ranks', 'jpg|png|gif|bmp'), 'rankimages');
                    $this->vars = array(
                        'RANKS_JS' => $ranksJS,
                        'RANKS_IMAGES_JS' => $rankimagesJS,
                    );
                }
                break;
        }
    }
}