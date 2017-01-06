<?php
    include("../config.php");
    include("functions.php");

    $samples=raw_getnumberofsamples();
    header("Location: https://img.shields.io/badge/samples-".$samples."-green.svg");
