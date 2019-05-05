<?php
require_once("../lib.php");
$course_id = $_POST["course_id"];
$role = $_POST["role"];
$out = DataBaseCourse::getInstance()->AttachUsers($course_id, $role, $_POST['user_id']);
header('Content-Type: application/json; charset=utf-8');
echo json_encode($out, JSON_UNESCAPED_UNICODE);