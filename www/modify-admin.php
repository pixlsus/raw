<?php
    include("../config.php");
    include("functions.php");

    if(!isset($_SESSION['loggedin'])){
        $_SESSION['referer']=$_SERVER['REQUEST_URI'];
        header("Location: ".baseurl."/login.php");
        exit();
    }

    $id = $_POST['id'] ?? '';
    $checksum = $_POST['checksum'] ?? '';

    if(isset($_POST['validated'])){
        $data['validated']=1;
    } else {
        $data['validated']=0;
    }
    $data['make']=$_POST['make'] ?? '';
    $data['model']=$_POST['model'] ?? '';
    $data['mode']=$_POST['mode'] ?? '';
    $data['remark']=$_POST['remark'] ?? '';
    $data['license']=$_POST['license'] ?? '';
    $data['aspectratio']=$_POST['aspectratio'] ?? '';
    $data['bitspersample']=$_POST['bitspersample'] ?? '';

    if(raw_check($id,$checksum)==1){
        raw_modify($id,$data);
    }
    $cameras=raw_getnumberofcameras();
    #file_put_contents("button-cameras.svg", file_get_contents("https://img.shields.io/badge/cameras-".$cameras."-green.svg?maxAge=3600"));
    system("wget https://img.shields.io/badge/samples-".$cameras."-green.png?maxAge=3600 -O button-cameras.png");
    $samples=raw_getnumberofsamples();
    #file_put_contents("button-samples.svg", file_get_contents("https://img.shields.io/badge/samples-".$samples."-green.svg?maxAge=3600"));
    system("wget https://img.shields.io/badge/samples-".$samples."-green.png?maxAge=3600 -O button-samples.png");


    header("Location: ".baseurl."/admin.php");
