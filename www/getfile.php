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
        $file=datapath."/raw_pixls_us_archive.zip";
        if(file_exists($file)){
            header('Content-Type: '.mime_content_type($file));
            header('Content-Disposition: attachment; filename="'.basename($file).'"');
            header('Content-Length: ' . filesize($file));
            readfile($file);
        }
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
                    header('Content-Type: '.mime_content_type($file));
                    header('Content-Disposition: attachment; filename="'.basename($file).'"');
                    header('Content-Length: ' . filesize($file));
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
            case "sha1sum":
                header('Content-Type: text/plain');
                //header('Content-Disposition: attachment; filename="'.$data['filename'].".sha1");
                echo $data['checksum']."  ".$data['filename'];
                break;
        }
    }
