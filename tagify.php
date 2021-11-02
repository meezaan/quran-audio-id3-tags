<?php

require_once('vendor/autoload.php');

use Stormiix\EyeD3\EyeD3;
use AlQuranCloud\ApiClient\Client;

$input = realpath(__DIR__) . '/files/';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($input));
$routes = array_keys(array_filter(iterator_to_array($iterator), function($file) {
    return $file->isFile();
}));

$aq = new Client();

$count = 0;
foreach ($routes as $route) {
  $count++;
  if (strpos($route, '.mp3') !== false) {
    echo "Reading $route ..."; 

    $parts = explode('/', $route);
    $totalParts = count($parts);
    
    // Extract data
    $ayahNo = intval($parts[$totalParts - 1]);
    $edition = $parts[$totalParts - 2];
    $bitrate = $parts[$totalParts - 3];

    echo "Querying AlQuran API...";

    $aqResponse = $aq->ayah($ayahNo, $edition);
    $artist = $aqResponse->data->edition->englishName;
    $title = $aqResponse->data->surah->englishName .', Ayah ' . $aqResponse->data->numberInSurah . ' (' . $aqResponse->data->surah->number . ':'
    . $aqResponse->data->numberInSurah . ')';

    $eyed3 = new EyeD3($route, true);
    $tags = $eyed3->readMeta();

    if (strpos($tags['Comment'], 'Islamic Network CDN') !== false) {
      $comment = $tags['Comment'];
    } else {
      $comment = "Served by Islamic Network CDN - " . $tags['Comment'];
    }
    var_dump($comment);

    $meta = [
      "artist" => $artist,
      "title" => $title,
      "album" => "The Holy Qur'an",
      "comment" => $comment
    ];
    // Update the mp3 file with the new meta tags
    $eyed3->updateMeta($meta);
    echo "Updated tags!\n";
    $eyed3 = '';
  }

  if ($count === 5) {
    sleep(1);
    $count = 0;
  }

}
