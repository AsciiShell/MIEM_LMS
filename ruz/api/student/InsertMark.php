<?php
require_once("../../lib.php");
isAdmin() || RedirectTo('/login/index.php', false);
$lesson_id = $_GET["lesson_id"];
$user_id = $_GET["user_id"];
$status = $_GET["status"];
DataBaseCourse::getInstance()->InsertVisitForCourse($lesson_id, $user_id, $status);

header('Content-Type: application/json; charset=utf-8');
echo json_encode(true, JSON_UNESCAPED_UNICODE);