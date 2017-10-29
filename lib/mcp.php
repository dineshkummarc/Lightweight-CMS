<?php
    $CURRENT_MODULE = 'mcp';
    include_once "./control_panel.php";
    render_forum_path();
    //~ load($MAIN_PAGE[$CURRENT_MODULE], $PAGE_TITLE[$CURRENT_MODULE], $FILE_PATH[$CURRENT_MODULE]);
    load($MAIN_PAGE[$CURRENT_MODULE], $PAGE_TITLE[$CURRENT_MODULE], $FILE_PATH[$CURRENT_MODULE]);
?>