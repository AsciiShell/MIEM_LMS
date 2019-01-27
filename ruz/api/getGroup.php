<?php
require_once("../lib.php");
$id = $_GET["id"];
$out = new RequestsGet(sprintf("https://ruz.hse.ru/api/search?term=%s&type=group", $name));
header('Content-Type: application/json; charset=utf-8');
if ($out->result)
    echo json_encode($out->result, JSON_UNESCAPED_UNICODE);