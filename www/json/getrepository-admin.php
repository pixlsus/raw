{
    "data":
<?php
    include_once "../../config.php";
    include_once "../functions.php";

    if(isset($_SESSION['loggedin'])){
        $raws=raw_getalldata();

        foreach($raws as $raw){
            switch($raw['validated']){
                case 0:
                    $validate="Validated";
                    break;
                case 1:
                    $validate="";
                    break;
                case 2:
                    // dupes, aborted uploads etc.
                    $validate="Aborted upload";
                    continue;
                    break;
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
        echo json_encode($data);
    }
?>
}
