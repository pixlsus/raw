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
    $filesize = $_POST['$filesize'] ?? '';

    if(isset($_POST['validated'])){
        $data['validated']=1;
    } else {
        $data['validated']=0;
        $data['state']='validated';
    }
    if(isset($_POST['masterset']) and isset($_POST['license']) and $_POST['license'] == 'CC0'){
        $data['masterset']=1;
    } else {
        $data['masterset']=0;
    }
    $data['make']=$_POST['make'] ?? '';
    $data['model']=$_POST['model'] ?? '';
    $data['mode']=$_POST['mode'] ?? '';
    $data['remark']=$_POST['remark'] ?? '';
    $data['license']=$_POST['license'] ?? '';
    $data['aspectratio']=$_POST['aspectratio'] ?? '';
    $data['bitspersample']=$_POST['bitspersample'] ?? '';
    $data['pixels']=$_POST['pixels'] ?? '';

    if(raw_check($id,$checksum)==1){
        $olddata=raw_getdata($id);
        raw_modify($id,$data);
        notify($id,"modify",$olddata);
    }

    header("Location: ".baseurl."/admin.php");
