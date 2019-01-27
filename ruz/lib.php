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
    private const createDBStm = "CREATE TABLE IF NOT EXISTS mdl_ruz_groups
(
  id        BIGINT(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  course_id BIGINT(10) NOT NULL REFERENCES mdl_course (id) ON DELETE CASCADE,
  group_id  BIGINT(10) NOT NULL UNIQUE
)
  ENGINE = InnoDB";

    private const createScheduleStm = "CREATE TABLE IF NOT EXISTS mdl_ruz_scheduler
(
  id          BIGINT(10)   NOT NULL PRIMARY KEY AUTO_INCREMENT,
  group_id    BIGINT(10)   NOT NULL REFERENCES mdl_ruz_groups (id) ON DELETE CASCADE,
  discipline  VARCHAR(100) NOT NULL,
  date_       DATE         NOT NULL,
  beginLesson TIME         NOT NULL,
  endLesson   TIME         NOT NULL,
  building    VARCHAR(100) NOT NULL,
  auditorium  VARCHAR(10)  NOT NULL,
  kindOfWork  VARCHAR(50),
  lecturer    VARCHAR(100) NOT NULL,
  stream      VARCHAR(200) NOT NULL
) ENGINE = InnoDB
";

    private const DeleteSchedulerStm = "DELETE
FROM mdl_ruz_scheduler
WHERE group_id = ?";

    private const ruzDate = "Y.m.d";
    private const ruzDuration = 30 * 24 * 60 * 60;

    private static $instance;

    private function __construct()
    {
        global $DB;
        $DB->execute(self::createDBStm);
        $DB->execute(self::createScheduleStm);
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

    public function rusFetcher()
    {
        global $DB;
        foreach ($DB->get_records('ruz_groups') as $value) {
            $out = new RequestsGet(sprintf("https://ruz.hse.ru/api/schedule/group/%s?start=%s&finish=%s&lng=1",
                $value->group_id,
                date(self::ruzDate),
                date(self::ruzDate, time() + self::ruzDuration)));
            $DB->execute(self::createDBStm, array($value->group_id));
            foreach ($out->result as $row) {
                $data = new stdClass();
                $data->group_id = $value->group_id;
                $data->discipline = $row->discipline;
                $data->date_ = str_replace('.', '-', $row->date);
                $data->beginLesson = $row->beginLesson;
                $data->endLesson = $row->endLesson;
                $data->building = $row->building;
                $data->auditorium = $row->auditorium;
                $data->kindOfWork = $row->kindOfWork;
                $data->lecturer = $row->lecturer;
                $data->stream = $row->stream;
                $DB->insert_record("ruz_scheduler", $data, false);
            }
        }
    }
}