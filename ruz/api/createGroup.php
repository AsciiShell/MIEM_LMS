<?php
require_once("../lib.php");
isAdmin() ||  RedirectTo('/login/index.php', false);
$id = intval($_GET["groupID"]);
$name = $_GET["name"];
header('Content-Type: application/json; charset=utf-8');
echo json_encode(DataBaseCourse::getInstance()->createCourse($name, $id), JSON_UNESCAPED_UNICODE);
