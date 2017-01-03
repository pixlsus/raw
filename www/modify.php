<?php
    include("../config.php");
    include("functions.php");

    $sd=$_SESSION['upload'] ?? '';

    $id = $_POST['id'] ?? '';
    $checksum = $_POST['checksum'] ?? '';

    $data['make']=$_POST['make'] ?? '';
    $data['model']=$_POST['model'] ?? '';
    $data['remark']=$_POST['remark'] ?? '';
    $data['license']="CC0";

    if(raw_check($id,$checksum)==1 and $checksum==$sd){
        raw_modify($id,$data);
        $_SESSION['upload']='';
        notify($id,"new");
    }
    header("Location: ".baseurl);
