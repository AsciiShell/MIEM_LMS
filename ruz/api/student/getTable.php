<?php
require_once("../../lib.php");
isUser() || RedirectTo('/login/index.php', false);
$id = $_GET["id"];
$students = DataBaseCourse::getInstance()->GetStudentsForCourse($id);
$lessons = DataBaseCourse::getInstance()->GetLessonsForCourse($id);
$visits = DataBaseCourse::getInstance()->GetVisitsForCourse($id);
header('Content-Type: application/json; charset=utf-8');
$out = array("students" => $students, "lessons" => $lessons, "visits" => $visits);
echo json_encode($out, JSON_UNESCAPED_UNICODE);