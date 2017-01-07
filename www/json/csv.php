<?php
    header("Content-type: text/csv");
    include_once "../../config.php";
    include_once "../functions.php";

    $raws=raw_getalldata();
    $i=0;
    foreach($raws as $raw){
        if($raw['validated']==1) {
            echo $raw['id'].',"'.$raw['make'].'","'.$raw['model'].'","'.$raw['mode'].'","'.$raw['remark'].'","'.$raw['license'].'","'.$raw['filename'].'","'.$raw['checksum'].'","https://raw.pixls.us/getfile.php/'.$raw['id'].'/raw/'.$raw['filename'].'"\n';
        }
    }
