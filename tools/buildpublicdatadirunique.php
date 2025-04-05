#!/usr/bin/php
<?php
    include("../config.php");
    include("../www/functions.php");

    // We don't really want to race with ourselves, so acquire a lock.
    define('publicdatauniquepath_lock', publicdatauniquepath.'.lock');
    $lock = fopen(publicdatauniquepath_lock, "w");
    if(!flock($lock, LOCK_EX | LOCK_NB)) {
        echo 'Unable to obtain lock';
        exit(-1);
    }

    define('timestamp', time());
    define('publicdatauniquepath_timestamped', publicdatauniquepath.".".timestamp);

    define('publicdatauniquegittmppath', publicdatauniquepath."-git");
    define('publicdatauniquegitrepopath', publicdatauniquepath.".git");
    define('publicdatauniquegitrepopath_timestamped', publicdatauniquegitrepopath.".".timestamp);

    $cameradata=parsecamerasxml();
    $data=raw_getalldata();
    $makes=array();

    assert(!file_exists(publicdatauniquepath_timestamped));
    mkdir(publicdatauniquepath_timestamped);

    if(is_dir(publicdatauniquegittmppath)){
        delTree(publicdatauniquegittmppath);
    }
    mkdir(publicdatauniquegittmppath);

    # Install the CC0 legalcode. THIS IS ONLY APPLICABLE TO THE MASTERSET!
    copy("../data/LICENSE-CC0-1.0.txt", publicdatauniquepath_timestamped."/LICENSE.txt");

    foreach($data as $raw){
        if($raw['masterset']==1){
            $output_filename = get_raw_pretty_name($raw, $make, $model);
            if(!is_dir(publicdatauniquepath_timestamped."/".$make)){
                mkdir(publicdatauniquepath_timestamped."/".$make);
            }
            if(!is_dir(publicdatauniquepath_timestamped."/".$make."/".$model)){
                mkdir(publicdatauniquepath_timestamped."/".$make."/".$model);
            }
            symlink(datapath."/".hash_id($raw['id'])."/".$raw['id']."/".$raw['filename'],publicdatauniquepath_timestamped."/".$output_filename);
            $sha256table[$output_filename]=$raw['checksum'];
            if(!in_array($make,$makes)){
                $makes[]=$make;
            }

            if(!is_dir(publicdatauniquegittmppath."/".$make)){
                mkdir(publicdatauniquegittmppath."/".$make);
            }
            if(!is_dir(publicdatauniquegittmppath."/".$make."/".$model)){
                mkdir(publicdatauniquegittmppath."/".$make."/".$model);
            }
            writeGitLFSPointer(publicdatauniquegittmppath."/".$output_filename, $raw);
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

    foreach (scandir(publicdatauniquepath_timestamped) as $filename) {
        if(is_file(publicdatauniquepath_timestamped."/".$filename)) {
            copy(publicdatauniquepath_timestamped."/".$filename, publicdatauniquegittmppath."/".$filename);
        }
    }

    turnIntoAGitLFSRepo(publicdatauniquegittmppath, publicdatauniquegitrepopath_timestamped);

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
    if(file_exists(publicdatauniquegitrepopath)) {
        if(is_link(publicdatauniquegitrepopath)) {
            define('publicdatauniquegitrepopath_old', realpath(publicdatauniquegitrepopath));
            // NOTE: do not delete anything yet.
        } else if(is_dir(publicdatauniquegitrepopath)) {
            delTree(publicdatauniquegitrepopath);
        } else {
            assert(false);
        }
    }

    define('publicdatauniquepath_new', publicdatauniquepath.".new");
    define('publicdatauniquegitrepopath_new', publicdatauniquegitrepopath.".new");

    assert(!file_exists(publicdatauniquepath_new));
    assert(!file_exists(publicdatauniquegitrepopath_new));
    symlink(basename(publicdatauniquepath_timestamped), publicdatauniquepath_new);
    symlink(basename(publicdatauniquegitrepopath_timestamped), publicdatauniquegitrepopath_new);
    rename(publicdatauniquepath_new, publicdatauniquepath); // replaces old symlink!
    rename(publicdatauniquegitrepopath_new, publicdatauniquegitrepopath); // replaces old symlink!

    if(defined("publicdatauniquepath_old")) {
        delTree(publicdatauniquepath_old);
    }
    if(defined("publicdatauniquegitrepopath_old")) {
        delTree(publicdatauniquegitrepopath_old);
    }

    delTree(publicdatauniquegittmppath);

    //--------------------------------------------------------------------------

    // We're done, release the lock.
    unlink(publicdatauniquepath_lock);
    flock($lock, LOCK_UN);
    fclose($lock);
