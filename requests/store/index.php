<?php 
header("Content-Type: application/json; charset=UTF-8");
require_once("../../admin/includes/config.php");
require_once("../../admin/includes/functions.php");
require_once("../../admin/includes/translate.php");

if( isset($_GET["language"]) && !empty($_GET["language"]) ){
    $lang = ($_GET["language"] == "ar") ? "ar" : "en";
    $titleDB = ($_GET["language"] == "ar") ? "arTitle" : "enTitle";
    $preorderDB = ($_GET["language"] == "ar") ? "preorderTextAr" : "preorderText";
    $detailsDB = ($_GET["language"] == "ar") ? "arDetails" : "enDetails";
    $aboutDB = ($_GET["language"] == "ar") ? "arAbout" : "enAbout";
    $termsDB = ($_GET["language"] == "ar") ? "arTerms" : "enTerms";
    $policyDB = ($_GET["language"] == "ar") ? "arPolicy" : "enPolicy";
}else{
    $lang = "en";
    $titleDB = "enTitle";
    $preorderDB = "preorderText";
    $detailsDB = "enDetails";
    $aboutDB = "enAbout";
    $termsDB = "enTerms";
    $policyDB = "enPolicy";
}

if( isset($_SERVER['HTTP_AUTHORIZATION']) && !empty($_SERVER['HTTP_AUTHORIZATION']) ){
    $token = str_replace("Bearer ","",$_SERVER["HTTP_AUTHORIZATION"]);
}else{
    $token = "";
}

// get viewed page from pages folder \\
if( isset($_GET["endpoint"]) && searchFile("views","api{$_GET["endpoint"]}.php") ){
	require_once("views/".searchFile("views","api{$_GET["endpoint"]}.php"));
}else{
	echo outputError(array("msg" => "404 endpoint Not Found"));die();
}
?>