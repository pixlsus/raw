<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>raw.pixls.us</title>
        <link rel="stylesheet" type="text/css" href="css/jquery-ui.css" >
        <link rel="stylesheet" type="text/css" href="css/datatables.min.css"/>
        <link rel="stylesheet" type="text/css" href="https://pixls.us/styles/style.css"/>
        <link rel="stylesheet" type="text/css" href="css/style.css"/>
        <link rel="stylesheet" type="text/css" href="css/cc-icons.min.css"/>
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



		<section class="row clearfix">
			<div class="container">
<?php
    $set="all";
    if(isset($_SERVER['QUERY_STRING'])) switch($_SERVER['QUERY_STRING']) {
    case "noncc0":
        $set="noncc0";
        break;
    case "missing":
        $set="missing";
        break;
?>
            ?>
<?php   case "unwanted": ?>
				<div class="column full ui-widget">
                    <h2>Oops?  Small problem.</h2>
                    <p>D'oh! You tried to upload an unwanted file. (jpg, png, or archive)<br/>
                    Please note, we're only looking for raw files here (no archives).</p>
				</div>
<?php     break; ?>
<?php   case "thankyou": ?>
				<div class="column full ui-widget">
					<h2>Thank you for your submission!</h2>
                    <p>Thank you for helping us to improve support for more raw files!</p>
<p>
<b>NOTE</b>: If you uploaded this raw file because you would like support for it added to your open source program, now is the time to open an issue in your program's issue tracker.<br/>
<b>WARNING</b>: If you <i>don't</i> report the issue, nothing will happen. Just uploading a raw is <b>not</b> sufficient to get the issue fixed.
<ul>
  <li><a href="https://github.com/darktable-org/darktable/issues/new/choose">darktable</p></li>
  <li><a href="https://github.com/Beep6581/RawTherapee/issues/new">RawTherapee</a></li>
</ul>
</p>                    
				</div>
<?php     break; ?>
<?php } ?>
				<div class="column full ui-widget">
					<h2 id='submit-a-file'>Submit a file
						<span class='orview'>(or <a href="#repo" style="">view repo</a>)</span>
					</h2>
				</div>


				<div class='column half'>
					<p>If you can provide a raw file from a camera we're currently <a href="?missing#repo">missing</a>, if the sample we have is <a href="?noncc0#repo">not under <span class='cc' style='color: #497bad;'>co</span></a>, or if you can provide a more useful photo from a camera model we already support (e.g. a photo of a color target), please upload that file now.</p>


					<div class='form-upload'>

					<h3 id='upload'>Upload</h3>

					<form action="upload.php" method="post" enctype="multipart/form-data">
						<div>
							<input class="fc" type="checkbox" name="rights" id="rights">
							<label for="rights">I declare that I own full rights to this file and I hereby release it under the <a href="https://creativecommons.org/share-your-work/public-domain/cc0/" class='cc' style='color: #497bad;' title='Creative Commons Zero - Public Domain Dedication'>co</a> license into the public domain.</label>
						</div>
						<div>
							<input class="fc" type="checkbox" name="edited" id="edited">
							<label for="edited"> The file is manually copied from card/camera, without using any software like Nikon Transfer, and <em>hasn't been modified in any way</em>.</label>
						</div>
						<input type="file" name="file" id="file"><br>
						<input type="submit" name="submit" id="submit" value="Upload" disabled>
					</form>

					</div>

					<p class='small'>
						We are looking for shots that are:
					</p>
					<ul class='small'>
						<li>Lens mounted on the camera, cap off</li>
						<li>Image in focus and properly exposed</li>
						<li>Landscape orientation</li>
					</ul>

					<p class='small'>
						We are <b><em>NOT</em></b> looking for:
					</p>
					<ul class='small'>
						<li>Series of images with different ISO, aperture, shutter, wb,
						lighting, or different lenses</li>
						<li>DNG files created with Adobe DNG Converter</li>
						<li>Photographs of people, for legal reasons.</li>
					</ul>
<?php if($set!="noncc0") { ?>
                    <p class='small'>
                        <a href="?noncc0#repo">View</a> only the non-<a href="https://creativecommons.org/share-your-work/public-domain/cc0/" class='cc' style='color: #497bad;' title='Creative Commons Zero - Public Domain Dedication'>co</a> samples.
                    </p>
<?php } ?>
<?php if($set!="missing") { ?>
                    <p class='small'>
                        <a href="?missing#repo">View</a> all the cameras with no samples at all.
                    </p>
<?php } ?>
<?php if($set!="all") { ?>
                    <p class='small'>
                        If your camera is listed here, <i>please</i> contribute the full sample set!
                    </p>
                    <p class='small'>
                        Go back to the <a href="?#repo">full repository</a>.
                    </p>
<?php } ?>
				</div>

				<div class='column half'>
					<p>
						Of the following brands we like to have:
					</p>

					<ul>
						<li><b>Panasonic</b>: all the crops (1:1, 4:3, 3:2, 16:9).</li>
						<li><b>Nikon &amp; Sony</b>: all the combinations of compression and bitness settings, plus all the raw sizes, that is:
							<ul>
								<li>12bit-compressed</li>
								<li>12bit-uncompressed</li>
								<li>12bit-lossless-compressed</li>
								<li>14bit-compressed</li>
								<li>14bit-uncompressed</li>
								<li>14bit-lossless-compressed</li>
								<li>small NEF</li>
								<li>medium NEF</li>
							</ul>
						<li><b>Sony</b>: Both the uncompressed RAW and compressed RAW.</li>
						<li><b>Sony</b>: For the full-frame cameras, both the normal raw, and the APS-C crop.</li>
						<li><b>Canon (current CR2 cameras)</b>: RAW, mRAW, sRAW.</li>
						<li><b>Canon (future CR3 cameras)</b>: RAW, C-RAW.</li>
						<li><b>Canon (CHDK)</b>: Both the old-style CRW, and new DNG.</li>
						<li><b>Leica</b>: Both compressed and uncompressed and all the crops (1:1, 4:3, 3:2, 16:9).</li>
						<li><b>Pentax</b>: Both PEF and DNG.</li>
						<li><b>Phase One A/S</b>, <b>Leaf</b> (IIQ): IIQ-L, IIQ-S.</li>
                        <li><b>Fujifilm</b>: Both the old uncompressed RAF and new compressed RAF (if supported).</li>
					</ul>

                    <p>If your camera model can produce more than one type of output
                    please consider uploading the entire set.</p>

				</div>
			</div>
		</section>


		<section class="row clearfix">
			<div class="column full ui-widget">

				<h2 id='repo'>Repository</h2>
<?php if($set=="noncc0") { ?>
            <h3>Only the cameras with non-<a href="https://creativecommons.org/share-your-work/public-domain/cc0/" class='cc' style='color: #497bad;' title='Creative Commons Zero - Public Domain Dedication'>co</a> samples are listed here.</h3>
<?php } else if($set=="missing") { ?>
            <h3>Only the cameras with no samples at all are listed here.</h3>
<?php } ?>
				<table id="repository" class="display" cellspacing="0" width="100%">
					<thead>
						<tr><th>Make</th><th>Model</th>
<?php if($set=="all") { ?>
            <th>Mode</th><th>Pixls</th><th>Remark</th><th>License</th><th>Date</th><th>Raw</th><th>Exif</th>
<?php } ?>
            </tr>
					</thead>
					<tfoot>
						<tr><th>Make</th><th>Model</th>
<?php if($set=="all") { ?>
            <th>Mode</th><th>Pixls</th><th>Remark</th><th>License</th><th>Date</th><th>Raw</th><th>Exif</th>
<?php } ?>
            </tr>
          </tfoot>
				</table>

			</div>
        Full mirror available via rsync or <a href="/data/">https</a>.
        Use rsync -avL rsync://raw.pixls.us/data/ raw-pixls-us-data/ to make a full mirror.
    		</section>


		<hr/>

		<section class='row clearfix'>
			<div class='container'>
				<div class='column full'>
					<h3>Donators</h3>
					<p class='small' style='max-width: initial;'>
						The following persons have given raw files on rawsamples.ch (unsorted order):
					</p>

					<p class='small' style='max-width: initial;'>
						Ismael González, Martin Bloss, Reinhard Fetzer, Jens Duttke, Marco Rutz, Rainer Kriewald, Sergej Medvedev, Hans Schrotthofer, Marc Keune, Michi, Wolfram Soens, Ralf Geßner, Wolfgang Nickolay, Wolfgang Peisl, Andreas Pla., Oskar Teichmann, Martin Egger, Markus Schlieper, Peter Carsten, Uwe Mochel, Emil Eschenbach, Francis Willems, Ivaylo Iordanov, Gary Bainbridge, Ludger Jöster, Malcolm Barron, Andreas Norén, Michael Adams, David List, Urs Dünner, Dieter Bethke, Surinder Ram, Alexander Konopka, Udi Fuchs, Hans-Dieter Poppe, Bob Horton, Cyril Brunner, Niels Kristian Bech Jensen, Gerhard Hagen, Benjamin Adler, Jan Borgers, Frank Homann, Dirk Volkmann, Siegfried Henning, John Lightner, Gordon Goodsman, Yves Boyadjian, Paolo Massei, Elia Vecellio, Joerg Hoevel, Peter Gößweiner, Mike Newman, Thomas Lamprecht, Bernard Moschkon, Andrik Sieberichs, Dave Nicholson, Phil Harvey, Elia Vecellio, Suriya Matsuda, Matt Sephton, Tim McCormack, Zach Stuart, Horst Wittenburg, Dr. Falk Langhammer, Benjamin Derge, André Gärtner, Johannes Waschke, Anwar El Bizanti, Tim Beeck, Scott Picton, Adam Bryant, Heiko Kaufhold, Alexey Zilber, Andreas Isenegger, Radek Niec, Jean Glasser, Mats Karlsson, Willi Müller, Simon Schmitz, Till Grigat, Alexander Konopka, Alistair Jackson, Adrien Béraud, Aron Eisenpress, George Stiber, Robert Jackson, Phil Harvey, Sergej Medvedev, Marcel Cuculici, Chi Zhang, Fernando Prado, Prof. SAI GIRIDHAR KAMATH, Anders Torger, Herik Aiolfi, Marcel Wagner, Razil Shaikh, Haiyan Qu, James Wyper and many others.
					</p>
                    <hr>
                    <p>
                    raw.pixls.us is used by <a href="https://www.darktable.org/">darktable</a> for regression testing of <a href="https://github.com/darktable-org/rawspeed">rawspeed</a> and by <a href="http://rawtherapee.com">RawTherapee</a>.  It is available for any projects that need access to a library of raw files.
                    </p>
				</div>
			</div>
		</section>

        <script src="js/jquery.min.js"></script>
        <!--script src="js/jquery-ui.js"></script-->
        <script type="text/javascript" src="js/datatables.min.js"></script>
        <script>
$(document).ready(function() {
    $('#repository').DataTable( {
		"responsive": true,
        "ajax": 'json/getrepository<?php if($set=="missing") echo "-missing"; ?>.php?set=<?php echo $set; ?>',
        "aoColumns": [
            null,
            null
<?php if($set=="all") { ?>
            , null,
            null,
            null,
            null,
            null,
			{ 'className': "column-rawfile" ,"bSearchable": false },
            { "bSearchable": false }
<?php } ?>
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

        <!-- Piwik -->
        <script type="text/javascript">
var _paq = _paq || [];
// tracker methods like "setCustomDimension" should be called before "trackPageView"
_paq.push(['trackPageView']);
_paq.push(['enableLinkTracking']);
(function() {
    var u="//piwik.pixls.us/";
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', '15']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
})();
        </script>
        <noscript><p><img src="//piwik.pixls.us/piwik.php?idsite=15&rec=1" style="border:0;" alt="" /></p></noscript>
        <!-- End Piwik Code -->

    </body>
</html>
