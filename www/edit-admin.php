<?php
    include("../config.php");
    include("functions.php");

    if(!isset($_SESSION['loggedin'])){
        exit(0);
    }

    $id=$_GET['id'] ?? "";
    if(!is_numeric($id)) {
        exit(0);
    }

    $data=raw_getdata($id);
    if(!isset($data)){
        exit(0);
    }

    if($data['validated']=="1") {
        $validated="checked";
    } else {
        $validated="";
    }

    $rawpath=datapath."/".hash_id($data['id'])."/".$data['id'];
    if(filesize($rawpath."/".$data['filename'].".exif.txt") > 0 ) {
        $exifdata="<a href='".baseurl."/data/".hash_id($data['id'])."/".$data['id']."/".$data['filename'].".exif.txt'>exifdata</a>";
    } else {
        $exifdata="no exifdata";
    }

    $rawfile="<a href='".baseurl."/data/".hash_id($data['id'])."/".$data['id']."/".$data['filename']."'>".$data['filename']."</a>";
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
	<title>raw.pixls.us</title>
	<link href="css/jquery-ui.css" rel="stylesheet">
    </head>
    <body>
        <div class="ui-widget">
            rawfile: <?php echo $rawfile?><br>
            exif: <?php echo $exifdata?><br>
            <form action="modify-admin.php" method="post">
                <input type="hidden" id="id" name="id" value="<?php echo $data['id']?>" />
                <input type="hidden" id="checksum" name="checksum" value="<?php echo $data['checksum']?>" />
                <div>
                    <label for="validated">Validated:</label>
                    <input type="checkbox" name="validated" id="validated" <?php echo $validated?>><br>
                </div>
                <div>
                    <label for="make">Make:</label>
                    <input type="text" id="make" name="make" value="<?php echo $data['make']?>" />
                </div>
                <div>
                    <label for="model">Model:</label>
                    <input type="text" id="model" name="model" value="<?php echo $data['model']?>" />
                </div>
                <div>
                    <label for="mode">Mode:</label>
                    <input type="text" id="mode" name="mode" value="<?php echo $data['mode']?>" />
                </div>
                <div>
                    <label for="checksum">Checksum:</label>
                    <input type="text" id="checksum" name="checksum" value="<?php echo $data['checksum']?>" />
                </div>
                <div>
                    <label for="remark">Comment:</label>
                    <input type="text" id="remark" name="remark" value="<?php echo $data['remark']?>" />
                </div>
                <div>
                    <label for="license">License:</label>
                    <input type="text" id="license" name="license" value="<?php echo $data['license']?>" />
                </div>
                <input type="submit" name="submit" id="submit" value="Update" />
            </form>
            <form action="deletefile.php" method="post">
                <input type="hidden" id="deleteid" name="deleteid" value="<?php echo $data['id']?>" />
                <div>
                    <label for="deletecheck">Really delete:</label>
                    <input type="checkbox" name="deletecheck" id="deletecheck" />
                </div>
                <input type="submit" name="delete" id="delete" value="Delete raw" />
            </form>

        </div>
        <script src="js/jquery.min.js"></script>
        <script src="js/jquery-ui.js"></script>
    </body>
</html>
