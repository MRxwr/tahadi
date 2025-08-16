<?php 
include("admin/includes/config.php");
include("admin/includes/functions.php");
include("admin/includes/translate.php");
/*
if( isset($_GET["result"]) && !empty($_GET["result"]) ){
	if( isset($_GET["requested_order_id"]) && !empty($_GET["requested_order_id"]) && $order = selectDBNew("orders2",[$_GET["requested_order_id"]],"`gatewayId` LIKE ?","") ){
		if( $order[0]["status"] == 0 ){
			if( $_GET["result"] == "success" ){
				updateDB("orders2",array("status"=>1),"`id` = '{$order[0]["id"]}'");
			}else{
				updateDB("orders2",array("status"=>5),"`id` = '{$order[0]["id"]}'");
			}
			whatsappUltraMsg($order[0]["id"]);
		}
	}
}
*/
if( isset($_GET["v"]) && searchFile("views","blade{$_GET["v"]}.php") ){
	require_once("views/".searchFile("views","blade{$_GET["v"]}.php"));
}else{
	require_once("views/bladeHome.php");
}
?>