<?php
if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
    $favouritesList = "";
    $favourites = json_decode($user[0]["favo"], true);
    if (is_array($favourites)) {
        $favouritesList = implode(",", array_map('intval', $favourites));
    } else {
        $favouritesList = "";
    }
    if( $notifications = selectDB2("COUNT(`id`) AS `count`","notifications","`status` = '0' AND `userId` = '{$user[0]["id"]}'") ){
        $response["notifications"] = $notifications[0]["count"];
    }else{
        $response["notifications"] = 0;
    }
    if( $cartItems = selectDB2("SUM(`quantity`) AS `quantity`","cart","`userId` = '{$user[0]["id"]}'") ){
        $response["cartItems"] = $cartItems[0]["quantity"];
    }else{
        $response["cartItems"] = 0;
    }
}else{
    $response["notifications"] = 0;
    $response["cartItems"] = 0;
    $favouritesList = "";
}

if( $banners = selectDB2("`id`,`image`,`link`,`type`,`popup`","banners","`status` = '0' AND `hidden` = '1' ORDER BY `rank` ASC") ){
    for ( $i = 0; $i < sizeof($banners); $i++ ){
        $banners[$i]["image"] = "https://coeo-102070017.imgix.net/{$banners[$i]["image"]}";
    }
    $response["banners"] = $banners;
}else{
    $response["banners"] = array(); 
}

$joinData = array(
    "select" => ["t1.id", "t1.{$titleDB} AS `title`", "t1.imageurl2", "t1.header"],
    "join" => ["categories"],
    "on" => ["t.categoryId = t1.id"]
);
if( $categories = selectJoinDB("category_products",$joinData,"t1.status = '0' AND t1.hidden = '1' GROUP BY t.categoryId ORDER BY t1.rank ASC LIMIT 6") ){
    for ( $i = 0; $i < sizeof($categories); $i++ ){
        $categories[$i]["imageurl"] = "https://coeo-102070017.imgix.net/{$categories[$i]["imageurl2"]}?w=400&h=400";
        unset($categories[$i]["imageurl2"]);
    }
    $response["categories"] = $categories;
}else{
    $response["categories"] = array();
}

$joinData = array(
    "select" => ["t1.id", "t1.{$titleDB} AS `title`", "t1.imageurl2", "t1.header"],
    "join" => ["brands"],
    "on" => ["t.brandId = t1.id"]
);
if( $brands = selectJoinDB("products",$joinData,"t1.status = '0' AND t1.hidden = '1' AND t1.imageurl != '' GROUP BY t.brandId ORDER BY t1.rank ASC LIMIT 6" ) ){
    for ( $i = 0; $i < sizeof($brands); $i++ ){
        $brands[$i]["imageurl"] = "https://coeo-102070017.imgix.net/{$brands[$i]["imageurl2"]}?w=400&h=400";
        unset($brands[$i]["imageurl2"]);
    }
    $response["brands"] = $brands;
}else{
    $response["brands"] = array();
}

$joinData["select"] = ["t.id","t5.id AS `attributeId`","t.{$titleDB} AS `productTitle`","t2.{$titleDB} AS `categoryTitle`","t3.{$titleDB} AS `brandTitle`","t4.imageurl3 AS `image`","t.{$preorderDB} AS `flag`","t.discountType","t.discount","t5.quantity","CASE WHEN FIND_IN_SET(t.id, '{$favouritesList}') > 0 THEN 1 ELSE 0 END AS `isLiked`","CAST(ROUND(t5.price, 3) AS CHAR) AS `price`","CASE WHEN FIND_IN_SET(t.id, '{$favouritesList}') > 0 THEN 1 ELSE 0 END AS `isLiked`","CAST(ROUND(CASE WHEN t.discountType = 0 THEN t5.price * ((100 - t.discount) / 100) ELSE t5.price - t.discount END, 3) AS CHAR) AS `finalPrice`"];
$joinData["join"] = ["category_products","categories","brands","images","attributes_products"];
$joinData["on"] = ["t.id = t1.productId","t1.categoryId = t2.id","t.brandId = t3.id","t.id = t4.productId","t.id = t5.productId"];
if( $bestSellers = selectJoinDB("products", $joinData, "t.hidden = 1 AND t.status = 0 AND t5.status = 0 AND t5.hidden = 0 AND t.bestSeller = '1' AND t5.price != '0' GROUP BY t.id ORDER BY RAND() LIMIT 6") ){
    for ( $i = 0; $i < sizeof($bestSellers); $i++ ){
        $bestSellers[$i]["image"] = "https://coeo-102070017.imgix.net/{$bestSellers[$i]["image"]}?w=400&h=400";
    }
    $response["bestSellers"] = $bestSellers;
}else{
    $response["bestSellers"] = array();
}

$joinData["select"] = ["t.id","t5.id AS `attributeId`","t.{$titleDB} AS `productTitle`","t2.{$titleDB} AS `categoryTitle`","t3.{$titleDB} AS `brandTitle`","t4.imageurl3 AS `image`","t.{$preorderDB} AS `flag`","t.discountType","t.discount","t5.quantity","CASE WHEN FIND_IN_SET(t.id, '{$favouritesList}') > 0 THEN 1 ELSE 0 END AS `isLiked`","CAST(ROUND(t5.price, 3) AS CHAR) AS `price`","CASE WHEN FIND_IN_SET(t.id, '{$favouritesList}') > 0 THEN 1 ELSE 0 END AS `isLiked`","CAST(ROUND(CASE WHEN t.discountType = 0 THEN t5.price * ((100 - t.discount) / 100) ELSE t5.price - t.discount END, 3) AS CHAR) AS `finalPrice`"];
$joinData["join"] = ["category_products","categories","brands","images","attributes_products"];
$joinData["on"] = ["t.id = t1.productId","t1.categoryId = t2.id","t.brandId = t3.id","t.id = t4.productId","t.id = t5.productId"];
if( $recent = selectJoinDB("products", $joinData, "t.hidden = 1 AND t.status = 0 AND t5.status = 0 AND t5.hidden = 0 AND t.recent = '1' AND t5.price != '0' GROUP BY t.id ORDER BY RAND() LIMIT 6") ){
    for ( $i = 0; $i < sizeof($recent); $i++ ){
        $recent[$i]["image"] = "https://coeo-102070017.imgix.net/{$recent[$i]["image"]}?w=400&h=400";
    }
    $response["recent"] = $recent;
}else{
    $response["recent"] = array();
}

echo outputData($response);die();
?>