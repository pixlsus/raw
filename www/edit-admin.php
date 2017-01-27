<?php
    include("../config.php");
    include("functions.php");

    if(!isset($_SESSION['loggedin'])){
        $_SESSION['referer']=$_SERVER['REQUEST_URI'];
        header("Location: ".baseurl."/login.php");
        exit(0);
    }

    $id=$_GET['id'] ?? "";
    $parseexif=$_GET['parseexif'] ?? "";

    if(!is_numeric($id)) {
        exit(0);
    }

    $data=raw_getdata($id);
    $tmpdata=$data;

    if(!isset($data)){
        exit(0);
    }
    if($parseexif == "true" ){
        $exifdata=raw_readexif(datapath."/".hash_id($id)."/".$id."/".$data['filename'].".exif.txt");
        $newdata=raw_parseexif($exifdata);
        $tmpdata['make']=$newdata['make'] ?? "";
        $tmpdata['model']=$newdata['model'] ?? "";
        $tmpdata['mode']=$newdata['mode'] ?? "";
        $tmpdata['aspectratio']=$newdata['aspectratio'] ?? "";
        $tmpdata['bitspersample']=$newdata['bitspersample'] ?? "";
    }

    if($data['validated']=="1") {
        $validated="checked";
    } else {
        $validated="";
    }

    $rawpath=datapath."/".hash_id($data['id'])."/".$data['id'];

    $exifdata="";
    if(filesize($rawpath."/".$data['filename'].".exif.txt") > 0 ) {
        $exifdata.="<a target='_blank' href='".baseurl."/getfile.php/".$data['id']."/exif/".$data['filename'].".exif.txt'>exiv2</a>";
    }
    if(filesize($rawpath."/".$data['filename'].".exiftool.txt") > 0 ) {
        $exifdata.=" <a target='_blank' href='".baseurl."/getfile.php/".$data['id']."/exiftool/".$data['filename'].".exiftool.txt'>exiftool</a>";
    }

    $rawfile="<a href='".baseurl."/getfile.php?type=raw&id=".$data['id']."'>".$data['filename']."</a>";

    $files=scandir($rawpath);
    $preview=current(preg_grep("/.*-preview[0-9]\.jpg$/", $files));
    if($preview!=""){
        $previewimage="<img src='".baseurl."/getfile.php?type=preview&id=".$data['id']."' width='100%'>\n";
    } else {
        $previewimage="No preview image available<br>\n";
    }

    $reparse="<a href='".baseurl."/edit-admin.php?parseexif=true&id=".$data['id']."'>Re-evaluate exif</a>\n";
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
            <?php if($parseexif!="true") {echo $reparse;} ?>
            <form action="modify-admin.php" method="post">
                <input type="hidden" id="id" name="id" value="<?php echo $tmpdata['id']?>" />
                <input type="hidden" id="checksum" name="checksum" value="<?php echo $tmpdata['checksum']?>" />
                <div>
                    <label for="validated">Validated:</label>
                    <input type="checkbox" name="validated" id="validated" <?php echo $validated?>><br>
                </div>
                <div>
                    <label for="make">Make:</label>
                    <input type="text" id="make" name="make" value="<?php echo $tmpdata['make']?>" /><?php if($parseexif) echo " was : ".$data['make'] ?>
                </div>
                <div>
                    <label for="model">Model:</label>
                    <input type="text" id="model" name="model" value="<?php echo $tmpdata['model']?>" /><?php if($parseexif) echo " was : ".$data['model'] ?>
                </div>
                <div>
                    <label for="mode">Mode:</label>
                    <input type="text" id="mode" name="mode" value="<?php echo $tmpdata['mode']?>" /><?php if($parseexif) echo " was : ".$data['mode'] ?>
                </div>
                <div>
                    <label for="aspectratio">Aspect ratio:</label>
                    <input type="text" id="aspectratio" name="aspectratio" value="<?php echo $tmpdata['aspectratio']?>" /><?php if($parseexif) echo " was : ".$data['aspectratio'] ?>
                </div>
                <div>
                    <label for="bitspersample">Bits per sample:</label>
                    <input type="text" id="bitspersample" name="bitspersample" value="<?php echo $tmpdata['bitspersample']?>" /><?php if($parseexif) echo " was : ".$data['bitspersample'] ?>
                </div>
                <div>
                    <label for="checksum">Checksum:</label>
                    <input type="text" id="checksum" name="checksum" value="<?php echo $tmpdata['checksum']?>" />
                </div>
                <div>
                    <label for="remark">Comment:</label>
                    <input type="text" id="remark" name="remark" value="<?php echo $tmpdata['remark']?>" />
                </div>
                <div>
                    <label for="license">License:</label>
                    <input type="text" id="license" name="license" value="<?php echo $tmpdata['license']?>" />
                </div>
                <input type="submit" name="submit" id="submit" value="Update" />
            </form>
            <br>
            <?php echo $previewimage?>
            <br>
            <form action="deletefile.php" method="post">
                <input type="hidden" id="deleteid" name="deleteid" value="<?php echo $tmpdata['id']?>" />
                <div>
                    <label for="deletereason">Reason for deletion:</label>
                    <input type="text" id="deletereason" name="deletereason" />
                </div>
                <div>
                    <label for="deletecheck">Really delete:</label>
                    <input type="checkbox" name="deletecheck" id="deletecheck" />
                </div>
                <input type="submit" name="delete" id="delete" value="Delete raw" disabled/>
            </form>

        </div>
        <script src="js/jquery.min.js"></script>
        <script src="js/jquery-ui.js"></script>
        <script>
$(document).ready(function() {
    $("#deletereason").keyup(function() {
        if ( $("#deletereason").val().length !=0  ) {
            $("#delete").prop('disabled', false);
        } else {
            $("#delete").prop('disabled', true);
        }
    });
} );
        </script>

    </body>
</html>
