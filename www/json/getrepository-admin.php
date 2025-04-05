{
    "data":
<?php
    include_once "../../config.php";
    include_once "../functions.php";
    
    $state=$_GET['state'] ?? "all";

    if(isset($_SESSION['loggedin'])){
        $raws=raw_getalldata();

        foreach($raws as $raw){
            $validate="";
            switch($raw['state']){
                case "validated":
                    $validate="Validated";
                    break;
                case "orphaned":
                    $validate="Orphaned";
                    break;
                case 2:
                    // dupes, aborted uploads etc.
                    $validate="Aborted upload";
                    break;
            }
            if($raw['masterset']==1){
                $validate="Masterset";
            }

            $filesize=human_filesize($raw['filesize']);

            $rawpath=datapath."/".hash_id($raw['id'])."/".$raw['id'];

            $exifdata="";
            if(filesize($rawpath."/".$raw['filename'].".exif.txt") > 0 ) {
                $exifdata.="<a target='_blank' href='".baseurl."/getfile.php/".$raw['id']."/exif/".$raw['filename'].".exif.txt'>exiv2</a>";
            }
            if(filesize($rawpath."/".$raw['filename'].".exiftool.txt") > 0 ) {
                $exifdata.=" <a target='_blank' href='".baseurl."/getfile.php/".$raw['id']."/exiftool/".$raw['filename'].".exiftool.txt'>exiftool</a>";
            }


            if($state=="all" or ($state=="masterset" and $raw['masterset']==1) or ($state==$raw['state']) ) {
              $data[]=array($validate,
                            $raw['make'],
                            $raw['model'],
                            $raw['mode'],
                            $raw['aspectratio'],
                            $raw['bitspersample'],
                            $raw['remark'],
                            $raw['license'],
                            $raw['checksum'],
                            $filesize,
                            $raw['pixels'],
                            $raw['date'],
                            "<a href='".baseurl."/getfile.php/".$raw['id']."/raw/".$raw['filename']."'>".$raw['filename']."</a>",
                            $exifdata,
                            "<a href='".baseurl."/edit-admin.php?id=".$raw['id']."'>edit</a>");
            }
        }
        echo json_encode($data);
    }
?>
}
