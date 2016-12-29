<?php
    include("../config.php");
    include("functions.php");

    $referer=$_SESSION['referer'] ?? '';
    $username=$_POST['username'] ?? '';
    $password=$_POST['password'] ?? '';
    
    if($password!="" and $username!="" and $referer!="") {
        if(user_validate($username,$password)) {
            $_SESSION['loggedin']="TRUE";
            $_SESSION['username']=$username;
            header("Location: ".baseurl.$_SESSION['referer']);
            exit(0);
        }
    }
?>
<!doctype html>
<html lang="en">
    <head>
	<meta charset="utf-8">
	<title>raw.dtsyle.net</title>
	<link href="css/jquery-ui.css" rel="stylesheet">
    </head>
    <body>
        <div class="ui-widget">
	    <form action="login.php" method="post">
                <div>
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" />
                </div>
                <div>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" />
                </div>
                <input type="submit" name="submit" id="submit" value="Login"  >
            </form>
        </div>
        <script src="js/jquery.min.js"></script>
        <script src="js/jquery-ui.js"></script>
    </body>
</html>
