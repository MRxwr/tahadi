<?php
$sql = "SELECT * FROM `s_media` WHERE `id` LIKE '3'";
$result = $dbconnect->query($sql);
$row = $result->fetch_assoc();
$emailOpt = $row["emailOpt"];
$giftCard = $row["giftCard"];
$theme = $row["theme"];

$sql = "SELECT * FROM `settings` WHERE `id` LIKE '1'";
$result = $dbconnect->query($sql);
$row = $result->fetch_assoc();
$settingsEmail = $row["email"];
$settingsTitle = $row["title"];
$settingsImage = $row["bgImage"];
$settingsDTime = $row["dTime"];
$settingsDTimeAr = $row["dTimeArabic"];
$settingslogo = $row["logo"];
$cookieSession = $row["cookie"];
$settingsWebsite = $row["website"];
$PaymentAPIKey = $row["PaymentAPIKey"];
$settingsOgDescription = $row["OgDescription"];
$SettingsServiceCharge = $row["serviceCharge"];
$settingsShippingMethod = $row["shippingMethod"];
$headerButton = $row["headerButton"];
$websiteColor = $row["websiteColor"];
$defaultCountry = $row["country"];
$settingsLang = (isset($row["language"]) && $row["language"] == "0") ? "ENG" : "AR";

if ( isset($_GET["lang"]) ){
	$arrayLangs = ["ENG","AR"];
	if ( in_array($_GET["lang"], $arrayLangs) ){
		setcookie("CREATEkwLANG","{$_GET["lang"]}",(86400*30) + time(), "/");
		header("Refresh:0 , url=" . str_replace("?lang={$_GET["lang"]}", "" ,str_replace("&lang={$_GET["lang"]}", "", $_SERVER['REQUEST_URI'])) );die();
	}else{
		setcookie("CREATEkwLANG","{$settingsLang}",(86400*30) + time(), "/");
		header("Refresh:0 , url=" . str_replace("?lang={$settingsLang}", "" ,str_replace("&lang={$settingsLang}", "", $_SERVER['REQUEST_URI'])) );die();
	}
}elseif( !isset($_COOKIE["CREATEkwLANG"]) ){
	$_COOKIE["CREATEkwLANG"] = $settingsLang;
}
?>