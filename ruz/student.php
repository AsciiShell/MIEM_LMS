<?php
include_once "lib.php";
isUser() ||  RedirectTo('/login/index.php', false);

include "html/student_courses.html";