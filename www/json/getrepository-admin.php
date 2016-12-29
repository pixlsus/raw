{
    "data": 
<?php
    include_once "../../config.php";
    include_once "../functions.php";
    
    if(isset($_SESSION['loggedin'])){
        $raws=raw_getalldata();
        foreach($raws as $raw){
            if($raw['validated']=="0"){
                $validate=" ";
            } else {
                $validate="X";
            }

            $rawpath=datapath."/".hash_id($raw['id'])."/".$raw['id'];
            if(filesize($rawpath."/".$raw['filename'].".exif.txt") > 0 ) {
                $exifdata="<a href='".baseurl."/data/".hash_id($raw['id'])."/".$raw['id']."/".$raw['filename'].".exif.txt'>exifdata</a>";
            } else {
                $exifdata="no exifdata";
            }
            $data[]=array($validate,
                          $raw['make'],
                          $raw['model'],
                          $raw['mode'],
                          $raw['remark'],
                          $raw['license'],
                          $raw['checksum'],
                          "<a href='".baseurl."/data/".hash_id($raw['id'])."/".$raw['id']."/".$raw['filename']."'>".$raw['filename']."</a>",
                          $exifdata,
                          "<a href='".baseurl."/edit-admin.php?id=".$raw['id']."'>edit</a>");
        }    
        echo json_encode($data);
    }
?>
}    