<?php
require_once("../lib.php");
isUser() ||  RedirectTo('/login/index.php', false);
$group = intval($_GET["groupID"]);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(DataBaseCourse::getInstance()->GetScheduler($group), JSON_UNESCAPED_UNICODE);
