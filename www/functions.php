<?php
    session_start();

// db shizzle
    function db_init() {
        try {
            $dbh=new pdo(dbdsn,dbuser,dbpw);
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
            exit(0);
        }
        return($dbh);
    }

// otherstuff
    function hash_id($id) {
        return(implode("/",str_split(substr(str_pad($id,hashdepth,"0"),-hashdepth))));
    }

    function aspectratio($width,$height,$tolerance=0.1) {
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
            $ars=$ar;
        }
        return($ars);
    }

    // found on the internets (http://stackoverflow.com/questions/9635968/convert-dot-syntax-like-this-that-other-to-multi-dimensional-array-in-php)
    function assignArrayByPath(&$arr, $path, $value, $separator='.') {
        $keys = explode($separator, $path);

        foreach ($keys as $key) {
            $arr = &$arr[$key];
        }

        $arr = $value;
    }

    // found on the internets (http://jeffreysambells.com/2012/10/25/human-readable-filesize-php)
    function human_filesize($bytes, $decimals = 2) {
        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
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
        $checksum=sha1_file($tmpfilename);

        $sth = $dbh->prepare('insert into raws(filename,validated,checksum)  values(:filename,0,:checksum)');
        $result = $sth->execute(array(':filename' => $filename,':checksum' => $checksum));
        if (!$result) {
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

        system("exiv2 -Pkt  ".$fullpath."/".$filename.">".$fullpath."/".$filename.".exif.txt");
        //extracting best quality jpeg preview
        system("exiv2 -ep$(exiv2 -pp ".$fullpath."/".$filename."|grep jpeg |tail -1|sed \"s/Preview \([1-9]\{1\}\).*/\\1/g\") ".$fullpath."/".$filename);

        $data['checksum']=$checksum;
        $data['make']="";
        $data['model']="";
        $data['remark']="";
        $data['mode']="";
        $data['license']="CC0";

        $exifdata=array();
        $fp=fopen($fullpath."/".$filename.".exif.txt","r");
        while (!feof($fp)) {
            $buffer=fgets($fp);
            if (preg_match("/([a-zA-Z0-9-_.\/:]+)\s+(.*)/",$buffer,$matches)) {
                assignArrayByPath($exifdata,$matches[1],$matches[2],".");
            }
        }
        fclose($fp);

        if(count($exifdata) >0){
            $data['make'] = $exifdata['Exif']['Image']['Make'] ?? "";
            $data['model'] = $exifdata['Exif']['Image']['Model'] ?? "";

            // canon raw settings
            if(preg_match("/^canon/i",$data['make'])){
                $data['mode']=$exifdata['Exif']['CanonCs']['SRAWQuality'] ?? "";
            }

            // nikon raw modes
            if(preg_match("/^nikon/i",$data['make'])){
                $ar="";
                foreach($exifdata['Exif'] as $key => $value){
                    if(isset($value['NewSubfileType']) and $value['NewSubfileType']=="Primary image"){
                        $data['mode']=$value['BitsPerSample']."bit";
                        if($value['Compression']=="Nikon NEF Compressed"){
                            $data['mode'].="-compressed";
                        } else if ($value['Compression']=="Uncompressed" ){
                            $data['mode'].="-uncompressed";
                        }
                        $ar=aspectratio($value['ImageWidth'],$value['ImageLength']);
                    }
                }
                if(isset($exifdata['Exif']['Nikon3']['NEFCompression'])){
                    $data['mode'].=" (".$exifdata['Exif']['Nikon3']['NEFCompression'].")";
                }
                $data['mode'].=" ".$ar;
            }

            // Panasonic aspect ratio
            if(preg_match("/^panasonic/i",$data['make'])) {
                if(isset($exifdata['Exif']['PanasonicRaw']['ImageWidth']) and isset($exifdata['Exif']['PanasonicRaw']['ImageHeight'])) {
                    $data['mode']=aspectratio($exifdata['Exif']['PanasonicRaw']['ImageWidth'],$exifdata['Exif']['PanasonicRaw']['ImageHeight']);
                }
            }

            // Leica compressions
            if(preg_match("/^leica/i",$data['make'])){
                foreach($exifdata['Exif'] as $key => $value){
                    if(isset($value['NewSubfileType']) and $value['NewSubfileType']=="Primary image"){
                        if($value['BitsPerSample']=="8"){
                            $data['mode']="compressed 8bit";
                        } else if ($value['BitsPerSample']=="16" ){
                            $data['mode']="uncompressed 16bit";
                        } else {
                            $data['mode']="";
                        }
                    }
                }
            }
        }

        raw_modify($id,$data);
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
        $sth = $dbh->prepare('delete from raws where id=:id');
        $result = $sth->execute(array(':id' => $id));
        return($result);
    }

    function raw_modify($id,$data) {
        $dbh = db_init();
        foreach($data as $key => $value){
            $sth = $dbh->prepare('update raws set '.strtolower($key).'=:value where id=:id');
            $result = $sth->execute(array(':id' => $id, ':value' => $value));
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
                $message.="Remark: ".$data['remark']."\r\n";
                $message.="Admin: ".baseurl."/edit-admin.php?id=$id\r\n";
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
                break;
        }

        $dbh = db_init();
        $sth = $dbh->prepare("select email from users where notify='1'");
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        foreach($result as $email) {
            mail($email['email'],$subject,$message);
        }
    }
