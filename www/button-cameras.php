<?php
    include("../config.php");
    include("functions.php");

    $cameras=raw_getnumberofcameras();
    header("Location: https://img.shields.io/badge/cameras-".$cameras."-green.svg");
