{
    "data":
<?php
    include_once "../../config.php";
    include_once "../functions.php";

    echo json_encode(unserialize(file_get_contents(datapath."/missingcameradata.serialize")));
?>
}
