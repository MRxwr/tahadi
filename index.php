<?php 
include("admin/includes/config.php");
include("admin/includes/functions.php");
include("admin/includes/translate.php");

if( isset($_GET["v"]) && searchFile("views","blade{$_GET["v"]}.php") ){
	require_once("views/".searchFile("views","blade{$_GET["v"]}.php"));
}else{
	require_once("views/bladeHome.php");
}
?>