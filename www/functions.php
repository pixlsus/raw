<?php
    session_start();
    require_once "Mail.php";

// db shizzle
    function db_init() {
        try {
            $dbh=new pdo(dbdsn,dbuser,dbpw,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT));
        } catch (PDOException $e) {
            error_log('Connection failed: ' . $e->getMessage());
            exit(0);
        }
        return($dbh);
    }

// otherstuff
    function hash_id($id) {
        return(implode("/",str_split(substr(str_pad($id,hashdepth,"0"),-hashdepth))));
    }

    function aspectratio($width,$height,$tolerance=0.1) {
        if($width==0 or $height==0) {
            return("NAN");
        }
        $ar=$width/$height;
        if($ar<1) {
            $ar=1/$ar;
        }
        if(abs($ar/(4/3)-1.0)<$tolerance) {
            $ars="4:3";
        } elseif (abs($ar/(16/9)-1.0)<$tolerance) {
            $ars="16:9";
        } elseif (abs($ar/(3/2)-1.0)<$tolerance) {
            $ars="3:2";
        } elseif (abs($ar/(1/1)-1.0)<$tolerance) {
            $ars="1:1";
        } elseif (abs($ar/(5/4)-1.0)<$tolerance) {
            $ars="5:4";
        } else {
            // really wierd stuff
            $ars=number_format($ar,2);
        }
        return($ars);
    }

    // found on the internets (http://stackoverflow.com/questions/9635968/convert-dot-syntax-like-this-that-other-to-multi-dimensional-array-in-php)
    function assignArrayByPath(&$arr, $path, $value, $separator='.') {
        $keys = explode($separator, $path);

        foreach ($keys as $key) {
            $arr = &$arr[(string)$key];
        }

        $arr = $value;
    }

    // found on the internets (http://jeffreysambells.com/2012/10/25/human-readable-filesize-php)
    function human_filesize($bytes, $decimals = 2) {
        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }

    function parsecamerasxml(){
        $xml=simplexml_load_file(datapath."/cameras.xml");
        $cameras=$xml->xpath("//Camera");

        foreach($cameras as $camera){
            $exifmake=(string)$camera->attributes()->make;
            $exifmodel=(string)$camera->attributes()->model;

            $ID=$xml->xpath("//Camera[@make='$exifmake'][@model='$exifmodel']/ID");
            if($ID){
                $preferedmake=(string)$ID[0]->attributes()->make;
                $preferedmodel=(string)$ID[0]->attributes()->model;
                $data[$exifmake]['make']=$preferedmake;
                $uniquemodel=(string)$ID[0];
                $data[$exifmake][$uniquemodel]['make']=$preferedmake;
                $data[$exifmake][$uniquemodel]['model']=$preferedmodel;
            } else {
                $preferedmake=$exifmake;
                $preferedmodel=$exifmodel;
            }

            $data[$exifmake][$exifmodel]['make']=$preferedmake;
            $data[$exifmake][$exifmodel]['model']=$preferedmodel;

            $aliases=$xml->xpath("//Camera[@make='$exifmake'][@model='$exifmodel']/Aliases/Alias");
            if($aliases){
                foreach($aliases as $alias){
                    $preferedmodel=(string)$alias[0]->attributes()->id;
                    if($preferedmodel!=""){
                        $data[$exifmake][(string)$alias[0]]['make']=$preferedmake;
                        $data[$exifmake][(string)$alias[0]]['model']=$preferedmodel;
                    } else {
                        $data[$exifmake][(string)$alias[0]]['make']=$preferedmake;
                        $data[$exifmake][(string)$alias[0]]['model']=(string)$alias[0];
                    }
                }
            }
        }
        return($data);
    }

// user related functions
    function user_exist($username){
        $dbh = db_init();
        $sth = $dbh->prepare('select count(username) from users where username = :username');
        $sth->execute(array(':username' => $username));
        $result = $sth->fetchColumn();
        return($result);
    }

    function user_add($username,$password) {
        $dbh = db_init();
        $sth = $dbh->prepare('insert into users(username,password)  values(:username,:password)');
        $result = $sth->execute(array(':username' => $username,':password' => password_hash($password,PASSWORD_DEFAULT)));
        return($result);
    }

    function user_delete($username) {
        $dbh = db_init();
        $sth = $dbh->prepare('delete from users where username = :username');
        $result = $sth->execute(array(':username' => $username));
        return($result);
    }

    function user_modify($username,$email,$notify) {
        $dbh = db_init();
        $sth = $dbh->prepare('update users set email=:email,notify=:notify where username=:username');
        $result = $sth->execute(array(':username' => $username, ':email' => $email,':notify' => $notify));
        return($result);
    }

    function user_chpasswd($username,$oldpassword,$newpassword) {
        if(user_validate($username,$oldpassword)){
            $dbh = db_init();
            $sth = $dbh->prepare('update users set password=:password where username=:username');
            $result = $sth->execute(array(':username' => $username,':password' => password_hash($newpassword,PASSWORD_DEFAULT)));
            return($result);
        }
        return(false);
    }

    function user_validate($username,$password) {
        $dbh = db_init();
        $sth = $dbh->prepare('select password from users where username = :username');
        $sth->execute(array(':username' => $username));
        $result = $sth->fetchColumn();
        return(password_verify($password,$result));
    }

    function user_getdata($username) {
        $dbh = db_init();
        $sth = $dbh->prepare('select * from users where username = :username');
        $sth->execute(array(':username' => $username));
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return($result);
    }

//rawfile functions
    function raw_add($tmpfilename,$filename) {
        $dbh = db_init();
        $checksum=hash_file('sha256', $tmpfilename);
        $filesize=filesize($tmpfilename);

        $sth = $dbh->prepare('insert into raws(filename,validated,make,model,remark,checksum,mode,license,filesize,masterset,state)  values(:filename,0,"","","",:checksum,"","CC0",:filesize,0,"created")');
        $result = $sth->execute(array(':filename' => $filename,':checksum' => $checksum,':filesize' => $filesize));
        if (!$result) {
            $error=$sth->errorInfo();
            print_r($error);
            exit(0);
            return(FALSE);
        }
        $sth = $dbh->prepare('select last_insert_id()');
        $sth->execute();
        $id = $sth->fetchColumn();

        $fullpath=datapath."/".hash_id($id)."/".$id;
        if (!mkdir($fullpath,0755,true)){
            echo "fileupload failed (not enough filerights)";
            exit (1);
        };
        if (!move_uploaded_file($tmpfilename, $fullpath."/".$filename)) {
            echo "local move $tmpfilename\n";
            rename($tmpfilename, $fullpath."/".$filename);
        }

        system('exiv2 -Pkt "'.$fullpath.'/'.$filename.'">"'.$fullpath.'/'.$filename.'.exif.txt"');
        system('exiftool -G -s -a -m -u "'.$fullpath.'/'.$filename.'"|grep -vE "^\[(ExifTool|File)\]">"'.$fullpath.'/'.$filename.'.exiftool.txt"');
        system('exiftool -G -s -a -m -u -json "'.$fullpath.'/'.$filename.'">"'.$fullpath.'/'.$filename.'.exiftool.json"');
        //extracting best quality jpeg preview
        system('exiv2 -ep$(exiv2 -pp "'.$fullpath.'/'.$filename.'"|grep jpeg |tail -1|sed "s/Preview \([1-9]\{1\}\).*/\\1/g") "'.$fullpath.'/'.$filename.'"');

        $files=scandir($fullpath);
        if(count(preg_grep("/.*-preview[0-9]\.jpg$/", $files))==0){
          system('darktable-cli '.$fullpath.'/'.$filename." ".$fullpath.'/'.$filename.'-preview1.jpg --width 1000 --height 1000 2>/dev/null 1>/dev/null');
        }

        $data['checksum']=$checksum;
        $data['filesize']=$filesize;
        $data['make']="";
        $data['model']="";
        $data['remark']="";
        $data['mode']="";
        $data['license']="CC0";
        $data['date']=date("Y-m-d");
        raw_modify($id,$data);

        $exifdata=raw_readexif($fullpath."/".$filename,"r");
        if($exifdata) {
            $data=raw_parseexif($exifdata);
            raw_modify($id,$data);
        }
        return($id);
    }

    function raw_delete($id) {
        $data=raw_getdata($id);

        $path=datapath."/".hash_id($id)."/".$id;
        if(is_dir($path)){
            $entries=scandir($path);
            foreach($entries as $entry){
                if(is_file($path."/".$entry)){
                    unlink($path."/".$entry);
                }
            }
            rmdir($path);
        }

        $dbh = db_init();
        $sth = $dbh->prepare('delete from raws where id=:id and masterset=:masterset');
        $result = $sth->execute(array(':id' => $id,':masterset' => 0));
        return($result);
    }

    function raw_modify($id,$data) {
        $dbh = db_init();
        foreach($data as $key => $value){
            $sth = $dbh->prepare('update raws set '.strtolower($key).'=:value where id=:id');
            $result = $sth->execute(array(':id' => $id, ':value' => trim($value)));
            if(!$result) {
                return(FALSE);
            }
        }
        return(TRUE);
    }

    function raw_check($id,$checksum){
        $dbh = db_init();
        $sth = $dbh->prepare('select count(id) from raws where id = :id and checksum=:checksum');
        $sth->execute(array(':id' => $id, ':checksum' => $checksum));
        $result = $sth->fetchColumn();
        return($result);
    }

    function raw_getdata($id) {
        $dbh = db_init();
        $sth = $dbh->prepare('select * from raws where id = :id');
        $sth->execute(array(':id' => $id));
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return($result);
    }

    function raw_getalldata() {
        $dbh = db_init();
        $sth = $dbh->prepare('select * from raws');
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return($result);
    }

    function raw_getlast($limit) {
        $dbh = db_init();
        //limit doesn't work with execute+array
        $sth = $dbh->prepare('select * from raws order by id desc limit '.$limit);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return($result);
    }

    function raw_getnumberofsamples() {
        $dbh = db_init();
        $sth = $dbh->prepare('select count(*) from raws where validated=1');
        $sth->execute();
        $result = $sth->fetchColumn();
        return($result);
    }

    function raw_getnumberofcameras() {
        $dbh = db_init();
        $sth = $dbh->prepare('select  count(distinct(concat(make,model))) from raws where validated=1');
        $sth->execute();
        $result = $sth->fetchColumn();
        return($result);
    }

    function raw_gettotalrepositorysize() {
        $dbh = db_init();
        $sth = $dbh->prepare('select sum(filesize) from raws where validated=1');
        $sth->execute();
        $result = $sth->fetchColumn();
        return($result);
    }

    function raw_readexif($filename) {
        $skip=0;
        
        $exiv2=$filename.".exif.txt";
        $exiftool=$filename.".exiftool.json";
        $exifdata=array();

        if(is_readable($exiv2) and filesize($exiv2) > 0){
            $fp=fopen($exiv2,"r");

            while (!feof($fp)) {
                $buffer=fgets($fp,1024);
                
                if ($skip==0 && preg_match("/([a-zA-Z0-9-_.\/:]+)\s+(.*)/",$buffer,$matches)) {
                    assignArrayByPath($exifdata,$matches[1],$matches[2],".");
                }

                if (strlen($buffer)==1023) {
                    $skip=1;
                } else {
                    $skip=0;
                }
            }
            fclose($fp);
        }
        if(is_readable($exiftool) and filesize($exiftool) > 1000){
            $jsondata=json_decode(file_get_contents($exiftool));
            $exifdata['exiftool']=(array)$jsondata[0];
        }
        return($exifdata);
    }

    function raw_parseexif($exifdata) {
        $data=array();

        if(count($exifdata) >0){
            $data['make'] = $exifdata['Exif']['Image']['Make'] ?? $exifdata['exiftool']['EXIF:Make'] ?? "";
            $data['model'] = $exifdata['Exif']['Image']['Model'] ?? $exifdata['exiftool']['EXIF:Model'] ?? "";

            // bits per sample
            if(isset($exifdata['Exif'])){
                foreach($exifdata['Exif'] as $key => $value){
                    if(isset($value['BitsPerSample']) and is_numeric($value['BitsPerSample'])){
                        $data['bitspersample']=$value['BitsPerSample'] ?? '';
                    }
                }
            }

            $w1=0;
            $h1=0;
            if(isset($exifdata['Exif'])){
                foreach($exifdata['Exif'] as $key => $value){
                    //          | default
                    $w1=max($w1,$value['ImageWidth'] ?? 0);
                    //          | default                | panasonic
                    $h1=max($h1,$value['ImageLength'] ?? $value['ImageHeight'] ?? 0);
                }
            }
            // put width/height in a known order
            $w2=max($w1,$h1);
            $h2=min($w1,$h1);
            if(isset($exifdata['exiftool']['Composite:ImageSize'])){
                $dimensions=explode("x",$exifdata['exiftool']['Composite:ImageSize']);
                $w2=max($w2,max($dimensions[0],$dimensions[1]));
                $h2=max($h2,min($dimensions[0],$dimensions[1]));
            }
            $data['pixels']=round(($w2*$h2)/1000000.0,2);
            $data['aspectratio']=aspectratio($w2,$h2);

            // canon raw settings
            if(preg_match("/^canon/i",$data['make'])){
                if(isset($exifdata['Exif']['CanonCs']['SRAWQuality']) and $exifdata['Exif']['CanonCs']['SRAWQuality']!="n/a"){
                    $data['mode']=$exifdata['Exif']['CanonCs']['SRAWQuality'];
                } elseif (isset($exifdata['Exif']['CanonCs']['Quality'])){
                    $data['mode']=$exifdata['Exif']['CanonCs']['Quality'];
                } else {
                    $data['mode']="";
                }
                if(isset($exifdata['Exif']['Image']['Software']) and preg_match("/^CHDK/",$exifdata['Exif']['Image']['Software'])){
                    $data['mode'].=" ".$exifdata['Exif']['Image']['Software'];
                }
            }

            // nikon raw modes
            if(preg_match("/^nikon/i",$data['make'])){
                $ar="";
                if(isset($exifdata['Exif'])){
                    foreach($exifdata['Exif'] as $key => $value){
                        if(isset($value['NewSubfileType']) and $value['NewSubfileType']=="Primary image"){
                            if($value['Compression']=="Nikon NEF Compressed"){
                                $data['mode']="compressed";
                            } else if ($value['Compression']=="Uncompressed" ){
                                $data['mode']="uncompressed";
                            }
                            $data['aspectratio']=aspectratio($value['ImageWidth'],$value['ImageLength']);
                        }
                    }
                }
                if(isset($exifdata['Exif']['Nikon3']['NEFCompression'])){
                    $data['mode'].=" (".$exifdata['Exif']['Nikon3']['NEFCompression'].")";
                }
            }

            // Panasonic stuff & Leica stuff
            if($data['make'] == '' and isset($exifdata['Exif']['PanasonicRaw']['Make'])){
                $data['make']=$exifdata['Exif']['PanasonicRaw']['Make'];
            }
            if($data['model'] == '' and isset($exifdata['Exif']['PanasonicRaw']['Model'])){
                $data['model']=$exifdata['Exif']['PanasonicRaw']['Model'];
            }
            if(preg_match("/^(panasonic|leica)/i",$data['make'])) {
                if(isset($exifdata['Exif']['PanasonicRaw']['ImageWidth']) and isset($exifdata['Exif']['PanasonicRaw']['ImageHeight'])) {
                    if(isset($exifdata['exiftool']['EXIF:CropTop']) and $exifdata['exiftool']['EXIF:CropTop'] != 0){
                        $w=$exifdata['exiftool']['EXIF:CropRight'] - $exifdata['exiftool']['EXIF:CropLeft'];
                        $h=$exifdata['exiftool']['EXIF:CropBottom'] - $exifdata['exiftool']['EXIF:CropTop'];
                    } else {
                        $w=$exifdata['Exif']['PanasonicRaw']['ImageWidth'];
                        $h=$exifdata['Exif']['PanasonicRaw']['ImageHeight'];
                    }
                    $data['aspectratio']=aspectratio($w,$h);
                }
            }

            // Leica compressions
            if(preg_match("/^leica/i",$data['make'])){
                if(isset($exifdata['Exif']['SubImage1']) && $exifdata['Exif']['SubImage1']['Compression']=="Uncompressed"){
                    $data['mode']="uncompressed";
                } elseif (isset($exifdata['Exif']['SubImage1']) && $exifdata['Exif']['SubImage1']['Compression']=="JPEG" ){
                    $data['mode']="compressed";
                } elseif(isset($data['bitspersample'])){
                    if($data['bitspersample']=="8"){
                        $data['mode']="compressed";
                    } elseif ($data['bitspersample']=="16" ){
                        $data['mode']="uncompressed";
                    }
                } else {
                    $data['mode']="";
                }
            }

            // Sony compressions
            if(preg_match("/^sony/i",$data['make'])){
                if(isset($exifdata['Exif']['SubImage1']['Compression'])){
                    if($exifdata['Exif']['SubImage1']['Compression']=="(32767)"){
                        $data['mode']="compressed";
                    } elseif ($exifdata['Exif']['SubImage1']['Compression']=="Uncompressed" ){
                        $data['mode']="uncompressed";
                    }
                } else if(isset($exifdata['Exif']['Photo']['CompressedBitsPerPixel'])){
                    if($exifdata['Exif']['Photo']['CompressedBitsPerPixel']=="8"){
                        $data['mode']="compressed";
                    } elseif ($exifdata['Exif']['Photo']['CompressedBitsPerPixel']=="16" ){
                        $data['mode']="uncompressed";
                    }
                } else {
                    $data['mode']="";
                }
            }

            // Fuji pixels
            if(preg_match("/^fujifilm/i",$data['make'])){
                if(isset($exifdata['exiftool']['RAF:RawImageFullWidth'])) {
                    $width=$exifdata['exiftool']['RAF:RawImageFullWidth'];
                    $height=$exifdata['exiftool']['RAF:RawImageFullHeight'];
                } elseif (isset($exifdata['Exif']['Photo']['PixelXDimension'])) {
                    $width=$exifdata['Exif']['Photo']['PixelXDimension']*3;
                    $height=$exifdata['Exif']['Photo']['PixelYDimension']*3;
                } else {
                    $width=1;
                    $height=1;
                }
                $data['pixels']=round(($width*$height)/1000000.0,2);
                $data['aspectratio']=aspectratio($width,$height);
                $data['bitspersample']=$exifdata['exiftool']['RAF:BitsPerSample'] ?? '';
                $stripbytecount=$exifdata['exiftool']['RAF:StripByteCounts'] ?? 0;
                if($stripbytecount and $width and $height) {
                  if(((8 * $stripbytecount) / ($width * $height)) < 10) {
                    $data['mode']="compressed";
                  } else {
                    $data['mode']="uncompressed";
                  }
                } else {
                    $data['mode']="";
                }
            }

            // Phase one
            if(preg_match("/^phase one/i",$data['make']) || preg_match("/^leaf/i",$data['make'])){
                if(isset($exifdata['exiftool']['MakerNotes:RawFormat'])){
                    $data['mode']=$exifdata['exiftool']['MakerNotes:RawFormat'];
                }
            }


            // SAMSUNG
            if(preg_match("/^samsung/i",$data['make'])){
                if(isset($exifdata['exiftool']['BitsPerSample'])) {
                    $data['bitspersample']=$exifdata['exiftool']['BitsPerSample'];
                }
            }

            
            // PENTAX 
            if(preg_match("/^pentax/i",$data['make'])){
                if($exifdata['exiftool']['EXIF:Compression'] == "Pentax PEF Compressed") {
                    $data['mode'] = "compressed";
                } else if($exifdata['exiftool']['EXIF:Compression'] == "PackBits" or $exifdata['exiftool']['EXIF:Compression'] == "Uncompressed") {
                    $data['mode'] = "uncompressed";
                }
                
                $data['bitspersample']=$exifdata['exiftool']['BitsPerSample'] ?? $data['bitspersample'];
            }


            //software created raws
            if(isset($exifdata['Exif']['Image']['Software']) and preg_match('/^HDRMerge/',$exifdata['Exif']['Image']['Software'])){
                $data['model']=$data['make']." ".$data['model'];
                $data['make']="HDRMerge";
            }
        }
        return($data);
    }

    function raw_dupecheck($id){
        $data=raw_getdata($id);

        $dbh = db_init();
        $sth = $dbh->prepare('select count(id) from raws where validated=1 and id!=:id
                                                                           and make=:make
                                                                           and model=:model
                                                                           and mode=:mode
                                                                           and aspectratio=:aspectratio
                                                                           and bitspersample=:bitspersample
                                                                           and pixels=:pixels
                                                                           and license=:license');
        $sth->execute(array(':id' => $id,
                            ':make' => $data['make'],
                            ':model' => $data['model'],
                            ':mode' => $data['mode'],
                            ':aspectratio' => $data['aspectratio'],
                            ':bitspersample' => $data['bitspersample'],
                            ':pixels' => $data['pixels'],
                            ':license' => $data['license']
                            ));

        $result = $sth->fetchColumn();
        return($result);
    }

    function raw_stats(){
        $stats['validated']=0;
        $stats['masterset']=0;
        $stats['new']=0;
        $stats['newdupe']=0;
        $stats['created']=0;
        $stats['dupe']=0;
        $stats['all']=0;
        
        $dbh = db_init();
        $sth = $dbh->prepare('select state,count(state) from raws group by state');
        $sth->execute();
        $result=$sth->fetchAll();
        foreach($result as $row) {
            $stats['all'] = $stats['all'] + $row[1];
            $stats[$row[0]] = $row[1];
        }

        $sth = $dbh->prepare('select count(masterset) from raws where masterset=1');
        $sth->execute();
        $result=$sth->fetchColumn();
        $stats['masterset']=$result;
        
        return($stats);
    }

// notification
    function notify($id,$action,$extra="") {
        $data=raw_getdata($id);

        if(isset($_SESSION['username'])){
            $userdata=user_getdata($_SESSION['username']);
        }
        switch($action){
            case "new":
                $subject="[raw.pixls.us] New upload: ".$data['make']." - ".$data['model'];

                $message="New upload\r\n\r\n";
                $message.="Make: ".$data['make']."\r\n";
                $message.="Model: ".$data['model']."\r\n";
                $message.="Mode: ".$data['mode']."\r\n";
                $message.="Filename: ".$data['filename']."\r\n";
                $message.="Remark: ".$data['remark']."\r\n";
                $message.="Admin: ".baseurl."/edit-admin.php?id=$id\r\n";
                $message.="Aspect ratio: ".$data['aspectratio']."\r\n";
                $message.="Bits per sample: ".$data['bitspersample']."\r\n";
                break;
            case "delete":
                $subject="[raw.pixls.us] Delete entry: ".$data['make']." - ".$data['model'];

                $message=$userdata['username']." <".$userdata['email']."> deleted\r\n";
                $message.="Reason: ".$extra."\r\n\r\n";
                $message.="Make: ".$data['make']."\r\n";
                $message.="Model: ".$data['model']."\r\n";
                $message.="Mode: ".$data['mode']."\r\n";
                $message.="License: ".$data['license']."\r\n";
                $message.="Remark: ".$data['remark']."\r\n";
                $message.="Aspect ratio: ".$data['aspectratio']."\r\n";
                $message.="Bits per sample: ".$data['bitspersample']."\r\n";
                break;
            case "modify":
                $subject="[raw.pixls.us] Modify entry: ".$data['make']." - ".$data['model'];

                $message=$userdata['username']." <".$userdata['email']."> modified\r\n";
                $message.="\r\nFrom:\r\n";
                $message.="Make: ".$extra['make']."\r\n";
                $message.="Model: ".$extra['model']."\r\n";
                $message.="Mode: ".$extra['mode']."\r\n";
                $message.="License: ".$extra['license']."\r\n";
                $message.="Validated: ".$extra['validated']."\r\n";
                $message.="Aspect ratio: ".$extra['aspectratio']."\r\n";
                $message.="Bits per sample: ".$extra['bitspersample']."\r\n";
                $message.="Masterset: ".$extra['masterset']."\r\n";
                $message.="Remark: ".$extra['remark']."\r\n";
                $message.="\r\nTo:\r\n";
                $message.="Make: ".$data['make']."\r\n";
                $message.="Model: ".$data['model']."\r\n";
                $message.="Mode: ".$data['mode']."\r\n";
                $message.="License: ".$data['license']."\r\n";
                $message.="Remark: ".$data['remark']."\r\n";
                $message.="Validated: ".$data['validated']."\r\n";
                $message.="Aspect ratio: ".$data['aspectratio']."\r\n";
                $message.="Masterset: ".$data['masterset']."\r\n";
                $message.="Bits per sample: ".$data['bitspersample']."\r\n";
                break;
        }

        $dbh = db_init();
        $sth = $dbh->prepare("select email from users where notify='1'");
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        foreach($result as $email) {
            $headers = ['From' => "rpu@raw.pixls.us",'To' => $email['email'], 'Subject' => $subject , 'Reply-To' => "noreply@raw.pixls.us"];
            $smtp = Mail::factory('sendmail');
            $mail = $smtp->send($email['email'] , $headers, $message);
        }
    }

    function get_raw_pretty_name($raw, &$make, &$model) {
        $make="unknown";
        $model="unknown";
        if($raw['make']!=""){
            $make=$cameradata[$raw['make']][$raw['model']]['make'] ?? $cameradata[$raw['make']]['make'] ?? $raw['make'];
        }
        if($raw['model']!=""){
            $model=$cameradata[$raw['make']][$raw['model']]['model'] ?? $raw['model'];
        }
        $make = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $make);
        $model = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $model);
        return $make."/".$model."/".$raw['filename'];
    }

    // found on http://php.net/manual/en/function.rmdir.php
    function delTree($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }


//gitlfs 
    function writeGitLFSPointer($filename, $raw) {
        $fp=fopen($filename,"w");
        fprintf($fp,"version https://git-lfs.github.com/spec/v1\n",);
        fprintf($fp,"oid sha256:%s\n", $raw['checksum']);
        fprintf($fp,"size %u\n", $raw['filesize']);
        fclose($fp);
    }

    function turnIntoAGitLFSRepo($checkout, $bare, $namespace) {
        $fp=fopen($checkout."/.lfsconfig","w");
        fprintf($fp,"[lfs]\n", );
        fprintf($fp,"\turl = %s/git-lfs.php/$namespace\n", baseurl);
        fclose($fp);

        $fp=fopen($checkout."/.gitattributes","w");
        fprintf($fp,"%s filter=lfs diff=lfs merge=lfs -text\n", "*");
        foreach (scandir($checkout) as $filename) {
            if(is_file($checkout."/".$filename)) {
                fprintf($fp,"%s !filter !diff !merge text\n", $filename);
            }
        }
        fclose($fp);

        $env = array(
            'GIT_AUTHOR_NAME' => GIT_AUTHOR_NAME,
            'GIT_AUTHOR_EMAIL' => GIT_AUTHOR_EMAIL,
            'GIT_AUTHOR_DATE' => timestamp,
            'GIT_COMMITTER_NAME' => GIT_AUTHOR_NAME,
            'GIT_COMMITTER_EMAIL' => GIT_AUTHOR_EMAIL,
            'GIT_COMMITTER_DATE' => timestamp,
        );
        proc_close(proc_open(array('git', 'init', '--quiet', '--initial-branch=master'), [], $pipes, $checkout, $env));
        proc_close(proc_open(array('git', 'add', '.'), [], $pipes, $checkout, $env));
        proc_close(proc_open(array('git', 'commit', '--quiet', '--message=Raw.Pixls.Us public data dump as of '.date('c', timestamp).'', '--no-signoff', '--no-gpg-sign'), [], $pipes, $checkout, $env));
        proc_close(proc_open(array('git', 'clone', '--quiet', '--mirror', $checkout, $bare), [], $pipes, $checkout, $env));
        proc_close(proc_open(array('git', 'repack', '--quiet', '-a', '-d', '-f', '-F'), [], $pipes, $bare, $env));
    }

    // https://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid/15875555#15875555
    function guidv4($data = null) {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    function parseHashsumsFile($fname) {
        define("MAGIC", 64);
        $arr = [];
        foreach (file($fname, FILE_IGNORE_NEW_LINES) as $line) {
            assert(strlen($line) >= MAGIC + 2 + 1);
            $arr[substr($line, 0, MAGIC)] = substr($line, MAGIC + 2);
        }
        return $arr;
    }

    function influxPoints($lines) {
        $content = array_reduce($lines, function($carry, $item) {
            return $carry . $item . "\n";
        });

        $headers = [];
        if(defined("influxtoken")) {
            $headers[] = "Authorization: Token " . influxtoken;
        }
        $headers[] = "Content-Type: application/x-www-form-urlencoded";

        $header = array_reduce($headers, function($carry, $item) {
            return $carry . $item . "\r\n";
        });

        $opts = array('http' => array('method' => 'POST', 'header' => $header, 'content' => $content, 'timeout' => 60));
        $context = stream_context_create($opts);
        $url = influxserver."/write?db=".influxdb;
        file_get_contents($url, false, $context);
    }

    function influxSetSerialize($set) {
        $arr = [];
        foreach($set as $k => $v) {
            $arr[] = $k."=".$v;
        }
        return implode(",", $arr);
    }

    function influxPointSerialize($measurement_str, $tagset, $fieldset) {
        $tags = influxSetSerialize($tagset);
        $fields = influxSetSerialize($fieldset);
        return implode(",", [$measurement_str, $tags]) . " " . $fields;
    }

    function influxPoint($measurement_str, $tagset, $fieldset) {
        influxPoints([influxPointSerialize($measurement_str, $tagset, $fieldset)]);
    }
