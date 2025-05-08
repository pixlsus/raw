#!/usr/bin/php
<?php
    include("../config.php");
    include("../www/functions.php");

    // We don't really want to race with ourselves, so acquire a lock.
    define('publicdatapath_lock', publicdatapath.'.lock');
    $lock = fopen(publicdatapath_lock, "w");
    if(!flock($lock, LOCK_EX | LOCK_NB)) {
        echo 'Unable to obtain lock';
        exit(-1);
    }

    define('timestamp', time());

    class RefCountedDir
    {
        public $name;
        public $old = NULL;
        public $staging;

        public function __construct(string $name_) {
            $this->name = $name_;
            $this->staging = $this->name.".".timestamp;

            if(file_exists($this->name)) {
                assert(is_link($this->name));
                $this->old = realpath($this->name);
            }

            assert(!file_exists($this->staging));
            mkdir($this->staging);
        }

        public function gc() {
            foreach(glob($this->name.".*") as $e) {
                if($e == $this->staging) {
                    continue; // Keep the newly created staging directory.
                }
                if($this->old != NULL && $e == $this->old) {
                    continue; // Keep the current symlinks valid for now.
                }
                // Delete all other stale timestamped directories.
                if (preg_match("/". str_replace("/", "\/", $this->name) ."\.[0-9]+/", $e)) {
                    delTree($e);
                }
            }
        }

        public function commit() {
            $new = $this->name.".new";
            assert(!file_exists($new));
            symlink(basename($this->staging), $new);
            rename($new, $this->name); // replaces old symlink!
            // NOTE: we intentionally leave the old dir around.
            //       The next run of this script will `gc()` it.
        }
    }

    define('publicdatagittmppath', publicdatapath."-git");

    $RCDs = [
                "publicdatapath" => new RefCountedDir(publicdatapath),
                "publicdatagitrepopath" => new RefCountedDir(publicdatapath.".git"),
            ];

    foreach($RCDs as $RCD) {
        $RCD->gc();
    }

    $cameradata=parsecamerasxml();
    $data=raw_getalldata();
    $makes=array();
    $noncc0samples=0;

    if(is_dir(publicdatagittmppath)){
        delTree(publicdatagittmppath);
    }
    mkdir(publicdatagittmppath);

    foreach($data as $raw){
        if($raw['validated']==1){
            $output_filename = get_raw_pretty_name($raw, $make, $model);
            if(!is_dir($RCDs["publicdatapath"]->staging."/".$make)){
                mkdir($RCDs["publicdatapath"]->staging."/".$make);
            }
            if(!is_dir($RCDs["publicdatapath"]->staging."/".$make."/".$model)){
                mkdir($RCDs["publicdatapath"]->staging."/".$make."/".$model);
            }
            symlink(datapath."/".hash_id($raw['id'])."/".$raw['id']."/".$raw['filename'],$RCDs["publicdatapath"]->staging."/".$output_filename);
            $sha256table[$output_filename]=$raw['checksum'];
            if(!in_array($make,$makes)){
                $makes[]=$make;
            }
            if($raw['license']!="CC0"){
                $noncc0samples++;
            }

            if(!is_dir(publicdatagittmppath."/".$make)){
                mkdir(publicdatagittmppath."/".$make);
            }
            if(!is_dir(publicdatagittmppath."/".$make."/".$model)){
                mkdir(publicdatagittmppath."/".$make."/".$model);
            }
            writeGitLFSPointer(publicdatagittmppath."/".$output_filename, $raw);
        }
    }

    ksort($sha256table, SORT_NATURAL | SORT_FLAG_CASE);

    $fp=fopen($RCDs["publicdatapath"]->staging."/filelist.sha256","w");
    foreach($sha256table as $file=>$sha256) {
        // There are two schemes:
        // <hash><space><space><filename>      <- read in text mode
        // <hash><space><asterisk><filename>   <- read in binary mode
        fprintf($fp,"%s *%s\n",$sha256,$file);
    }
    fclose($fp);

    file_put_contents($RCDs["publicdatapath"]->staging."/timestamp.txt",time());

    foreach (scandir($RCDs["publicdatapath"]->staging) as $filename) {
        if(is_file($RCDs["publicdatapath"]->staging."/".$filename)) {
            copy($RCDs["publicdatapath"]->staging."/".$filename, publicdatagittmppath."/".$filename);
        }
    }

    turnIntoAGitLFSRepo(publicdatagittmppath, $RCDs["publicdatagitrepopath"]->staging, 'data');

    //--------------------------------------------------------------------------

    foreach($RCDs as $RCD) {
        $RCD->commit();
    }

    delTree(publicdatagittmppath);

    //--------------------------------------------------------------------------

    // Badgegeneration
    $cameras=raw_getnumberofcameras();
    file_put_contents("../www/button-cameras.svg", file_get_contents("https://img.shields.io/badge/cameras-".$cameras."-green.svg?maxAge=3600"));
    file_put_contents("../www/button-cameras.png", file_get_contents("https://img.shields.io/badge/cameras-".$cameras."-green.png?maxAge=3600"));
    file_put_contents("../www/button-makes.svg", file_get_contents("https://img.shields.io/badge/makes-".count($makes)."-green.svg?maxAge=3600"));
    file_put_contents("../www/button-makes.png", file_get_contents("https://img.shields.io/badge/makes-".count($makes)."-green.png?maxAge=3600"));
    $samples=raw_getnumberofsamples();
    file_put_contents("../www/button-samples.svg", file_get_contents("https://img.shields.io/badge/samples-".$samples."-green.svg?maxAge=3600"));
    file_put_contents("../www/button-samples.png", file_get_contents("https://img.shields.io/badge/samples-".$samples."-green.png?maxAge=3600"));
    $reposize=raw_gettotalrepositorysize();
    file_put_contents("../www/button-size.svg", file_get_contents("https://img.shields.io/badge/size-".human_filesize($reposize)."-green.svg?maxAge=3600"));
    file_put_contents("../www/button-size.png", file_get_contents("https://img.shields.io/badge/size-".human_filesize($reposize)."-green.png?maxAge=3600"));

    $reposize/=(1024*1024*1024);

    $missingcameras=count(unserialize(file_get_contents(datapath."/missingcameradata.serialize")));


    influxPoints([
        influxPointSerialize("rpu", ["key"=>"cameras"], ["value"=>$cameras]),
        influxPointSerialize("rpu", ["key"=>"samples"], ["value"=>$samples]),
        influxPointSerialize("rpu", ["key"=>"reposize"], ["value"=>$reposize]),
        influxPointSerialize("rpu", ["key"=>"noncc0samples"], ["value"=>$noncc0samples]),
        influxPointSerialize("rpu", ["key"=>"missingcameras"], ["value"=>$missingcameras]),
        influxPointSerialize("rpu", ["key"=>"makes"], ["value"=>count($makes)]),
    ]);

    // We're done, release the lock.
    unlink(publicdatapath_lock);
    flock($lock, LOCK_UN);
    fclose($lock);
