<?php
class manage_attachments{
    var $module_info = array(
        'title' => "manage_attachments",
        'MODULES' => array(
            array('name' => 'manage_attachments','permissions' => 'a_manage_modules'),
            array('name' => 'manage_orphaned','permissions' => 'a_manage_modules')
        )
    );
    
    function save_attachments() {
        $_POST['orphaned_data'] = StringTrimRight($_POST['orphaned_data'], 1);
        $triples = explode(":", $_POST['orphaned_data']);
        $delete = array();
        $update = "";
        for($i = 0; $i < count($triples);$i++){
            $subTriples = explode(";",$triples[$i]);
            $subTriples[0] = intval($subTriples[0]);
            $subTriples[1] = intval($subTriples[1]);
            if($subTriples[2] == "true"){
                $delete[] = $subTriples[0];
            }else{
                $tid = post_get_topic($subTriples[1]);
                $fid = post_get_forum($subTriples[1]);

                if($tid != ""){
                    $update .= "UPDATE attachments SET post_id='".$subTriples[1]."', topic_id='". $tid ."', forum_id='". $fid ."'  WHERE id='".$subTriples[0]."';\n";
                }
            }
        }
        $files =  get_table_contents('attachments', array("actual_name","is_image"), "WHERE id IN (".  implode(', ', $delete).")");
        $files = $files == null ? array() : $files;
        foreach ($files as $file) {
            if($file['is_image'] == '1'){
                @unlink("../images/large/".$file['actual_name']);
                @unlink("../images/small/".$file['actual_name']);
            }
            @unlink("../uploads/".$file['actual_name']);
            _mysql_query("DELETE FROM attachments WHERE id IN (".  implode(', ', $delete).")");
        }
        if($update != ""){
            _mysql_query($update);
        }
        
    }

    function main($module){
        global $language;
        switch($module){
            case 'manage_attachments':
                if(isset($_POST['orphaned_data']) && $_POST['orphaned_data'] != ""){
                    $this->page_title = $language['module_titles']['manage_attachments'];
                    $this->template = "success_module";
                    $this->save_attachments();
                    $this->vars=array(
                        'SUCCESSMSG' => $language['notifications']['success']." <br><br><a href='./acp.php?id=".$_GET['id']."&a=".$_GET['a']."' style='color: #EEEEEE;'><b>Go back</b></a><br>"
                    );
                }else{
                    $this->page_title = $language['module_titles']['manage_attachments'];
                    $this->template = "manage_orphaned";
                    $orphaned = @get_table_contents("attachments", "ALL");
                    $table_start =  '<table id="oprhaned" class="sortable"><tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td><a href="#" onclick="selectAll()">Select all</a></td></tr><tr><td><b>Download</b></td><td><b>size</b></td><td><b>time</b></td><td><b>Download count</b></td><td><b>View post</b></td><td><b>Set post</b></td><td><b>Delete</b></td><tr>';
                    $table_end = '<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td><button type="button" onclick="submitForm()">Submit</button></td></tr></table>';
                    $table_middle = "";
                    for($i = 0; $i < count($orphaned);$i++){
                        $table_middle .= '<tr><td><a href="./upload.php?a=download&file='.$orphaned[$i]['id'].'">'.$orphaned[$i]['file_name'].'</a></td>
                            <td>'.bytes_to_size($orphaned[$i]['size']).'</td><td>'.$orphaned[$i]['time'].'</td><td>'.$orphaned[$i]['downloads'].'</td><td><a href="../?p='.$orphaned[$i]['post_id'].'"">view</a></td><td><input type="hidden" value="'.$orphaned[$i]['id'].'"><input class="short" value="'.$orphaned[$i]['post_id'].'" type="text"></td><td><input name="delete" type="checkbox"></td></tr>';
                    }
                    $this->vars=array(
                        'ATTACHMENTS' => $table_start.$table_middle.$table_end,
                    );
                }
            break;
            case 'manage_orphaned':
                if(isset($_POST['orphaned_data']) && $_POST['orphaned_data'] != ""){
                    $this->page_title = $language['module_titles']['manage_orphaned_attachments'];
                    $this->template = "success_module";
                    $this->save_attachments();
                    $this->vars=array(
                        'SUCCESSMSG' => $language['notifications']['success']." <br><br><a href='./acp.php?id=".$_GET['id']."&a=".$_GET['a']."' style='color: #EEEEEE;'><b>Go back</b></a><br>"
                    );
                }else{
                    $this->page_title = $language['module_titles']['manage_orphaned_attachments'];
                    $this->template = "manage_orphaned";
                    $orphaned = @get_table_contents("", "ALL", "", False, "SELECT * FROM (SELECT attachments.*, post.id AS pid FROM attachments LEFT OUTER JOIN post ON attachments.post_id=post.id) AS t1 WHERE pid IS NULL ");
                    $table_start =  '<table id="oprhaned" class="sortable"><tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td><a href="#" onclick="selectAll()">Select all</a></td></tr><tr><td><b>Download</b></td><td><b>size</b></td><td><b>time</b></td><td><b>Download count</b></td><td><b>Set post</b></td><td><b>Delete</b></td><tr>';
                    $table_end = '<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td><button type="button" onclick="submitForm()">Submit</button></td></tr></table>';
                    $table_middle = "";
                    for($i = 0; $i < count($orphaned);$i++){
                        $table_middle .= '<tr><td><a href="./upload.php?a=download&file='.$orphaned[$i]['id'].'">'.$orphaned[$i]['file_name'].'</a></td>
                            <td>'.$orphaned[$i]['size'].'</td><td>'.$orphaned[$i]['time'].'</td><td>'.$orphaned[$i]['downloads'].'</td><td><input type="hidden" value="'.$orphaned[$i]['id'].'"><input value="'.$orphaned[$i]['post_id'].'" type="text"></td><td><input name="delete" type="checkbox"></td></tr>';
                    }
                    $this->vars=array(
                        'ATTACHMENTS' => $table_start.$table_middle.$table_end,
                    );
                }
            break;
        }
    }
}