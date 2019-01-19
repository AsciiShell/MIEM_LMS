<?php
require(__DIR__ . '/../config.php');
function isAdmin()
{
    global $USER;
    $admins = get_admins();
    foreach ($admins as $admin) {
        if ($USER->id == $admin->id) {
            return true;
        }
    }
    return false;
}

class RequestsGet
{
    public $curl;
    public $result;

    public function __construct($url)
    {
        $this->curl = curl_init($url);
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_HEADER, 0);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        $this->result = curl_exec($this->curl);
        if($this->result)
            $this->result = json_decode($this->result);
    }

    function __destruct()
    {
        curl_close($this->curl);
    }
}