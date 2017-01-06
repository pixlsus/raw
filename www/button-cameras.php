<?php
    include("../config.php");
    include("functions.php");

    header("Content-Type: image/svg+xml");

    $cameras=raw_getnumberofcameras();
    readfile("https://img.shields.io/badge/cameras-".$cameras."-green.svg");
