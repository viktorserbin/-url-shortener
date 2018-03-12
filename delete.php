<?php

require_once 'inc' . DIRECTORY_SEPARATOR . 'functions.php';
require_once 'cfg' . DIRECTORY_SEPARATOR . 'app_config.php';

$cookie_name=$_COOKIE['username_url_shortener'];
$cookie_link_list = getCookieLinkList($cookie_name);

if (isset($_GET['url_id'])){
    $id=$_GET['url_id'];
    if (in_array($id,$cookie_link_list,false)) {
        echo $id;
        deleteUrl($id);
        $msg = "{$id}";
        $_SESSION["success_message"] = "$msg";
        header("Location: index.php");
    }
}