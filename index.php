<?php
require 'flight/Flight.php';
require 'phpedb.php';
require 'netphp.php';

// date_default_timezone_set('Asia/***');
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);
Flight::set('flight.log_errors', true);

$appModalFields="title,icon,categoryID,packageName,versionName";
$appSingleFields="*";
$categoryFields="id,slug,isGame";//,name".strtoupper($lang);

$db=new database();
$db->connect("localhost", "root-android", '*****');
$db->db="android_apk_site";

Flight::route('/(@lang)', function($lang){
  global $db;
  global $categoryFields;
  global $appModalFields;
  $data=[];
  if($lang === null) {
    $lang="en";
  }
  $data["categories"]=$db->selects("category", ["name".strtoupper($lang)=>["!=", "and", ""]], "ORDER BY RAND() LIMIT 16", $categoryFields);
  foreach($data["categories"] as $i=>$category) {
    $data["categories"][$i]["applications"]=$db->selects("application", ["lang"=>$lang,"categoryID"=>$category["id"]], "ORDER BY `id` DESC LIMIT 12", $appModalFields);
  }
  Flight::json([
    'status'=>'success',
    'type'=>'main',
    'lang'=>$lang,
    'result'=>$data,
  ]);
});

Flight::route('/@lang/search/@query/', function($lang, $query){
  global $db;
  global $appModalFields;
  $data["applications"]=$db->selectsRaw("select ".$appModalFields." from `android_apk_site`.`application` WHERE 
  `lang` = '".$lang."' AND
  (
  `title` LIKE '%".$query."%' OR
  `packageName` LIKE '%".$query."%' OR
  `description` LIKE '%".$query."%'
  ) LIMIT 50;");
  foreach($data["applications"] as $i=>$row) {
    $cat=$db->select("category", ["id"=>$row["categoryID"]], "", "slug,isGame");
    $data["applications"][$i]["categorySlug"]=$cat["slug"];
    $data["applications"][$i]["isGame"]=$cat["isGame"];
    unset($data["applications"][$i]["categoryID"]);
  }
  Flight::json([
    'status'=>'success',
    'type'=>'search',
    'lang'=>$lang,
    'query'=>$query,
    'result'=>$data,
  ]);
});

Flight::route('/@lang/@categorySlug/', function($lang, $categorySlug){
  global $db;
  global $categoryFields;
  global $appModalFields;
  $data=[];
  if($categorySlug === "game" || $categorySlug === "application") {
    $isGame=0;
    if($categorySlug === "game") {
      $isGame=1;
    }
    $data["categories"]=$db->selects("category", ["isGame"=>$isGame, "name".strtoupper($lang)=>["!=", "and", ""]], "ORDER BY RAND() LIMIT 16", $categoryFields.",name".strtoupper($lang));
    foreach($data["categories"] as $i=>$category) {
      $data["categories"][$i]["name"]=$category["name".strtoupper($lang)];
      unset($data["categories"][$i]["id"]);
      unset($data["categories"][$i]["slug"]);
      unset($data["categories"][$i]["name".strtoupper($lang)]);
      $data["categories"][$i]["applications"]=$db->selects("application", ["lang"=>$lang,"categoryID"=>$category["id"]], "ORDER BY `id` DESC LIMIT 12", $appModalFields);
      foreach($data["categories"][$i]["applications"] as $j=>$app) {
        $data["categories"][$i]["applications"][$j]["categorySlug"]=$category["slug"];
        $data["categories"][$i]["applications"][$j]["isGame"]=$isGame;
        unset($data["categories"][$i]["applications"][$j]["categoryID"]);
      }
    }
    Flight::json([
      'status'=>'success',
      'type'=>'main',
      'lang'=>$lang,
      'result'=>$data,
    ]);
    return;
  }
  $data["category"]=$db->select("category", ["slug"=>$categorySlug], "", $categoryFields.",name".strtoupper($lang));
  $data["applications"]=[];
  if(isset($data["category"]["id"])) {
    $data["applications"]=$db->selects("application", ["lang"=>$lang,"categoryID"=>$data["category"]["id"]], "ORDER BY `id` DESC LIMIT 256", $appModalFields);
  }
  Flight::json([
    'status'=>'success',
    'type'=>'category',
    'lang'=>$lang,
    'categorySlug'=>$categorySlug,
    'result'=>$data,
  ]);
});

Flight::route('/@lang/download/@applicationSlug', function($lang, $applicationSlug){
  global $db;
  $data=[];
  $appID=findAppBySlug($applicationSlug);
  $app=$db->select("application", ["id"=>$appID], "", "packageName,source,id");
  $db->insert("download", ["appID"=>$appID]);
  if($app["source"] == 1) {
    if(1==1) {
      Flight::json([
        'status'=>'failed',
        'type'=>'download',
        'lang'=>$lang,
        'applicationSlug'=>$applicationSlug,
        'result'=>null,
      ]);
      exit();
    }
    // ...
    $file_url="NOT PUBLIC";
    // $file_url=$addresses[0] . 'apks/' . $name . '.apk';
    $data["download_link"]=$file_url;
  }
  else if($app["source"] == 2) {
    if(1==1) {
      Flight::json([
        'status'=>'failed',
        'type'=>'download',
        'lang'=>$lang,
        'applicationSlug'=>$applicationSlug,
        'result'=>null,
      ]);
      exit();
    }
    $file_url="NOT PUBLIC";
    // $file_url=$addresses[0] . 'apks/' . $name . '.apk';
    $data["download_link"]=$file_url;
  }
  Flight::json([
    'status'=>'success',
    'type'=>'download',
    'lang'=>$lang,
    'applicationSlug'=>$applicationSlug,
    'result'=>$data,
  ]);
});

Flight::route('/@lang/@categorySlug/@applicationSlug', function($lang, $categorySlug, $applicationSlug){
  global $db;
  global $categoryFields;
  global $appSingleFields;
  global $appModalFields;
  $data=[];
  // if( is_tag ) {}
  $data["category"]=$db->select("category", ["slug"=>$categorySlug] , "" , $categoryFields.",name".strtoupper($lang));
  $appID=findAppBySlug($applicationSlug);
  $data["application"]=$db->select("application", ["id"=>$appID], "", $appSingleFields);
  $data["screenshots"]=$db->selects("screenshot", ["appID"=>$data["application"]["id"]], "", "image,thumbnail");
  $size=$db->count("application", ["lang"=>$lang, "categoryID"=>$data["application"]["categoryID"]], "", "id");
  $size=$size-10;
  if($size < 0) {
    $size=0;
  } 
  $data["similars"]=$db->selects("application", ["lang"=>$lang, "categoryID"=>$data["application"]["categoryID"]], "ORDER BY `id` DESC LIMIT 50 OFFSET ".rand(0, $size), $appModalFields);
  Flight::json([
    'type'=>'single',
    'lang'=>$lang,
    'categorySlug'=>$categorySlug,
    'applicationSlug'=>$applicationSlug,
    'result'=>$data,
  ]);
});

function findAppBySlug($applicationSlug) {
  global $db;
  $app=$db->select("application", ["packageName"=>$applicationSlug], "", "id");
  return $app["id"];
}

Flight::start();
