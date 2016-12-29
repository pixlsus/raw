<?php
    include("../config.php");
    include("functions.php");

    if(!isset($_SESSION['loggedin'])){
        $_SESSION['referer']=$_SERVER['REQUEST_URI'];
        header("Location: ".baseurl."/login.php");
        exit();
    }

    $password=$_POST['password'] ?? '';
    $newpassword1=$_POST['newpassword1'] ?? '';
    $newpassword2=$_POST['newpassword2'] ?? '';
    $email=$_POST['email'] ?? '';
    
    if(isset($_POST['notify'])){
        $notify=1;
    } else {
        $notify=0;
    }

    if( ($newpassword1!="") and ($newpassword1==$newpassword2) and user_validate($_SESSION['username'],$password )) {
        user_chpasswd($_SESSION['username'],$password,$newpassword1);
    }
    
    user_modify($_SESSION['username'],$email,$notify);
    
    header("Location: ".baseurl."/admin.php");
