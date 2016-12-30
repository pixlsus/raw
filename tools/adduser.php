#!/usr/bin/php
<?php 
    include("../config.php");
    include("../www/functions.php");

    if($argc!=3){
        echo $argv[0]." username password\n";
        exit(0);
    }
    user_add($argv[1], $argv[2]);
