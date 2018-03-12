<?php session_start();

function randomString($length = 6) {
    $str = "";
    $characters = array_merge(range('A','Z'), range('a','z'));
    $max = count($characters) - 1;
    for ($i = 0; $i < $length; $i++) {
        $rand = mt_rand(0, $max);
        $str .= $characters[$rand];
    }
    return $str;
}
function getUniqueID ($dir = DATA_DIR) {
    $random_id=randomString();
    while (file_exists($dir.DIRECTORY_SEPARATOR.$random_id.'.url')){
        $random_id=randomString();
    }
    return $random_id;
}
function writeUrl ($url,$unique_url_id){
    $file = fopen(DATA_DIR.DIRECTORY_SEPARATOR.$unique_url_id, "w") or die("Unable to open file!");
    fwrite($file, $url);
    fclose($file);
    file_put_contents(COOKIE_DIR.DIRECTORY_SEPARATOR.$_COOKIE['username_url_shortener'],
        str_replace('.url','',$unique_url_id.PHP_EOL),
    FILE_APPEND | LOCK_EX) or die ("Не могу записать в {$_COOKIE['username_url_shortener']}");
}

function getData($dir){
    $file_list=false;
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                // if ($file === '.' || $file === '..') {
                if ($file === '.' or $file === '..') {
                    continue; // Skip directory links
                }
                $fullFilePath = $dir . DIRECTORY_SEPARATOR . $file;
                if (filetype($fullFilePath)=='dir'){
//                    echo "dir {$fullFilePath}";
                    getData($fullFilePath);
                }
                $pattern='/(.url)$/i';
                $check_ext=preg_match($pattern,$fullFilePath);
                if ((filetype($fullFilePath)!='dir') and ($check_ext)) {
                    $file_data=file_get_contents($fullFilePath) or die ("Не могу открыть {$fullFilePath}");
                    $file_list [basename($file,'.url')] = $file_data;
//                    $file_list [] = ['file' => basename($file,'.url'), 'data' => $file_data];
                }
            }
            closedir($dh);
        }
    } else {
        die('Error: directory ' . $dir . ' does not exists!');
    }
    return $file_list;
}

function showUrls ($data_list,$cookie_name = false){
    $url_table = '<div class="row">'.PHP_EOL;
    $i=1;
    if ($cookie_name) {
        $cookie_link_list = getCookieLinkList($cookie_name);
        //var_dump($cookie_link_list);
        $url_root=getRootUrl($_SERVER['REQUEST_URI']);
        foreach ($data_list as $key => $value){
            if (in_array($key,$cookie_link_list,false)) {
                $url_table .= "<div class=\"col-sm-2\">
                <a href='{$url_root}delete.php?url_id={$key}' onclick=\"return confirmDelete();\">
                <img src=\"images/delete.png\" width=\"15\" /></a>
                <a href='{$url_root}go/{$key}' target='_blank'> {$key} </a>
                </div>" . PHP_EOL;
                if ($i == 6) {
                    $i = 0;
                    $url_table .= '</div>' . PHP_EOL . '<div class="row">';
                }
                $i++;
            }
        }
    }
    $url_table.='</div>'.PHP_EOL;
    return $url_table;
}
function createCookie(){
    $user_id = getUniqueID(COOKIE_DIR);
    $file = fopen(COOKIE_DIR . DIRECTORY_SEPARATOR . $user_id, "w");
    fclose($file);
    setcookie("username_url_shortener", $user_id, time() + 8640000);
}
function getCookieLinkList($cookie_name){
    $file_name=COOKIE_DIR.DIRECTORY_SEPARATOR.$cookie_name;
    $result[]='';
    if (file_exists($file_name)) {
        if (0 != filesize($file_name)) {
            $file = file($file_name)
            or die ("Не могу открыть файл {$file_name}");
            foreach ($file as $string) {
                $result[] = trim($string);
            }
        }
    }
    return $result;
}
function globCounter ($dir = DATA_DIR){
    $count=0;
    $file_list = glob($dir.DIRECTORY_SEPARATOR."*.url");
    if (isset($file_list)) {
        $count = count ($file_list);
    }
    return $count;
}
function getRootUrl($uri){
    $parts = explode('/',$uri);
    $dir = $_SERVER['SERVER_NAME'];
    for ($i = 0; $i < count($parts) - 1; $i++) {
        $dir .= $parts[$i] . "/";
    }
    $proto='http://';
    if( isset($_SERVER['https'] ) ) {
        $proto='HTTPS://';
    }
    return $proto.$dir;
}
function getLink($id){
    $file_name=DATA_DIR.DIRECTORY_SEPARATOR.$id.'.url';
    $file = file_get_contents($file_name) or die ("Не могу открыть {$file_name}");
    return $file;
}
function deleteUrl($id){
    $cookie_name=$_COOKIE['username_url_shortener'];
    $file_name = DATA_DIR . DIRECTORY_SEPARATOR . $id . '.url';
    unlink($file_name) or die ("Не могу удалить {$file_name}");
    $file_name=COOKIE_DIR.DIRECTORY_SEPARATOR.$cookie_name;
    $contents = file_get_contents($file_name) or die ("Не могу открыть {$file_name}");
    $contents = str_replace($id.PHP_EOL, '', $contents);
    if ($contents==''){
        unlink($file_name) or die ("Не могу удалить {$file_name}");
    } else {
        file_put_contents($file_name, $contents) or
        die ("Не могу записаaть в {$file_name}");
    }
}