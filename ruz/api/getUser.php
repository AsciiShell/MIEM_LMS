<?php
require_once("../lib.php");
isAdmin() ||  RedirectTo('/login/index.php', false);
$id = $_GET["id"];
$enroll = $_GET["enroll"] !== null; // Delete users, who have already enrolled
$out = DataBaseCourse::getInstance()->GetUsers($id, $enroll);
header('Content-Type: application/json; charset=utf-8');
echo json_encode($out, JSON_UNESCAPED_UNICODE);