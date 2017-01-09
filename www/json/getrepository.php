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
            $filesize=human_filesize(filesize($rawpath."/".$raw['filename']));

            if(filesize($rawpath."/".$raw['filename'].".exif.txt") > 0 ) {
                $exifdata="<a target='_blank' href='".baseurl."/getfile.php/".$raw['id']."/exif/".$raw['filename'].".exif.txt'>exifdata</a>";
            } else {
                $exifdata="";
            }

            $mode="";
            if($raw['bitspersample']!=""){
                $mode.=$raw['bitspersample']."bit";
            }
            if($raw['mode']!=""){
                $mode.=" ".$raw['mode'];
            }
            if($raw['aspectratio']!=""){
                if($mode==""){
                    $mode=$raw['aspectratio'];
                } else {
                    $mode.=" (".$raw['aspectratio'].")";
                }
            }
            $mode=trim($mode);

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
                          $mode,
                          $raw['remark'],
                          $lic,
                          $raw['date'],
                          "<a href='".baseurl."/getfile.php/".$raw['id']."/raw/".$raw['filename']."'>".$raw['filename']."</a><div class='checksumdata'><span title='SHA1 Checksum'>". $raw['checksum'] ."</span>&nbsp;(".$filesize.")</div>",
                          $exifdata);
        }
    }
    echo json_encode($data);
?>
}
