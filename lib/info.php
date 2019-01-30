<?php
$user_id = 58;$key = "asdf";
die($_SERVER['REQUEST_SCHEME']."://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."?a=activate&uid=".$user_id."&key=".$key);