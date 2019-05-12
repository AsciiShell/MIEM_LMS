<?php
require_once("../lib.php");
isUser() ||  RedirectTo('/login/index.php', false);
$out = DataBaseCourse::getInstance()->GetUserCourses();
header('Content-Type: application/json; charset=utf-8');
echo json_encode($out, JSON_UNESCAPED_UNICODE);