<?php
require_once("../lib.php");
header('Content-Type: application/json; charset=utf-8');
echo json_encode(DataBaseCourse::getInstance()->rusFetcher(), JSON_UNESCAPED_UNICODE);