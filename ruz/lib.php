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

function isUser()
{
    global $USER;
    return $USER->id !== 0;
}

function RedirectTo($url, $permanent = false)
{
    header('Location: ' . $url, true, $permanent ? 301 : 302);
    exit();
}

function isCommandLineInterface()
{
    return (php_sapi_name() === 'cli');
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
  stream      TEXT,
  CONSTRAINT fk_sch_group FOREIGN KEY (group_id) REFERENCES mdl_ruz_groups (group_id) ON DELETE CASCADE
) ENGINE = InnoDB";

    private const createLessonStm = "CREATE TABLE IF NOT EXISTS mdl_ruz_lesson
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
  stream      TEXT,
  CONSTRAINT fk_lesson_group FOREIGN KEY (group_id) REFERENCES mdl_ruz_groups (group_id) ON DELETE CASCADE
) ENGINE = InnoDB";

    private const createVisitStm = "CREATE TABLE IF NOT EXISTS mdl_ruz_visit
(
    id        BIGINT(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    lesson_id BIGINT(10) NOT NULL,
    user_id   BIGINT(10) NOT NULL,
    status    boolean    NOT NULL default false,
    CONSTRAINT fk_visit_lesson FOREIGN KEY (lesson_id) REFERENCES mdl_ruz_lesson (id) ON DELETE CASCADE,
    CONSTRAINT fk_visit_user FOREIGN KEY (user_id) REFERENCES mdl_user (id) ON DELETE CASCADE
) ENGINE = InnoDB";

    private const createVisitMoveProcedure = "create  procedure AddLesson(id BIGINT(10))
BEGIN
    declare _group_id BIGINT(10);
    declare _discipline TEXT;
    declare _date_ DATE;
    declare _beginLesson TIME;
    declare _endLesson TIME;
    declare _building VARCHAR(100);
    declare _auditorium VARCHAR(10);
    declare _kindOfWork VARCHAR(50);
    declare _lecturer VARCHAR(100);
    declare _stream TEXT;
    SELECT group_id,
           discipline,
           date_,
           beginLesson,
           endLesson,
           building,
           auditorium,
           kindOfWork,
           lecturer,
           stream
    FROM mdl_ruz_scheduler
    WHERE mdl_ruz_scheduler.id = id INTO _group_id, _discipline, _date_, _beginLesson, _endLesson, _building, _auditorium, _kindOfWork, _lecturer, _stream;
    INSERT INTO mdl_ruz_lesson VALUES (null, _group_id, _discipline, _date_, _beginLesson, _endLesson, _building, _auditorium, _kindOfWork, _lecturer, _stream);
    
end;";

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

    private const SelectScheduler = "SELECT *
FROM mdl_ruz_scheduler
WHERE (date_ > current_date OR (date_ = current_date AND endLesson > current_time))
  AND group_id = ?
ORDER BY date_, beginLesson";

    private const ruzDate = "Y.m.d";
    private const ruzDuration = 1 * 7 * 24 * 60 * 60;

    private static $instance;

    private function __construct()
    {

    }

    public function Migrate()
    {
        global $DB;
        $DB->execute(self::createDBStm);
        $DB->execute(self::createScheduleStm);
        $DB->execute(self::createLessonStm);
        $DB->execute(self::createVisitStm);
        $DB->execute(self::createVisitMoveProcedure);
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
        return array_values($DB->get_records_sql(self::SelectScheduler, array($group)));
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

    const SelectUsersAll = "SELECT id, username, firstname, lastname
FROM mdl_user
WHERE confirmed = 1
  AND deleted = 0
  AND suspended = 0";
    const SelectNewStudents = "SELECT id, username, firstname, lastname
FROM mdl_user
WHERE id NOT IN (SELECT DISTINCT userid FROM mdl_user_enrolments)
  AND confirmed = 1
  AND deleted = 0
  AND suspended = 0";

    const SelectUser = "SELECT id, username, firstname, lastname
FROM mdl_user
WHERE id = ?
  AND confirmed = 1
  AND deleted = 0
  AND suspended = 0";

    public function GetUsers($id, $enroll)
    {
        global $DB;
        if ($id == null) {
            if ($enroll) {
                return array_values($DB->get_records_sql(self::SelectNewStudents));
            } else {
                return array_values($DB->get_records_sql(self::SelectUsersAll));
            }
        } else {
            return $DB->get_record_sql(self::SelectUser, array($id));
        }
    }

    private const EnrollUsers = "INSERT INTO mdl_user_enrolments
VALUES (null,
        0,
        (SELECT id
         FROM mdl_enrol
         WHERE courseid =
               (SELECT course_id FROM mdl_ruz_groups WHERE group_id = ?)
           AND enrol = 'manual'),
        ?,
        UNIX_TIMESTAMP(),
        0,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP())";

    private const EnrollUserRole = "INSERT INTO mdl_role_assignments
VALUES (null, ?, (SELECT id FROM mdl_context WHERE contextlevel=50 AND instanceid=(SELECT course_id FROM mdl_ruz_groups WHERE group_id = ?)), ?,UNIX_TIMESTAMP(), 0, '',0, 0)";

    public function AttachUsers($course_id, $role, $users)
    {
        global $DB;
        if ($role === "teacher")
            $role = 3;
        else
            $role = 5; // Student
        foreach ($users as $user) {
            $DB->execute(self::EnrollUsers, array($course_id, $user));
            $DB->execute(self::EnrollUserRole, array($role, $course_id, $user));
        }


        return true;

    }

    private const SelectUserCourses = self ::SelectCourses . "\nWHERE course_id IN (
    SELECT courseid
    FROM mdl_enrol
    WHERE id IN (SELECT enrolid FROM mdl_user_enrolments WHERE userid = ?))";

    public function GetUserCourses()
    {
        global $DB, $USER;
        return array_values($DB->get_records_sql(self::SelectUserCourses, array($USER->id)));
    }

    private const SelectStudentsForCourse = "SELECT userid, firstname, lastname
FROM mdl_role_assignments
INNER  JOIN mdl_user ON userid = mdl_user.id
WHERE roleid = 5
  AND contextid IN (
    SELECT id
    FROM mdl_context
    WHERE contextlevel = 50
      AND instanceid = (
        SELECT course_id
        FROM mdl_ruz_groups
        WHERE group_id = ?
    )
)";

    public function GetStudentsForCourse($id)
    {
        global $DB;
        return array_values($DB->get_records_sql(self::SelectStudentsForCourse, array($id)));
    }

    private const SelectLessonsForCourse = "SELECT id, discipline, date_ FROM mdl_ruz_lesson WHERE group_id = ?";

    public function GetLessonsForCourse($id)
    {
        global $DB;
        return array_values($DB->get_records_sql(self::SelectLessonsForCourse, array($id)));
    }

    private const SelectVisitsForCourse = "SELECT *
FROM mdl_ruz_visit
WHERE user_id IN (SELECT userid
                  FROM mdl_role_assignments
                  WHERE roleid = 5
                    AND contextid IN (
                      SELECT id
                      FROM mdl_context
                      WHERE contextlevel = 50
                        AND instanceid = (
                          SELECT course_id
                          FROM mdl_ruz_groups
                          WHERE group_id = ?
                      )
                  ))
  AND lesson_id IN (SELECT id FROM mdl_ruz_lesson WHERE group_id = ?)";

    public function GetVisitsForCourse($id)
    {
        global $DB;
        return array_values($DB->get_records_sql(self::SelectVisitsForCourse, array($id, $id)));
    }

    private const InsertVisitCourse = "INSERT INTO mdl_ruz_visit (id, lesson_id, user_id, status)
VALUES (null, ?, ?, ?)
ON DUPLICATE KEY UPDATE status = ?";

    public function InsertVisitForCourse($lesson_id, $user_id, $status)
    {
        global $DB;
        return $DB->execute(self::InsertVisitCourse, array($lesson_id, $user_id, $status, $status));
    }
}