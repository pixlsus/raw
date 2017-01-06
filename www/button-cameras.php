<?php
    include("../config.php");
    include("functions.php");

    $cameras=raw_getnumberofcameras();

    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Sun, 25 Aug 1991 05:00:00 GMT");
    header("Location: https://img.shields.io/badge/cameras-".$cameras."-green.svg?maxAge=3600");
