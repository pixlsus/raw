<?php
    include("../config.php");
    include("functions.php");
    
    if(!isset($_SESSION['loggedin'])){
        $_SESSION['referer']=$_SERVER['REQUEST_URI'];
        header("Location: ".baseurl."/login.php");
        exit();
    }
    
    $data=user_getdata($_SESSION['username']);

    if($data['notify']=="1") {
        $notify="checked";
    } else {
        $notify="";
    }
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
	<title>raw.pixls.us</title>
	<link href="css/jquery-ui.css" rel="stylesheet">
    </head>
    <body>
        <div class="ui-widget">
	    <form action="modify-user.php" method="post">
                <div>
                    <label for="password">Current password:</label>
                    <input type="password" id="password" name="password" />
                </div>
                <div>
                    <label for="newpassword1">New password:</label>
                    <input type="password" id="newpassword1" name="newpassword1" />
                </div>
                <div>
                    <label for="newpassword2">New password:</label>
                    <input type="password" id="newpassword2" name="newpassword2" />
                </div>
                <div>
                    <label for="email">Email:</label>
                    <input type="text" id="email" name="email" value="<?php echo $data['email']?>" />
                </div>
	        <div>
	            <label for="notify">Notify:</label>
                    <input type="checkbox" name="notify" id="notify" <?php echo $notify?>><br>
                </div>
                <input type="submit" name="submit" id="submit" value="Update" >
            </form>
        </div>
        <script src="js/jquery.min.js"></script>
        <script src="js/jquery-ui.js"></script>
    </body>
</html>
