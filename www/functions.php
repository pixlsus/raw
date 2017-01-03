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

        $bitspersample="";
        $compression="";
        $nefcompression="";

        $fp=fopen($fullpath."/".$filename.".exif.txt","r");
        while (!feof($fp)) {
            $buffer=fgets($fp);
            if(preg_match("/^Exif.Image.Make\s/",$buffer)){
                $data['make']=trim(substr($buffer,46));
            }
            if(preg_match("/^Exif.Image.Model/",$buffer)){
                $data['model']=trim(substr($buffer,46));
            }

            // canon raw settings
            if(preg_match("/^Exif.CanonCs.SRAWQuality/",$buffer)){
                $sraw=trim(substr($buffer,46));
            }

            // nikon raw modes, also used by leica
            if(preg_match("/^Exif.*.BitsPerSample.*[0-9]{1,2}$/",$buffer)){
                $bitspersample=trim(substr($buffer,46));
            }
            if(preg_match("/^Exif.Sub.*\.Compression/",$buffer)){
                $tmp=trim(substr($buffer,46));
                if(!preg_match("/JPEG/",$tmp)){
                    $compression=$tmp;
                }
            }
            if(preg_match("/^Exif.*.NEFCompression/",$buffer)){
                $nefcompression=trim(substr($buffer,46));
            }

            // panasonic modes
            if(preg_match("/^Exif.PanasonicRaw.ImageHeight/",$buffer)){
                $ph=trim(substr($buffer,46));
            }
            if(preg_match("/^Exif.PanasonicRaw.ImageWidth/",$buffer)){
                $pw=trim(substr($buffer,46));
            }
        }
        fclose($fp);

        // Canon rawmodes
        if(isset($sraw) and $sraw!="n/a" ){
            $data['mode']=$sraw;
        }

        // Nikon compression modes
        if(preg_match("/^nikon/i",$data['make'])){
            $data['mode']=$bitspersample."bit";
            if($compression=="Nikon NEF Compressed"){
                $data['mode'].="-compressed";
            } else if ($compression=="Uncompressed" ){
                $data['mode'].="-uncompressed";
            }
            if($nefcompression!=""){
                $data['mode'].=" ($nefcompression)";
            }
        }

        // Leica compressions
        if(preg_match("/^leica/i",$data['make'])){
            if($bitspersample=="8"){
                $data['mode']="compressed 8bit";
            } else if ($bitspersample=="16" ){
                $data['mode']="uncompressed 16bit";
            } else {
                $data['mode']="";
            }
        }

        // Panasonic aspect ratio
        if(preg_match("/^panasonic/i",$data['make'])) {
            if(isset($ph) and isset($pw)) {
                $tol=0.1;
                $ar=$pw/$ph;
                if($ar<1) {
                    $ar=1/$ar;
                }

                if(abs($ar/(4/3)-1.0)<$tol) {
                    $ars="4:3";
                } elseif (abs($ar/(16/9)-1.0)<$tol) {
                    $ars="16:9";
                } elseif (abs($ar/(3/2)-1.0)<$tol) {
                    $ars="3:2";
                } elseif (abs($ar/(1/1)-1.0)<$tol) {
                    $ars="1:1";
                } else {
                    // really wierd stuff
                    $ars=$ar;
                }
                $data['mode']=$ars;
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

        if($_SESSION['username']){
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
