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
    
    if(raw_check($id,$checksum)==1){
        raw_modify($id,$data);
    }
    header("Location: ".baseurl."/admin.php");
