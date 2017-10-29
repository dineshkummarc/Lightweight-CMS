<?php
if (!function_exists("ConnectDataBase")){include_once("./groups.php");include_once("./users.php");include_once("./funcs.php");include_once("./tobase.php");include_once("../settings.php");}
if (!$GLOBALS_DEFINED == true){include_once("../globals.php");}
if (!function_exists("CreateUser")){include_once("./login.php");}
//~ if($Action == "") {$Action = "user";}
$LoginURL = "./auth.php?a=login";
//~ die("STOP".$DATABASE_NAME);
ConnectDataBase();

if ($_GET['login']){
    $AuthReply='Log in with your username and password or <a href"registerurl">create</a> an account.<br><br>'.GetLoginForm()."<br>";
}else{

    if (strstr($_SERVER['HTTP_REFERER'],"auth") || $_SERVER['HTTP_REFERER'] == "")
    {
        $_SERVER['HTTP_REFERER'] = $ROOTDIR.'/index.html';
    }

    if (Login($_POST['UserName'], $_POST['UserPassword']) > 1){
        //success
        $AuthReply='You have successfully logged in.<br><br>Click <a href="'.$_SERVER['HTTP_REFERER'].'">here</a> if your browser does not automatically redirect you.'."<br>";
        dbg("rd2");
        die("sss");
        //header('Refresh: 3; url='.$_SERVER['HTTP_REFERER']);
    }else{
        //fail.
        $AuthReply='Login failed.<br><br>You can try again or <a href"registerurl">create</a> an account.<br><br>'.GetLoginForm()."<br>";
    }
}
echo TemplateReplace(file_get_contents($form_path['FORMS'].'/auth.html'), array());


?>