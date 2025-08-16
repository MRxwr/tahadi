<?php
$joinData = array(
    "select" => ["t1.id", "t1.{$titleDB} AS `title`", "t1.imageurl2", "t1.header"],
    "join" => ["brands"],
    "on" => ["t.brandId = t1.id"]
);

if( $brands = selectJoinDB("products",$joinData,"t1.status = '0' AND t1.hidden = '1' AND t1.imageurl != '' GROUP BY t.brandId ORDER BY t1.rank ASC") ){
    for ( $i = 0; $i < sizeof($brands); $i++ ){
        $brands[$i]["imageurl"] = "https://coeo-102070017.imgix.net/{$brands[$i]["imageurl2"]}?w=400&h=400";
        unset($brands[$i]["imageurl2"]);
    }
    $response["brands"] = $brands;
}else{
    $response["brands"] = array();
}

echo outputData($response);die();
?>