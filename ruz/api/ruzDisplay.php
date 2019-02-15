<?php
require_once("../lib.php");
$group = intval($_GET["groupID"]);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(DataBaseCourse::getInstance()->GetScheduler($group), JSON_UNESCAPED_UNICODE);
