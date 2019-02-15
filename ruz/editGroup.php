<?php
include_once "lib.php";

$template = array("header" => "МИЭМ LMS | Редактор групп",
    "body" => file_get_contents("html/editGroup.html"));

include "html/base.php";