<?php
define('CLI_SCRIPT', true);

require_once("../lib.php");

header('Content-Type: application/json; charset=utf-8');
DataBaseCourse::getInstance()->Migrate();
echo json_encode(true, JSON_UNESCAPED_UNICODE);