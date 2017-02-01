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

				<div class="column full ui-widget">
					<h2 id='submit-a-file'>Submit a file
						<span class='orview'>(or <a href="#repo" style="">view repo</a>)</span>
					</h2>
				</div>


				<div class='column half'>

					<p>If you can provide a raw file from a camera we're currently missing, if the sample we have is not under <a href="https://creativecommons.org/share-your-work/public-domain/cc0/" class='cc' style='color: #497bad;' title='Creative Commons Zero - Public Domain Dedication'>co</a>, or if you can provide a more useful photo from a camera model we already support (e.g. a photo of a color target), please upload that file now.</p>


					<div class='form-upload'>

					<h3 id='upload'>Upload</h3>

					<form action="upload.php" method="post" enctype="multipart/form-data">
						<div>
							<input class="fc" type="checkbox" name="rights" id="rights">
							<label for="rights">I declare that I own full rights to this file and I hereby release it under the <a href="https://creativecommons.org/share-your-work/public-domain/cc0/" class='cc' style='color: #497bad;' title='Creative Commons Zero - Public Domain Dedication'>co</a> licence into the public domain.</label>
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
						We are <em>not</em> looking for:
					</p>
					<ul class='small'>
						<li>Series of images with different ISO, aperture, shutter, wb,
						lighting, or different lenses</li>
						<li>DNG files created with Adobe DNG Converter</li>
						<li>Photographs of people, for legal reasons.</li>
					</ul>

				</div>

				<div class='column half'>
					<p>
						Of the following brands we like to have:
					</p>

					<ul>
						<li><b>Panasonic</b>: all the crops (1:1, 4:3, 3:2, 16:9).</li>
						<li><b>Nikon &amp; Sony</b>: all the combinations of compression and bitness settings, that is:
							<ul>
								<li>12bit-compressed</li>
								<li>12bit-uncompressed</li>
								<li>12bit-lossless-compressed</li>
								<li>14bit-compressed</li>
								<li>14bit-uncompressed</li>
								<li>14bit-lossless-compressed</li>
							</ul>
						<li><b>Canon</b>: RAW, mRAW, sRAW.</li>
						<li><b>Canon (CHDK)</b>: Both the old-style CRW, and new DNG.</li>
						<li><b>Leica</b>: Both compressed and uncompressed and all the crops (1:1, 4:3, 3:2, 16:9).</li>
						<li><b>Pentax</b>: Both PEF and DNG.</li>
					</ul>

				</div>
			</div>
		</section>


		<section class="row clearfix">
			<div class="column full ui-widget">

				<h2 id='repo'>Repository</h2>

				<table id="repository" class="display" cellspacing="0" width="100%">
					<thead>
						<tr><th>Make</th><th>Model</th><th>Mode</th><th>Pixls</th><th>Remark</th><th>License</th><th>Date</th><th>Raw</th><th>Exif</th></tr>
					</thead>
					<tfoot>
						<tr><th>Make</th><th>Model</th><th>Mode</th><th>Pixls</th><th>Remark</th><th>License</th><th>Date</th><th>Raw</th><th>Exif</th></tr>
					</tfoot>
				</table>

			</div>
        <a href="getfile.php/0/archive/raw_pixls_us_archive.zip">Full archive as zip</a> - <a href="/data/">Browseable directory</a>
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
        "ajax": 'json/getrepository.php',
        "aoColumns": [
            null,
            null,
            null,
            null,
            null,
            null,
            null,
			{ 'className': "column-rawfile" ,"bSearchable": false },
            { "bSearchable": false }
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
    var u="//piwik.kees.nl/";
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', '15']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
})();
        </script>
        <noscript><p><img src="//piwik.kees.nl/piwik.php?idsite=15&rec=1" style="border:0;" alt="" /></p></noscript>
        <!-- End Piwik Code -->

    </body>
</html>