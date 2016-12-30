#!/usr/bin/php
<?php
    include("../config.php");
    include("../www/functions.php");
    
    if($argc!=4) {
        echo $argv[0]." /full/path/filename remark license\n";
        exit(0);
    }
    
    $filename=basename($argv[1]);
    $id=raw_add($argv[1],$filename);
    $data['validated']=1;
    $data['license']=$argv[3];
    $data['remark']=$argv[2];
    
    raw_modify($id,$data);
    