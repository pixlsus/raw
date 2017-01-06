{
    "data":
<?php
    include_once "../../config.php";
    include_once "../functions.php";

    $raws=raw_getalldata();
    $i=0;
    foreach($raws as $raw){
        if($raw['validated'] == "1" ){

            $rawpath=datapath."/".hash_id($raw['id'])."/".$raw['id'];

            if(filesize($rawpath."/".$raw['filename'].".exif.txt") > 0 ) {
                $exifdata="<a target='_blank' href='".baseurl."/getfile.php?type=exif&id=".$raw['id']."'>exifdata</a>";
            } else {
                $exifdata="";
            }

            switch($raw['license']){
                case "CC0":
                    $lic = "<a href='https://creativecommons.org/publicdomain/zero/1.0/' title='Creative Commons 0 - Public Domain' class='cc'>co</a>";
                    break;
                case "by-nc-sa/4.0":
                    $lic = "<a href='http://creativecommons.org/licenses/by-nc-sa/4.0/' title='Creative Commons - Attribution, Non-Commercial, ShareAlike 4.0' class='cc'>cbna</a>";
                    break;
                default:
                    $lic = $raw['license'];
                    break;
            }

            $data[]=array($raw['make'],
                          $raw['model'],
                          $raw['mode'],
                          $raw['remark'],
                          $lic,
                          "<a href='".baseurl."/getfile.php?type=raw&id=".$raw['id']."'>".$raw['filename']."</a><div class='checksumdata'>". $raw['checksum'] ."</div>",
                          $exifdata);
        }
    }
    echo json_encode($data);
?>
}
