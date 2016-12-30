<?php
    include("../config.php");
    include("functions.php");

    if(!isset($_SESSION['loggedin'])){
        $_SESSION['referer']=$_SERVER['REQUEST_URI'];
        header("Location: ".baseurl."/login.php");
        exit();
    }

    $id = $_POST['deleteid'] ?? '';

    if(isset($_POST['deletecheck'])){
        raw_delete($id);
    }
    header("Location: ".baseurl."/admin.php");
