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
    $stats=raw_stats();
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
            <button class="ui-button ui-corner-all" onclick="location.href='edit-user.php'" type="button">Edit Account</button>
        </div>
        </br>
        <div class="ui-widget">
            <button class="ui-button ui-corner-all" style="font-weight: <?= $state=="all"?"bold":"normal"?>" onclick="location.href='admin.php?state=all'" type="button">All (<?=$stats['all']?>)</button>
            <button class="ui-button ui-corner-all" style="font-weight: <?= $state=="validated"?"bold":"normal"?>" onclick="location.href='admin.php?state=validated'" type="button">Validated (<?=$stats['validated']?>)</button>
            <button class="ui-button ui-corner-all" style="font-weight: <?= $state=="masterset"?"bold":"normal"?>" onclick="location.href='admin.php?state=masterset'" type="button">Masterset (<?=$stats['masterset']?>)</button> | 
            <button class="ui-button ui-corner-all" style="font-weight: <?= $state=="new"?"bold":"normal"?>; color: <?= $stats['new']==0? "green":"red"?>" onclick="location.href='admin.php?state=new'" type="button">New (<?=$stats['new']?>)</button>
            <button class="ui-button ui-corner-all" style="font-weight: <?= $state=="newdupe"?"bold":"normal"?>; color: <?= $stats['newdupe']==0? "green":"red"?>" onclick="location.href='admin.php?state=newdupe'" type="button">Dupe (<?=$stats['newdupe']?>)</button>
            <button class="ui-button ui-corner-all" style="font-weight: <?= $state=="created"?"bold":"normal"?>; color: <?= $stats['created']==0? "green":"red"?>" onclick="location.href='admin.php?state=created'" type="button">Incomplete New (<?=$stats['created']?>)</button>
            <button class="ui-button ui-corner-all" style="font-weight: <?= $state=="dupe"?"bold":"normal"?>; color: <?= $stats['dupe']==0? "green":"red"?>" onclick="location.href='admin.php?state=dupe'" type="button">Incomplete Dupes (<?=$stats['dupe']?>)</button>
        </div>
        </br>
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
        <script type="text/javascript" src="js/ellipsis.js"></script>
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
            { targets: 8, render: $.fn.DataTable.render.ellipsis( 16 ) },
        ],
        initComplete: function () {
            this.api()
                .columns()
                .every(function () {
                    let column = this;

                    // Create select element
                    let select = document.createElement('select');
                    select.add(new Option(''));
                    column.footer().replaceChildren(select);

                    // Apply listener for user change in value
                    select.addEventListener('change', function () {
                        column
                            .search(select.value, {exact: true})
                            .draw();
                    });

                    // Add list of options
                    column
                        .data()
                        .unique()
                        .sort()
                        .each(function (d, j) {
                            select.add(new Option(d));
                        });
                });
        }
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
