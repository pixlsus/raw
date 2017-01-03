<?php
    include("../config.php");
    include("functions.php");

    if(!isset($_SESSION['loggedin'])){
        $_SESSION['referer']=$_SERVER['REQUEST_URI'];
        header("Location: ".baseurl."/login.php");
        exit();
    }

    $id = $_POST['deleteid'] ?? '';
    $reason = $_POST['deletereason'] ?? '';

    if(isset($_POST['deletecheck'])){
        notify($id,"delete",$reason);
        raw_delete($id);
    }
    header("Location: ".baseurl."/admin.php");
