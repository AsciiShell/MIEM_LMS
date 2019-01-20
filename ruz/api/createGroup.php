<?php
require_once("../lib.php");
$id = intval($_GET["groupID"]);
$name = $_GET["name"];
echo DataBaseCourse::getInstance()->createCourse($name, $id);
