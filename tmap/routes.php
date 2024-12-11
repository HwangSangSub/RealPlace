<?php
//http://api2.sktelecom.com/tmap/routes?version=1&tollgateFareOption=2&endX=128.62358719&endY=35.89800895&startX=128.6410154444359&startY=35.91915595507744&appKey=ba988557-ba1c-4617-baa6-b6668f1ce2a7
//http://places.gachita.co.kr/tmap/routes.php?endX=128.62358719&endY=35.89800895&startX=128.6410154444359&startY=35.91915595507744&viaX=128.6373592362496&viaY=35.89886856653733
function debug($v) {
  echo "<pre>";
  print_r($v);
  echo "</pre>";
}

$endX = $_GET["endX"];
$endY = $_GET["endY"];
$startX = $_GET["startX"];
$startY = $_GET["startY"];
$viaX = $_GET["viaX"];
$viaY = $_GET["viaY"];


if( $viaX != "" && $viaY != "") {
  $md = md5($startX."_".$startY."_".$endX."_".$endY."_".$viaX."_".$viaY);

  $local_file = "/hd/webFolder/places/tmap/cache/".$md;
} else {
  $md = md5($startX."_".$startY."_".$endX."_".$endY);
  $local_file = "/hd/webFolder/places/tmap/cache/".$md;
}



if ($viaX != "" && $viaY != "" ){
    $remote_file = "http://api2.sktelecom.com/tmap/routes?version=1&tollgateFareOption=2&endX=".$endX."&endY=".$endY."&startX=".$startX."&startY=".$startY."&appKey=ba988557-ba1c-4617-baa6-b6668f1ce2a7&passList=".$viaX.",".$viaY;
}else {
  $remote_file = "http://api2.sktelecom.com/tmap/routes?version=1&tollgateFareOption=2&endX=".$endX."&endY=".$endY."&startX=".$startX."&startY=".$startY."&appKey=ba988557-ba1c-4617-baa6-b6668f1ce2a7";
}
// http://api2.sktelecom.com/tmap/routes?version=1&tollgateFareOption=2&endX=128.62358719&endY=35.89800895&startX=128.6410154444359&startY=35.91915595507744&appKey=ba988557-ba1c-4617-baa6-b6668f1ce2a7&passList=128.6373592362496,35.89886856653733
// http://api2.sktelecom.com/tmap/routes?version=1&tollgateFareOption=2&endX=35.89800895&endY=35.89800895&startX=128.640569&startY=35.919345&appKey=ba988557-ba1c-4617-baa6-b6668f1ce2a7&passList=128.63769702,35.89959236
// debug($remote_file);

if (file_exists($local_file)) {
  $handle = fopen($local_file, "rb");
  $contents = fread($handle, filesize($local_file));
  fclose($handle);
}else {
  $ch = curl_init($remote_file);
  curl_setopt($ch, CURLOPT_TIMEOUT, 50);
  curl_setopt($ch, CURLOPT_FILE, $fp);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_ENCODING, "");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $contents = curl_exec($ch);
  curl_close($ch);

  $fp = fopen ($local_file, 'w');
  fwrite($fp, $contents);
  fclose($fp);
}
echo $contents;
?>
