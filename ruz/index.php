<?php
include_once "lib.php";

$template = array("header" => "МИЭМ LMS | Просмотр групп",
    "body" => file_get_contents("html/displayGroup.html"));

include "html/base.php";