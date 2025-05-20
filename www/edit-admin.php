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
        $exifdata=raw_readexif(datapath."/".hash_id($id)."/".$id."/".$data['filename']);
        $newdata=raw_parseexif($exifdata);
        $tmpdata['make']=$newdata['make'] ?? "";
        $tmpdata['model']=$newdata['model'] ?? "";
        $tmpdata['mode']=$newdata['mode'] ?? "";
        $tmpdata['aspectratio']=$newdata['aspectratio'] ?? "";
        $tmpdata['bitspersample']=$newdata['bitspersample'] ?? "";
        $tmpdata['pixels']=$newdata['pixels'] ?? "";
    }

    if($data['validated']=="1") {
        $validated="checked";
    } else {
        $validated="";
    }

    if($data['masterset']=="1") {
        $masterset="checked";
    } else {
        $masterset="";
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

    //echo "<pre>"; print_r($tmpdata);echo "</pre>";
    //exit(0);
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
	<title>raw.pixls.us</title>
	<link href="css/jquery-ui.css" rel="stylesheet">
  <style type="text/css">
img {
  display: block;
  max-width:100vw;
  max-height:95vh;
  width: auto;
  height: auto;
}
  </style>
    </head>
    <body>
        <table>
        <tr>
        <td>
        <div class="ui-widget">
            rawfile: <?php echo $rawfile?><br>
            exif: <?php echo $exifdata?><br>
            <?php if($parseexif!="true") {echo $reparse;} ?>
            <form action="modify-admin.php" method="post">
                <input type="hidden" id="id" name="id" value="<?php echo $tmpdata['id']?>" />
                <input type="hidden" id="checksum" name="checksum" value="<?php echo $tmpdata['checksum']?>" />
                <input type="hidden" id="filesize" name="filesize" value="<?php echo $tmpdata['filesize']?>" />
                <div>
                    <label for="validated">Validated:</label>
                    <input type="checkbox" name="validated" id="validated" <?php echo $validated?>><br>
                </div>
                <div>
                    <label for="make">Make:</label>
                    <input type="text" id="make" name="make" value="<?php echo htmlspecialchars($tmpdata['make'])?>" /><?php if($parseexif) echo " was : ".$data['make'] ?>
                </div>
                <div>
                    <label for="model">Model:</label>
                    <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($tmpdata['model'])?>" /><?php if($parseexif) echo " was : ".$data['model'] ?>
                </div>
                <div>
                    <label for="mode">Mode:</label>
                    <input type="text" id="mode" name="mode" value="<?php echo htmlspecialchars($tmpdata['mode'])?>" /><?php if($parseexif) echo " was : ".$data['mode'] ?>
                </div>
                <div>
                    <label for="aspectratio">Aspect ratio:</label>
                    <input type="text" id="aspectratio" name="aspectratio" value="<?php echo htmlspecialchars($tmpdata['aspectratio'])?>" /><?php if($parseexif) echo " was : ".$data['aspectratio'] ?>
                </div>
                <div>
                    <label for="pixels">Pixels:</label>
                    <input type="text" id="pixels" name="pixels" value="<?php echo htmlspecialchars($tmpdata['pixels'])?>" /><?php if($parseexif) echo " was : ".$data['pixels'] ?>
                </div>
                <div>
                    <label for="bitspersample">Bits per sample:</label>
                    <input type="text" id="bitspersample" name="bitspersample" value="<?php echo htmlspecialchars($tmpdata['bitspersample'])?>" /><?php if($parseexif) echo " was : ".$data['bitspersample'] ?>
                </div>
                <div>
                    <label for="checksum">Checksum:</label>
                    <input type="text" id="checksum" name="checksum" value="<?php echo htmlspecialchars($tmpdata['checksum'])?>" />
                </div>
                <div>
                    <label for="filesize">Filesize:</label>
                    <input type="text" id="filesize" name="filesize" value="<?php echo htmlspecialchars($tmpdata['filesize'])?>" />
                </div>
                <div>
                    <label for="remark">Comment:</label>
                    <input type="text" id="remark" name="remark" value="<?php echo htmlspecialchars($tmpdata['remark'])?>" />
                </div>
                <div>
                    <label for="license">License:</label>
                    <input type="text" id="license" name="license" value="<?php echo htmlspecialchars($tmpdata['license'])?>" />
                </div>
<?php if($tmpdata['license']=='CC0'){ ?>
                <div>
                    <label for="masterset">Masterset:</label>
                    <input type="checkbox" name="masterset" id="masterset" disabled <?php echo htmlspecialchars($masterset)?>><br>
                    <label for="mastersetreason">Masterset reason:</label>
                    <input type="text" id="mastersetreason" name="mastersetreason" />
                </div>
<?php } ?>
                <input type="submit" name="submit" id="submit" value="Update" />
            </form>
            <br>
<?php if($tmpdata['masterset']==0){ ?>
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
<?php } ?>
        </div>
        </td>
        <td class="preview">
        <?php echo $previewimage?>
        </td>
        </tr>
        </table>
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

    $("#mastersetreason").keyup(function() {
        if ( $("#mastersetreason").val().length !=0  ) {
            $("#masterset").prop('disabled', false);
        } else {
            $("#masterset").prop('disabled', true);
        }
    });
} );
        </script>

    </body>
</html>
