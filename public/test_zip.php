<?php
header('Content-Type: text/plain');
$exists = class_exists('ZipArchive');
var_dump($exists);
if (!$exists) {
    echo "\nLoaded extensions:\n";
    print_r(get_loaded_extensions());
}
?>
