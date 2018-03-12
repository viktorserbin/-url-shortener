<?php

require_once 'inc' . DIRECTORY_SEPARATOR . 'functions.php';
require_once 'cfg' . DIRECTORY_SEPARATOR . 'app_config.php';
if ($_GET){
    if (isset($_GET['u'])){
        echo $_GET['u'];
        $link=getLink($_GET['u']);
        header("Location: $link");
    }
}else {
    header('Location: index.php');
}