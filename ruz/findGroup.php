<?php
include_once "lib.php";
isAdmin() || isCommandLineInterface() || RedirectTo('/login/index.php', false);
$template = array("header" => "МИЭМ LMS | Поиск групп",
    "body" => file_get_contents("html/findGroup.html"));

include "html/base.php";