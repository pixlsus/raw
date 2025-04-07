#!/usr/bin/php
<?php
    include("../config.php");
    include("../www/functions.php");

    // found on http://php.net/manual/en/function.rmdir.php
    function delTree($dir) {
       $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
      }

    // We don't really want to race with ourselves, so acquire a lock.
    define('publicdatauniquepath_lock', publicdatauniquepath.'.lock');
    $lock = fopen(publicdatauniquepath_lock, "w");
    if(!flock($lock, LOCK_EX | LOCK_NB)) {
        echo 'Unable to obtain lock';
        exit(-1);
    }

    define('timestamp', time());
    define('publicdatauniquepath_timestamped', publicdatauniquepath.".".timestamp);

    $cameradata=parsecamerasxml();
    $data=raw_getalldata();
    $makes=array();

    assert(!file_exists(publicdatauniquepath_timestamped));
    mkdir(publicdatauniquepath_timestamped);

    # Install the CC0 legalcode. THIS IS ONLY APPLICABLE TO THE MASTERSET!
    copy("../data/LICENSE-CC0-1.0.txt", publicdatauniquepath_timestamped."/LICENSE.txt");

    foreach($data as $raw){
        if($raw['masterset']==1){
            $make="unknown";
            $model="unknown";
            if($raw['make']!=""){
                $make=$cameradata[$raw['make']][$raw['model']]['make'] ?? $cameradata[$raw['make']]['make'] ?? $raw['make'];
            }
            if($raw['model']!=""){
                $model=$cameradata[$raw['make']][$raw['model']]['model'] ?? $raw['model'];
            }
            $make = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $make);
            $model = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $model);
            if(!is_dir(publicdatauniquepath_timestamped."/".$make)){
                mkdir(publicdatauniquepath_timestamped."/".$make);
            }
            if(!is_dir(publicdatauniquepath_timestamped."/".$make."/".$model)){
                mkdir(publicdatauniquepath_timestamped."/".$make."/".$model);
            }
            symlink(datapath."/".hash_id($raw['id'])."/".$raw['id']."/".$raw['filename'],publicdatauniquepath_timestamped."/".$make."/".$model."/".$raw['filename']);
            $sha256table[$make."/".$model."/".$raw['filename']]=$raw['checksum'];

            if(!in_array($make,$makes)){
                $makes[]=$make;
            }
        }
    }

    ksort($sha256table, SORT_NATURAL | SORT_FLAG_CASE);

    $fp=fopen(publicdatauniquepath_timestamped."/filelist.sha256","w");
    foreach($sha256table as $file=>$sha256) {
        // There are two schemes:
        // <hash><space><space><filename>      <- read in text mode
        // <hash><space><asterisk><filename>   <- read in binary mode
        fprintf($fp,"%s *%s\n",$sha256,$file);
    }
    fclose($fp);

    file_put_contents(publicdatauniquepath_timestamped."/timestamp.txt",time());

    //--------------------------------------------------------------------------

    if(file_exists(publicdatauniquepath)) {
        if(is_link(publicdatauniquepath)) {
            define('publicdatauniquepath_old', realpath(publicdatauniquepath));
            // NOTE: do not delete anything yet.
        } else if(is_dir(publicdatauniquepath)) {
            delTree(publicdatauniquepath);
        } else {
            assert(false);
        }
    }

    define('publicdatauniquepath_new', publicdatauniquepath.".new");

    assert(!file_exists(publicdatauniquepath_new));
    symlink(basename(publicdatauniquepath_timestamped), publicdatauniquepath_new);
    rename(publicdatauniquepath_new, publicdatauniquepath); // replaces old symlink!

    if(defined("publicdatauniquepath_old")) {
        delTree(publicdatauniquepath_old);
    }

    //--------------------------------------------------------------------------

    // We're done, release the lock.
    unlink(publicdatauniquepath_lock);
    flock($lock, LOCK_UN);
    fclose($lock);
