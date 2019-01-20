<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 19.01.2019
 * Time: 20:49
 */
require_once ("lib.php");

$o = new RequestsGet("https://ruz.hse.ru/api/search?term=БИВ&type=group");
var_dump($o);