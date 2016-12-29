<?php 
  header("Content-type: application/rss+xml");
?>
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>raw.dtstyle.net</title>
    <description>raw.dtstyle.net repository feed</description>
    <link>http://raw.dtstyle.net</link>
    <atom:link href="http://raw.dtstyle.net/json/rss.php" rel="self" type="application/rss+xml" />
<?php
    include_once "../../config.php";
    include_once "../functions.php";

    $raws=raw_getlast(20);
    $i=0;
    foreach($raws as $raw){
        echo "    <item>\n";
        $rawpath=datapath."/".hash_id($raw['id'])."/".$raw['id'];
        $stat=stat($rawpath."/".$raw['filename']);
        echo "      <title>".$raw['make']." ".$raw['model'].(($raw['mode']!="")?" - ".$raw['mode']:"")."</title>\n";
        echo "      <description>".$raw['make']." ".$raw['model'].(($raw['mode']!="")?" - ".$raw['mode']:"")."</description>\n";
        echo "      <link>https://raw.dtstyle.net/data/".hash_id($raw['id'])."/".$raw['id']."/".$raw['filename']."</link>\n";
        echo "      <guid>https://raw.dtstyle.net/data/".hash_id($raw['id'])."/".$raw['id']."/".$raw['filename']."</guid>\n";
        
        echo "      <pubDate>".date('r',$stat['mtime'])."</pubdate>\n";
        echo "    </item>\n";
    }    
?>
  </channel>
</rss>
