<?php

    include("../config.php");
    include("functions.php");

    $_SESSION['upload']="";
    if( isset($_FILES['file']) and is_file($_FILES['file']['tmp_name']) and ($_FILES['file']['size'] > 0) and isset($_POST['rights']) and isset($_POST['edited']) and preg_match("/[a-zA-Z0-9-_.]/",$_FILES['file']['name']) and ! preg_match("/php/i",$_FILES['file']['name']) ) {
        $id=raw_add($_FILES['file']['tmp_name'],$_FILES['file']['name']);
        if($id){
            $data=raw_getdata($id);
        }

        // disable submit button if data is missing
        if($data['make']=="" or $data['model']==""){
            $disabled="disabled";
        } else {
            $disabled="";
        }
        $_SESSION['upload']=$data['checksum'];
    }

?>
<!doctype html>
<html lang="en">
    <head>
	<meta charset="utf-8">
	<title>raw.pixls.us</title>
	<link href="css/jquery-ui.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://pixls.us/styles/style.css"/>
    <link rel="stylesheet" type="text/css" href="css/style.css"/>
    </head>
    <body>
		<header>
			<div class="container">
				<a href="https://pixls.us">
					<img id="logo-header" src="https://pixls.us/images/pixls.us-logo-url.svg" alt="PIXLS.US logo">
				</a>
				<div id="about-header">RAW</div>
			</div>
		</header>

        <?php if(isset($data)) : ?>
        <section class="row clearfix">
            <div class='container'>
            <h1>Thank you for submitting!</h1>
                <div class="column full ui-widget">
                    <p>
                        Please take a moment to correct or fill-in any missing fields below,
                        then press 'Update':
                    </p>
                    <form action="modify.php" method="post">
                        <input type="hidden" id="id" name="id" value="<?php echo $data['id']?>" />
                        <input type="hidden" id="checksum" name="checksum" value="<?php echo $data['checksum']?>" />
                        <div>
                            <label for="make" class='fcc-label'>Make</label>
                            <input class="fcc" type="text" id="make" name="make" value="<?php echo $data['make']?>" />
                        </div>
                        <div>
                            <label for="model" class='fcc-label'>Model</label>
                            <input class="fcc" type="text" id="model" name="model" value="<?php echo $data['model']?>" />
                        </div>
                        <div>
                            <label for="mode-dummy" class='fcc-label'>Mode</label>
                            <input class="fcc" type="text" id="mode-dummy" name="mode-dummy" value="<?php echo $data['mode']?>" disabled/>
                        </div>
                        <div>
                            <label for="remark" class='fcc-label'>Comment</label>
                            <input class="fcc" type="text" id="remark" name="remark" value="<?php echo $data['remark']?>" />
                        </div>
                        <input type="submit" name="submit" id="submit" value="Update" <?php echo $disabled?> >
                    </form>
                </div>
            </div>
        </section>

        <?php else : ?>

        <section class="row clearfix">
            <div class='container'>
            <h1>D'oh!</h1>
                <div class="column full ui-widget">
                    <p>
                    The file either already exists, the CC0/modified checkboxes weren't checked,
                    or you forgot to choose a file.
                    Please <a href='//raw.pixls.us'>go back</a> and try again.
                    </p>
                </div>
            </div>
        </section>

        <?php endif; ?>



        <script src="js/jquery.min.js"></script>
        <script src="js/jquery-ui.js"></script>
        <script>
$(document).ready(function() {
    $(".fcc").keyup(function() {
        if ( $("#make").val().length !=0  & $("#model").val().length != 0 ) {
            $("#submit").prop('disabled', false);
        } else {
            $("#submit").prop('disabled', true);
        }
    });
} );
        </script>
    </body>
</html>
