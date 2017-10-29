<?php
class bbcode{
    var $module_info = array(
        'title' => "bbcode",
        'MODULES' => array(
            array('name' => 'manage_bbcode','permissions' => 'a_define_bbcode'),
        )
    );
    function main($module){
        global $language;
        switch($module){
            case 'manage_bbcode':
                $this->page_title = $language['module_titles']['manage_bbcode'];
                $this->template = "bbcode_manage";
                if(isset($_GET["edit"])){
                    $this->page_title = $language['module_titles']['manage_bbcode_edit'];
                    if ($_GET["edit"] == "0"){
                        $bbcode_hint="";
                        $bbcode="";
                        $bbcode_html="";
                        $bbcode_show="";
                        $bbcode_helper="";
                    }else{
                        $result = _mysql_query("SELECT * FROM bbcode where bbcode_id=".$_GET["edit"]);
                        $bbcode_hint = _mysql_result($result,0,"bbcode_hint");
                        $bbcode = _mysql_result($result,0,"bbcode");
                        $bbcode_html = _mysql_result($result,0,"bbcode_html");
                        $bbcode_helper = _mysql_result($result,0,"attrib_func");
                        $bbcode_show = int_to_checked(_mysql_result($result,0,"bbcode_show"));
                    }
                    $this->template = "bbcode_edit";

                }elseif(isset($_GET["save"])){
                    if(to_base_bbcode()){
                        $this->template = "success_module";
                        $this->vars=array(
                            'SUCCESSMSG' => $language['notifications']['bbcode_update']." <br><br><a href='./acp.php?id=".$_GET['id']."&a=".$_GET['a']."' style='color: #EEEEEE;'><b>Click here to go back</b></a>"
                        );
                    }else{
                        $this->template = "failure_module";
                        $this->vars=array(
                            'SUCCESSMSG' => $language['notifications']['bbcode_update_fail']." <br><br><a href='./acp.php?id=".$_GET['id']."&a=".$_GET['a']."' style='color: #EEEEEE;'><b>Click here to go back</b></a>"
                        );
                    }
                    break;
                }elseif(isset($_GET["delete"])){
                    if(_mysql_query("DELETE FROM bbcode WHERE bbcode_id=".$_GET["delete"])){
                        $this->template = "success_module";
                        $this->vars=array(
                            'SUCCESSMSG' => $language['notifications']['bbcode_delete']." <br><br><a href='./acp.php?id=".$_GET['id']."&a=".$_GET['a']."' style='color: #EEEEEE;'><b>Click here to go back</b></a>"
                        );
                    }else{
                        $this->template = "failure_module";
                        $this->vars=array(
                            'SUCCESSMSG' => $language['notifications']['bbcode_delete_fail']." <br><br><a href='./acp.php?id=".$_GET['id']."&a=".$_GET['a']."' style='color: #EEEEEE;'><b>Click here to go back</b></a>"
                        );
                    }
                    break;
                }
                $bbcodes_js = array_to_js(get_table_contents(bbcode,array('bbcode_id','bbcode_hint')),'bbcode',true);
                $this->vars=array(
                    'bbcode' => $bbcodes_js,
                    'hint' => $bbcode_hint,
                    'code' => $bbcode,
                    'HTML' => $bbcode_html,
                    'Attrib' => $bbcode_helper,
                    'show' => $bbcode_show
                );
            break;
        }
    }
}

?>