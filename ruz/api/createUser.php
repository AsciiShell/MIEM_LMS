<?php
require_once("../lib.php");
$user_name = $_GET["user_name"];
$f_name = $_GET["f_name"];
$l_name = $_GET["l_name"];
$email = $_GET["email"];
$password = $_GET["password"];
if ($user_name && $f_name && $l_name && $email && $password)
    try {
        $out = DataBaseCourse::getInstance()->CreateUser($user_name, $f_name, $l_name, $email, $password);
    } catch (Exception $ex) {
        $out = $ex;
    }
else
    $out = false;
header('Content-Type: application/json; charset=utf-8');
echo json_encode($out, JSON_UNESCAPED_UNICODE);