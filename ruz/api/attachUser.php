<?php
require_once("../lib.php");
$course_id = $_POST["course_id"];
$isStudent = $_POST["options"] == "on";
$out = DataBaseCourse::getInstance()->AttachUsers($course_id, $isStudent, $_POST['user_id']);
header('Content-Type: application/json; charset=utf-8');
echo json_encode($out, JSON_UNESCAPED_UNICODE);