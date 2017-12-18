<!DOCTYPE html>
<html>
    <head>
        <title>diff</title>
        <style>
      .diff td{
        padding:0 0.667em;
        vertical-align:top;
        white-space:pre;
        white-space:pre-wrap;
        font-family:Consolas,'Courier New',Courier,monospace;
        font-size:0.75em;
        line-height:1.333;
      }

      .diff span{
        display:block;
        min-height:1.333em;
        margin-top:-1px;
        padding:0 3px;
      }

      * html .diff span{
        height:1.333em;
      }

      .diff span:first-child{
        margin-top:0;
      }

      .diffDeleted span{
        border:1px solid rgb(255,192,192);
        background:rgb(255,224,224);
      }

      .diffInserted span{
        border:1px solid rgb(192,255,192);
        background:rgb(224,255,224);
      }

      #toStringOutput{
        margin:0 2em 2em;
      }

        </style>
    </head>
    <body>
<?php
    include("../config.php");
    include("functions.php");
    include("class.Diff.php"); // http://code.stephenmorley.org/php/diff-implementation/

    if(isset($_SERVER['PATH_INFO'])){
        if(preg_match("/\/([0-9]+)\/([0-9]+)/",$_SERVER['PATH_INFO'],$matches)){
            $id1=$matches[1];
            $id2=$matches[2];
        } else {
            exit();
        }
    }

    $raw1=raw_getdata($id1);
    $raw2=raw_getdata($id2);

    $exiv1=datapath."/".hash_id($id1)."/".$id1."/".$raw1['filename'].".exif.txt";
    $exiv2=datapath."/".hash_id($id2)."/".$id2."/".$raw2['filename'].".exif.txt";

    if((filesize($exiv1) > 0 ) and (filesize($exiv2) > 0 )){
        echo "exiv2 output<br><hr>";
        echo "<table width='100%'><tr><th>".$id1." : ".$raw1['filename']."</th><th>".$id2." : ".$raw2['filename']."</th></tr></table><hr>";
        echo Diff::toTable(Diff::compareFiles($exiv1, $exiv2));
        echo "<hr>";
    }

    $exiftool1=datapath."/".hash_id($id1)."/".$id1."/".$raw1['filename'].".exiftool.txt";
    $exiftool2=datapath."/".hash_id($id2)."/".$id2."/".$raw2['filename'].".exiftool.txt";

    if((filesize($exiftool1) > 0 ) and (filesize($exiftool2) > 0 )){
        echo "exiftool output<br><hr>";
        echo "<table width='100%'><tr><th>".$id1." : ".$raw1['filename']."</th><th>".$id2." : ".$raw2['filename']."</th></tr></table><hr>";
        echo Diff::toTable(Diff::compareFiles($exiftool1, $exiftool2));
    }

//            if(filesize($rawpath."/".$raw['filename'].".exiftool.txt") > 0 ) {
//                $exifdata.=" <a target='_blank' href='".baseurl."/getfile.php/".$raw['id']."/exiftool/".$raw['filename'].".exiftool.txt'>exiftool</a>";
//            }
?>
    </body>
</html>
