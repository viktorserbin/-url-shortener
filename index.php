<?php

require_once 'inc' . DIRECTORY_SEPARATOR . 'functions.php';
require_once 'cfg' . DIRECTORY_SEPARATOR . 'app_config.php';
$message='';
$success_message='';
$files_counter='';
$url_table='';

if (isset($_SESSION['success_message'])){
    $success_message=sprintf("<div class=\"p-3 mb-2 bg-primary text-white\">Url <strong>%s</strong> успешно удален</div>", $_SESSION['success_message']);
    unset($_SESSION['success_message']);
}

if (!isset($_COOKIE['username_url_shortener'])) {
    createCookie();
}

$data_list=getData(DATA_DIR);
if (($data_list)){
    $cookie_name=$_COOKIE['username_url_shortener'];
    $url_table=showUrls($data_list,$cookie_name);
    $files_counter=sprintf("<p class=\"text-primary\">Всего сокращенных ссылок <strong>%s</strong>.</p>",globCounter());
}


if (!empty($_POST)) {
    if (isset($_POST['url'])){
        $url=$_POST['url'];
        $unique_url_id = getUniqueID ();
        writeUrl ($url,$unique_url_id.'.url');
        $url_root=getRootUrl($_SERVER['REQUEST_URI']);
        $message = "<p>Добавленно сокращение <a href='{$url_root}go.php?u={$unique_url_id}' target=\"_blank\">{$unique_url_id}</a> для <a href='{$url}' target=\"_blank\">{$url}</a></p>".PHP_EOL;
//        $message.= "<p>Ваш короткий URL <strong>{$url_root}go.php?u={$unique_url_id}</strong></p>".PHP_EOL;
        $message.= "<p>Ваш короткий URL <strong>{$url_root}go/{$unique_url_id}</strong></p>".PHP_EOL;
    }
}

echo <<<EOT
<!DOCTYPE html>
<html lang="ru">
<head>
  <title>Сокращатель ссылок</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="js/functions.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>

<div class="container">
<nav class="navbar navbar-default">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="pull-left" href="index.php"> <img src="images/logo.png" width="50px" /></a>
      <a class="navbar-brand" href="index.php">Url Shortener</a>
    </div>
    <ul class="nav navbar-nav">
      <li class="active"><a href="#">Home</a></li>
      <li><a href="#">Page 1</a></li>
      <li><a href="#">Page 2</a></li>
      <li><a href="#">Page 3</a></li>
    </ul>
  </div>
</nav>
  <h2>Введи url и получи короткий аналог!</h2>
$success_message
$files_counter  
$message
  <form action="" method="post">
    <div class="form-group">
      <label for="url">Url:</label>
      <input type="url" class="form-control" id="url" placeholder="Enter url: http://example.com" name="url">
    </div>
    <button type="submit" class="btn btn-default">Submit</button>
  </form>
  <br /><br />

    $url_table

</div>
</body>
</html>
EOT;
