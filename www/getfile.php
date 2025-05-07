<?php
    include("../config.php");
    include("functions.php");

    $id=$_GET['id'] ?? '';
    $type=$_GET['type'] ?? '';

    if($id=='' and $type=='' and isset($_SERVER['PATH_INFO'])){
        if(preg_match("/\/([0-9]+)\/([a-z]+)\/(.*)/",$_SERVER['PATH_INFO'],$matches)){
            $id=$matches[1];
            $type=$matches[2];
        }
    }

    if($type=="archive"){
        header("410 Gone");
        echo("Zip archive support is removed. Please use 'git clone ".publicdataurl.".lfs.git' to make a full mirror (requires Git LFS extension to be installed) or mirror the data from ".publicdataurl."/");
        exit(0);
    }

    if(!is_numeric($id)){
        echo "wierd shit is happening";
        exit (0);
    }

    $data=raw_getdata($id);
    $datapath=datapath."/".hash_id($id)."/".$id;

    if(isset($data['validated']) and ($data['validated']==1 or isset($_SESSION['loggedin']))){
        switch($type){
            case "exif":
                $file=$datapath."/".$data['filename'].".exif.txt";
                if(file_exists($file)){
                    header('Content-Type: text/plain');
                    readfile($file);
                }
                break;
            case "exiftool":
                $file=$datapath."/".$data['filename'].".exiftool.txt";
                if(file_exists($file)){
                    header('Content-Type: text/plain');
                    readfile($file);
                }
                break;
            case "raw":
                $file=$datapath."/".$data['filename'];
                if(file_exists($file)){
                    $session = "";
                    $namespace = "data";
                    $filename = get_raw_pretty_name($data, $make, $model);
                    influxPoint("downloads",
                                [
                                    "namespace" => $namespace,
                                    "filename" => '"'.$filename.'"',
                                    "filesha256hash" => '"'.$data['checksum'].'"',
                                ],
                                [
                                    "filesize" => $data['filesize'],
                                    "session" => $session
                                ]);
                    header('Content-Type: '.mime_content_type($file));
                    header('Content-Disposition: attachment; filename="'.basename($file).'"');
                    header('Content-Length: ' . $data['filesize']);
                    readfile($file);
                }
                break;
            case "nice":
                $file=$datapath."/".$data['filename'];
                if(file_exists($file)){
                    $session = "";
                    $namespace = "data";
                    $filename = get_raw_pretty_name($data, $make, $model);
                    influxPoint("downloads",
                                [
                                    "namespace" => $namespace,
                                    "filename" => '"'.$filename.'"',
                                    "filesha256hash" => '"'.$data['checksum'].'"',
                                ],
                                [
                                    "filesize" => $data['filesize'],
                                    "session" => $session
                                ]);
                    $pathinfo=pathinfo($file);
                    $filename=$matches[3];
                    $stat=stat($file);
                    header('Content-Type: '.mime_content_type($file));
                    header('Content-Disposition: attachment; filename="'.$filename.'"');
                    header('Content-Length: ' . $data['filesize']);
                    header('Date: ' . date("r",$stat['mtime']));
                    readfile($file);
                }
                break;
            case "preview":
                $files=scandir($datapath);
                $preview=current(preg_grep("/.*-preview[0-9]\.jpg$/", $files));
                if($preview!=""){
                    header('Content-Type: image/jpeg');
                    readfile($datapath."/".$preview);
                }
                break;
            case "sha256sum":
                header('Content-Type: text/plain');
                //header('Content-Disposition: attachment; filename="'.$data['filename'].".sha256");
                echo $data['checksum']."  ".$data['filename'];
                break;
        }
    }
