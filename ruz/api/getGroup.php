<?php
require_once("../lib.php");
$id = $_GET["id"];
$out = DataBaseCourse::getInstance()->GetGroup($id);
header('Content-Type: application/json; charset=utf-8');
echo json_encode($out, JSON_UNESCAPED_UNICODE);