<?php

function buildpublicdatadir($raws, $timestamp, $publicdatapath, $publicdataurl, $publicdataannexuuid, $namespace) {
    $publicdatagitlfstmppath = $publicdatapath."-git-lfs";
    $publicdatagitannexmastertmppath = $publicdatapath."-git-annex-branch-master";
    $publicdatagitannexgitannextmppath = $publicdatapath."-git-annex-branch-git-annex";

    $RCDs = [
                "publicdatapath" => new RefCountedDir($publicdatapath),
                "publicdatagitannexrepopath" => new RefCountedDir($publicdatapath.".annex.git"),
                "publicdatagitlfsrepopath" => new RefCountedDir($publicdatapath.".lfs.git"),
            ];

    foreach($RCDs as $RCD) {
        $RCD->gc();
    }

    $tempdirs = [
        $publicdatagitlfstmppath,
        $publicdatagitannexmastertmppath,
        $publicdatagitannexgitannextmppath
    ];

    foreach($tempdirs as $tempdir) {
        if(is_dir($tempdir)){
            delTree($tempdir);
        }
        mkdir($tempdir);
    }

    $tree = get_as_leafless_tree($raws, function($raw) {
        return [$raw->make, $raw->model];
    });

    foreach([
                $RCDs["publicdatapath"]->staging,
                $publicdatagitlfstmppath,
                $publicdatagitannexmastertmppath
            ] as $prefix) {
        foreach(array_keys($tree) as $make) {
            mkdir($prefix."/".$make);
            foreach(array_keys($tree[$make]) as $model) {
                mkdir($prefix."/".$make."/".$model);
            }
        }
    }

    foreach($raws as $raw) {
        $prefix = $RCDs["publicdatapath"]->staging;
        $link = $prefix."/".$raw->getOutputPath();
        $raw = $raw->raw;
        $target = datapath."/".hash_id($raw['id'])."/".$raw['id']."/".$raw['filename'];
        symlink($target, $link);
    }

    foreach($raws as $raw) {
        $prefix = $publicdatagitlfstmppath;
        writeGitLFSPointer($prefix."/".$raw->getOutputPath(), $raw->raw);
    }

    foreach($raws as $raw) {
        $sha256table[$raw->getOutputPath()] = $raw->raw['checksum'];
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

    file_put_contents($RCDs["publicdatapath"]->staging."/timestamp.txt",$timestamp);

    foreach (scandir($RCDs["publicdatapath"]->staging) as $filename) {
        if(is_file($RCDs["publicdatapath"]->staging."/".$filename)) {
            foreach ([
                        $publicdatagitlfstmppath,
                        $publicdatagitannexmastertmppath
                     ] as $o) {
                copy($RCDs["publicdatapath"]->staging."/".$filename, $o."/".$filename);
            }
        }
    }

    turnIntoAGitLFSRepo($publicdatagitlfstmppath, $RCDs["publicdatagitlfsrepopath"]->staging, $namespace);

    //--------------------------------------------------------------------------

    $gitannexentries = [];
    foreach($raws as $raw) {
        $gitannexentries[] = new GitAnnexEntry($raw);
    }

    foreach($gitannexentries as $e) {
        $prefix = $publicdatagitannexmastertmppath;
        $target = implode("/", ["..","..",".git","annex","objects", ...$e->dirs, $e->key,$e->key]);
        $link = $prefix."/".$e->raw->getOutputPath();
        symlink($target, $link);
    }

    $fp=fopen($publicdatagitannexmastertmppath."/.gitattributes","w");
    fprintf($fp,"%s annex.backend=SHA256 -text\n", "*");
    foreach (scandir($publicdatagitannexmastertmppath) as $filename) {
        if(is_file($publicdatagitannexmastertmppath."/".$filename)) {
           fprintf($fp,"%s !annex.backend text\n", $filename);
        }
    }
    fclose($fp);

    turnIntoAGitRepo($publicdatagitannexmastertmppath, "master");

    $fp = fopen($publicdatagitannexgitannextmppath."/uuid.log","w");
    fprintf($fp,"%s %s timestamp=%s"."s\n", $publicdataannexuuid, $publicdataurl.".annex.git", $timestamp);
    fclose($fp);

    foreach($gitannexentries as $e) {
        $dir = $publicdatagitannexgitannextmppath;
        foreach($e->dirs as $p) {
            $dir .= "/".$p;
            if(!is_dir($dir)) {
                mkdir($dir);
            }
        }

        $fp = fopen($dir."/".$e->key.".log", "w");
        fprintf($fp,"%s"."s 1 %s\n", $timestamp, $publicdataannexuuid);
        fclose($fp);
    }

    turnIntoAGitRepo($publicdatagitannexgitannextmppath, "git-annex");

    assembleGitRepo($RCDs["publicdatagitannexrepopath"]->staging, [$publicdatagitannexmastertmppath, $publicdatagitannexgitannextmppath]);

    $fp = fopen($RCDs["publicdatagitannexrepopath"]->staging."/config", "w+");
    fprintf($fp,"[annex]\n".
                "\tuuid = %s\n".
                "\tversion = %s\n", $publicdataannexuuid, 10);
    fclose($fp);

    foreach($gitannexentries as $e) {
        $dir = $RCDs["publicdatagitannexrepopath"]->staging;
        $dirs = ["annex", "objects", ...$e->dirs, $e->key];
        foreach($dirs as $p) {
            $dir .= "/".$p;
            if(!is_dir($dir)) {
                mkdir($dir);
            }
        }

        $raw = $e->raw->raw;
        $target = datapath."/".hash_id($raw['id'])."/".$raw['id']."/".$raw['filename'];
        $link = $dir."/".$e->key;
        symlink($target, $link);
    }

    //--------------------------------------------------------------------------

    foreach($RCDs as $RCD) {
        $RCD->commit();
    }

    foreach($tempdirs as $tempdir) {
        delTree($tempdir);
    }
}
