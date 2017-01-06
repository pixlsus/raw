<?php
    include("../config.php");
    include("functions.php");

    header("Content-Type: image/svg+xml");

    $cameras=raw_getnumberofcameras();
    readfile("http://img.shields.io/badge/cameras-".$cameras."-green.svg");
