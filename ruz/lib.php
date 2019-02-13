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

function RedirectTo($url, $permanent = false)
{
    header('Location: ' . $url, true, $permanent ? 301 : 302);
    exit();
}

isAdmin() || RedirectTo('/login/index.php', false);

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
  course_id BIGINT(10) NOT NULL PRIMARY KEY,
  group_id  BIGINT(10) NOT NULL UNIQUE,
  CONSTRAINT fk_mdl_course FOREIGN KEY (course_id) REFERENCES mdl_course (id) ON DELETE CASCADE
)
  ENGINE = InnoDB";

    private const createScheduleStm = "CREATE TABLE IF NOT EXISTS mdl_ruz_scheduler
(
  id          BIGINT(10)   NOT NULL PRIMARY KEY AUTO_INCREMENT,
  group_id    BIGINT(10)   NOT NULL,
  discipline  TEXT NOT NULL,
  date_       DATE         NOT NULL,
  beginLesson TIME         NOT NULL,
  endLesson   TIME         NOT NULL,
  building    VARCHAR(100) NOT NULL,
  auditorium  VARCHAR(10)  NOT NULL,
  kindOfWork  VARCHAR(50),
  lecturer    VARCHAR(100) NOT NULL,
  stream      TEXT NOT NULL,
  CONSTRAINT fk_sch_group FOREIGN KEY (group_id) REFERENCES mdl_ruz_groups (group_id) ON DELETE CASCADE
) ENGINE = InnoDB";

    private const DeleteSchedulerStm = "DELETE
FROM mdl_ruz_scheduler
WHERE group_id = ?";

    private const InsertCourse = "INSERT INTO mdl_ruz_groups
VALUES (?, ?)";

    private const SelectCourses = "SELECT *
FROM mdl_ruz_groups
       INNER JOIN mdl_course ON mdl_ruz_groups.course_id = mdl_course.id";

    private const SelectCourse = self::SelectCourses . "\nWHERE course_id = ?";

    private const InsertUser = "INSERT INTO mdl_user (auth, confirmed, mnethostid, lang, username, firstname, lastname, email, password)
VALUES ('manual', 1, 1, 'ru', ?, ?, ?, ?, ?)";

    private const ruzDate = "Y.m.d";
    private const ruzDuration = 3 * 30 * 24 * 60 * 60;

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
        try {
            create_course($data);
        } catch (Exception $ex) {
            return false;
        }
        try {
            $course_id = intval($DB->get_record('course', array('shortname' => $name), 'id', MUST_EXIST)->id);
            $group = array($course_id,
                $id);
            $DB->execute(self::InsertCourse, $group);
        } catch (Exception $ex) {
            delete_course($course_id);
            return $ex->getMessage();
        }
        return true;
    }

    public function rusFetcher()
    {
        global $DB;
        $log = array();
        foreach ($DB->get_records('ruz_groups') as $value) {
            try {
                $out = new RequestsGet(sprintf("https://ruz.hse.ru/api/schedule/group/%s?start=%s&finish=%s&lng=1",
                    $value->group_id,
                    date(self::ruzDate),
                    date(self::ruzDate, time() + self::ruzDuration)));
                $DB->execute(self::DeleteSchedulerStm, array($value->group_id));
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
                array_push($log, $value);
            } catch (Exception $ex) {
                array_push($log, $ex);
            }
        }
        return $log;
    }

    public function GetScheduler($group)
    {
        global $DB;
        return array_values($DB->get_records("ruz_scheduler", array('group_id' => $group)));
    }

    public function GetGroup($group = null)
    {
        global $DB;
        if ($group == null) {
            return array_values($DB->get_records_sql(self::SelectCourses));
        } else {
            return $DB->get_record_sql(self::SelectCourse, array($group));
        }
    }

    public function CreateUser($user_name, $f_name, $l_name, $email, $password)
    {
        global $DB;
        $DB->execute(self::InsertUser, array($user_name, $f_name, $l_name, $email, password_hash($password, PASSWORD_DEFAULT)));
        return true;
    }

    public function AttachUser($id, $role)
    {

    }
}