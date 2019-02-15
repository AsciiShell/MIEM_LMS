<?php
require_once("../lib.php");
$name = $_GET["group"];
$out = new RequestsGet(sprintf("https://ruz.hse.ru/api/search?term=%s&type=group", $name));
header('Content-Type: application/json; charset=utf-8');
if ($out->result) {
    $exist = DataBaseCourse::getInstance()->GetGroup();
    foreach ($out->result as &$value){
        $value->exist = false;
        foreach ($exist as $value2){
            if($value->id == $value2->group_id ){
                $value->exist = true;
                break;
            }
        }
    }
    echo json_encode($out->result, JSON_UNESCAPED_UNICODE);
}
else
    echo json_encode(array(), JSON_UNESCAPED_UNICODE);