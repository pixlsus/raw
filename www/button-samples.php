<?php
    include("../config.php");
    include("functions.php");

    $samples=raw_getnumberofsamples();

    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Sun, 25 Aug 1991 05:00:00 GMT");
    header("Location: https://img.shields.io/badge/samples-".$samples."-green.svg?maxAge=3600");
