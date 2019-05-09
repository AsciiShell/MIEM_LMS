<?php
include_once "lib.php";
isAdmin() || isCommandLineInterface() || RedirectTo('/ruz/student.php', false);
$template = array("header" => "МИЭМ LMS | Просмотр групп",
    "body" => file_get_contents("html/displayGroup.html"));

include "html/base.php";