<?php
    include("../config.php");
    include("functions.php");
    if(!isset($_SESSION['loggedin'])){
        $_SESSION['referer']=$_SERVER['REQUEST_URI'];
        header("Location: ".baseurl."/login.php");
        exit();
    }
    $state="all";
    if(isset($_GET['state'])) {
        $state=$_GET['state'];
    }

?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
	<title>raw.pixls.us</title>
	<link href="css/jquery-ui.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="css/datatables.min.css"/>
    </head>
    <body>
        <h1>Welcome to raw.pixls.us Admin</h1>
        <div class="ui-widget">
            <a href="edit-user.php">Edit Account</a>
        </div>
        <div class="ui-widget">
            <button onclick="location.href='admin.php?state=all'" type="button">All</button>
            <button onclick="location.href='admin.php?state=validated'" type="button">Validated</button>
            <button onclick="location.href='admin.php?state=new'" type="button">New</button>
            <button onclick="location.href='admin.php?state=created'" type="button">Incomplete New</button>
            <button onclick="location.href='admin.php?state=dupe'" type="button">Incomplete Dupes</button>
            <button onclick="location.href='admin.php?state=masterset'" type="button">Masterset</button>
        </div>
        <div class="ui-widget">
            <table id="repository" class="display" cellspacing="0" width="100%">
                <thead>
                    <tr><th>Status</th><th>Make</th><th>Model</th><th>Mode</th><th>AR</th><th>BPS</th><th>Remark</th><th>License</th><th>Checksum (sha256)</th><th>Size</th><th>Pixels</th><th>Date</th><th>Raw</th><th>Exif</th><th>Edit</th></tr>
                </thead>
                <tfoot>
                    <tr><th>Status</th><th>Make</th><th>Model</th><th>Mode</th><th>AR</th><th>BPS</th><th>Remark</th><th>License</th><th>Checksum (sha256)</th><th>Size</th><th>Pixels</th><th>Date</th><th>Raw</th><th>Exif</th><th>Edit</th></tr>
                </tfoot>
            </table>
        </div>

        <script type="text/javascript" src="js/jquery.min.js"></script>
        <script type="text/javascript" src="js/jquery-ui.js"></script>
        <script type="text/javascript" src="js/datatables.min.js"></script>
        <script type="text/javascript" src="js/file-size.js"></script>
        <script>
$(document).ready(function() {
    $('#repository').DataTable( {
        "ajax": 'json/getrepository-admin.php?state=<?php print $state ?>',
        "pageLength": 100,
        "aoColumns": [
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null
		],
        "columnDefs": [
            { "type": "file-size", targets: 9 },
        ]

    } );
    $(".fc").click(function() {
        if ( $("#rights").is(':checked') & $("#edited").is(':checked') ) {
            $("#submit").prop('disabled', false);
        } else {
            $("#submit").prop('disabled', true);
        }
    });
} );
        </script>
    </body>
</html>
