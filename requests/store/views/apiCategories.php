<?php
$joinData = array(
    "select" => ["t1.id", "t1.{$titleDB} AS `title`", "t1.imageurl2", "t1.header"],
    "join" => ["categories"],
    "on" => ["t.categoryId = t1.id"]
);
if( $categories = selectJoinDB("category_products",$joinData,"t1.status = '0' AND t1.hidden = '1' GROUP BY t.categoryId ORDER BY t1.rank ASC") ){
    for ( $i = 0; $i < sizeof($categories); $i++ ){
        $categories[$i]["imageurl"] = "https://coeo-102070017.imgix.net/{$categories[$i]["imageurl2"]}?w=400&h=400";
        unset($categories[$i]["imageurl2"]);
    }
    $response["categories"] = $categories;
}else{
    $response["categories"] = array();
}
echo outputData($response);die();
?>