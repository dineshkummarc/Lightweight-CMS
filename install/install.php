<?php
include_once "../lib/database.php";

function encrypt($pass)
{
    $sum = 1;
    for ($i = 0; $i < strlen($pass); $i++) {
        $sum += ord($pass[$i]);
    }
    for ($i = 0; $i < $sum; $i++) {
        $pass = md5($pass);
    }
    return $pass;
}

function random_string($len)
{
    $chr = array('q', 'w', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p', 'a', 's', 'd', 'f', 'g', 'h', 'j',
        'a', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'z', 'x', 'c', 'v', 'b', 'n', 'm', 'Q', 'W', 'E', 'R',
        'T', 'Y', 'U', 'I', 'O', 'P', 'A', 'S', 'D', 'F', 'G', 'H', 'J', 'A', 'S', 'D', 'F', 'G', 'H', 'J',
        'K', 'L', 'Z', 'X', 'C', 'V', 'B', 'N', 'M', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '.',
        ',', '-', '/', '*', '+', ')', '(', '!', '$', '%', '[', ']', '{', '}', '@');
    $str = "";
    for ($i = 0; $i < $len; $i++) {
        $str .= $chr[rand(0, count($chr) - 1)];
    }
    return $str;
}

function setup_database_connection(){
    global $MODE, $DATABASE_SERVER, $DATABASE_PORT, $DATABASE_USER, $DATABASE_NAME, $DATABASE_PASSWORD;
    $MODE = $_POST['db_mode'];
    $DATABASE_SERVER = $_POST['db_server'];
    $DATABASE_PORT = $_POST['db_port'];
    $DATABASE_USER = $_POST['db_user'];
    $DATABASE_NAME = $_POST['db_name'];
    $DATABASE_PASSWORD = $_POST['db_password'];

    if (ConnectdataBase() == false) {
        return false;
    }
    return true;
}

function install_site(){
    if (setup_database_connection()) {
        install_database();
        write_settings_file();
    }else {
        return "database configuration is invalid!";
    }

    if (in_array($_POST['admin_user'], array("", "guest", "system"))) {
        return "This username is not allowed to use";
    }
    if ($_POST['admin_password'] == "") {
        return "You must choose a password for your administrator account!";
    }

    return "success";
}

function install_database(){
    $structure_file = prepare_structure_file();
    write_file_to_database($structure_file);

    $data_file = prepare_data_file();
    write_file_to_database($data_file);
}

function prepare_data_file(){
    $file = file_get_contents("data.sql");
    $salt = random_string(10);
    $replacements = array(
        '{FOOTER}' => $_POST['site_footer'],
        '{site_name}' => $_POST['site_name'],
        '{site_description}' => $_POST['site_description'],
        '{SITemail}' => $_POST['site_email'],
        '{ADMINNAME}' => $_POST['admin_user'],
        '{ADMINPASS}' => encrypt($_POST['admin_password'] . $salt),
        '{ADMINSALT}' => $salt,
        '{ADMINMAIL}' => $_POST['admin_email'],
        '{time}' => time()
    );
    return strtr($file, $replacements);
}

function prepare_structure_file(){
    return file_get_contents("./structure.sql");
}

function write_file_to_database($file){
    $lines = explode("\n", $file);
    // Loop through each line
    //printf("lines %d\n",$lines);
    $templine = "";
    foreach ($lines as $line) {
        // Skip it if it's a comment
        if (substr($line, 0, 2) == '--' || $line == '')
            continue;

        // Add this line to the current segment
        $templine .= $line;
        // If it has a semicolon at the end, it's the end of the query
        if (substr(trim($line), -1, 1) == ';') {
            // Perform the query
            _mysql_query($templine) or die('Error performing query: ' . $templine);
            // Reset temp variable to empty
            $templine = '';
        }
    }
}

function write_settings_file(){
    global $MODE, $DATABASE_SERVER, $DATABASE_PORT, $DATABASE_USER, $DATABASE_NAME, $DATABASE_PASSWORD;
    $settings = '<?php' . "\n" .
        '$MODE = "' . $MODE . '";' . "\n" .
        '$DATABASE_SERVER = "' . $DATABASE_SERVER . '";' . "\n" .
        '$DATABASE_PORT = "' . $DATABASE_PORT . '";' . "\n" .
        '$DATABASE_USER = "' . $DATABASE_USER . '";' . "\n" .
        '$DATABASE_NAME = "' . $DATABASE_NAME . '";' . "\n" .
        '$DATABASE_PASSWORD = "' . $DATABASE_PASSWORD . '";' . "\n" .
        '?>';

    file_put_contents('../settings.php', $settings);
}

function prepare_installer(){
    $file = file_get_contents("install.html");

    $yes = '<span style="color: green;">Yes</span>';
    $no = '<span style="color: red;">No</span>';
    $install_fail = "false";
    $settings_writable = "false";

    $php_check = $yes;
    $version_str = phpversion();
    if ($version_str[0] < "5") {
        $php_check = $no;
        $install_fail = "true";
    }

    $quotes_check = $yes;
    if (get_magic_quotes_gpc()) {
        $quotes_check = $no;
        $install_fail = "true";
    }

    $uploads_check = $no;
    if (is_writable("../uploads")) {
        $uploads_check = $yes;
    }
    $large_check = $no;
    if (is_writable("../images/large")) {
        $large_check = $yes;
    }

    $small_check = $no;
    if (is_writable("../images/small")) {
        $small_check = $yes;
    }

    $settings_check = $no;
    if (is_writable("../settings.php")) {
        $settings_check = $yes;
        $settings_writable = "true";
    }

    $replacements = array(
        '{PHPVER}' => $php_check,
        '{QUOTES}' => $quotes_check,
        '{UPLOADS}' => $uploads_check,
        '{LARGE}' => $large_check,
        '{SMALL}' => $small_check,
        '{settings}' => $settings_check,
        '{settingsWRITABLE}' => $settings_writable,
        '{FAILED}' => $install_fail
    );

    $file = strtr($file, $replacements);

    return $file;
}

function test_database_connection(){
    if (setup_database_connection()) {
        return "database configuration is invalid!";
    }

    return "database configuration is correct!";
}

function routeRequest(){
    $response = "";
    if (isset($_POST['admin_user'])) {
        $response = install_site();
    }else if(isset($_POST['test_connection'])) {
        $response = test_database_connection();
    }
    else {
        $response = prepare_installer();
    }
    print($response);
}

routeRequest();

?>