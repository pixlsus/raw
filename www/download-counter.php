<?php

include "../config.php";
include "functions.php";

// Do not allow this script to be externally-accessible,
// only via Apache redirects, that are sufficient to defeat
// data exfiltration attempts. Note that if user were to
// pass `REDIRECT-URL` header, it would end up in `HTTP_REDIRECT_URL`
if(!array_key_exists("REDIRECT_URL", $_SERVER) ||
   $_SERVER["REDIRECT_URL"] != $_SERVER["PATH_INFO"]) {
  header("HTTP/1.1 403 FORBIDDEN");
  exit();
}

$file = ".".$_SERVER["PATH_INFO"];
assert(file_exists($file));

list(, $namespace, ) = explode("/", $_SERVER["PATH_INFO"] ?? "", 3);

// git http transport does not support redirects. Handle the download ourselves.
if (in_array($_SERVER["PATH_INFO"],
              [
                "/data.annex.git/info/refs",
                "/data.lfs.git/info/refs",
                "/data-unique.annex.git/info/refs",
                "/data-unique.lfs.git/info/refs",
              ], true)) {
  influxPoint("gitrepo",
              [
                "namespace" => $namespace
              ],
              [
                "dummyfield" => false
              ]);

  header('HTTP/1.1 200 OK');
  header("Last-Modified: ". date('r',filemtime($file)));
  header('Content-Type: ' . mime_content_type($file));
  header('Content-Length: ' . filesize($file));
  readfile($file);
  exit();
}

// Otherwise, let the web server deal with this.
if (in_array($namespace, ["data", "data-unique"], true)) {
  list(, , $filename) = explode("/", $_SERVER["PATH_INFO"], 3);

  $hashsumsfile = parseHashsumsFile($namespace."/filelist.sha256");
  $sha256 = NULL;
  foreach($hashsumsfile as $k => $v) {
    if($v == $filename) { // TOCTOU
      $sha256 = $k;
      break;
    }
  }
} else assert(false);

$session = $_SERVER["HTTP_X_RPU_GIT_LFS_SESSION_ID"] ?? "";

influxPoint("downloads",
            [
              "namespace" => $namespace,
              "filename" => '"'.$filename.'"',
              "filesha256hash" => '"'.$sha256.'"',
            ],
            [
              "filesize" => filesize($file),
              "session" => $session
            ]);

header('HTTP/1.1 301');
header('Location: /download'.$_SERVER["PATH_INFO"]);
exit();
