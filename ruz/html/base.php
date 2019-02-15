<?php global $template;
$url = explode("/", $_SERVER["SCRIPT_NAME"]);
if(count($url) == 3 && $url[0] == "" && $url[1] == "ruz" && $url[2] != "")
    $url = $url[2];
else
    $url = null;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $template["header"] ?></title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
</head>

<body>
<nav class="navbar navbar-expand-sm bg-dark navbar-dark">

    <a class="navbar-brand" href="#">МИЭМ LMS</a>

    <div class="collapse navbar-collapse" id="navbarsExampleDefault">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item active">
                <a class="nav-link" href="">Настройки</a>
            </li>
        </ul>
    </div>
</nav>

<div class="row" style="margin:0;">
    <nav class="col-xs-5 col-sm-4 col-md-3 col-lg-2">
        <ul class="nav nav-pills flex-column">

            <li class="nav-item"><a class="nav-link <?php echo ($url == "index.php") ? "active" : "" ?>" href="index.php">Обзор групп</a></li>
            <li class="nav-item"><a class="nav-link <?php echo ($url == "findGroup.php") ? "active" : "" ?>" href="findGroup.php">Поиск групп</a></li>
            <li class="nav-item"><a class="nav-link <?php echo ($url == "editGroup.php") ? "active" : "" ?>" href="editGroup.php">Редактирование групп</a></li>
        </ul>
    </nav>
    <main class="col-xs-7 col-sm-8 col-md-9 col-lg-10">
        <?php echo $template["body"] ?>
    </main>
</div>
</body>
</html>