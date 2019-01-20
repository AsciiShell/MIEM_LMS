<?php
require(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../course/lib.php');
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

isAdmin() || die();

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
        if ($this->result)
            $this->result = json_decode($this->result);
    }

    function __destruct()
    {
        curl_close($this->curl);
    }
}

class DataBaseCourse
{
    private const createDBStm = "CREATE TABLE IF NOT EXISTS mdl_ruz_groups (
      id BIGINT(10) NOT NULL AUTO_INCREMENT , 
      course_id BIGINT(10) NOT NULL , 
      group_id BIGINT(10) NOT NULL , 
      PRIMARY KEY (id), 
      UNIQUE (group_id),
      FOREIGN KEY (course_id) REFERENCES mdl_course(id) ON DELETE CASCADE)
      ENGINE = InnoDB";

    private static $instance;

    private function __construct()
    {
        global $DB;
        $DB->execute(self::createDBStm);
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function createCourse($name, $id)
    {
        global $DB;
        $data = new stdClass();
        $data->category = 1;
        $data->fullname = $name;
        $data->shortname = $name;
        $data->idnumber = "";
        $data->format = "weeks"; // TODO may be topics
        $data->groupmode = 2;
        $data->groupmodeforce = 1;
        $data->visible = true;
        create_course($data);

        $group = new stdClass();
        $group->course_id = intval($DB->get_record('course', array('shortname' => $name), 'id', MUST_EXIST)->id);
        $group->group_id = $id;
        $DB->insert_record("ruz_groups", $group, false);
        return true;
    }

    public function someMethod2()
    {
        // whatever
    }
}