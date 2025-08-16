<?php
if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
    $favouritesList = "";
    $favourites = json_decode($user[0]["favo"], true);
    if (is_array($favourites)) {
        $favouritesList = implode(",", array_map('intval', $favourites));
    } else {
        $favouritesList = "";
    }
}else{
    $favouritesList = "";
}

if( !isset($_POST["id"]) || empty($_POST["id"]) ){
    echo outputError(array("msg" => errorResponse($lang,"Please set product id","يرجى تحديد رقم المنتج") ));die();
}else{
    if( $product = selectDB2New("`id`,`{$titleDB}` AS `title`, `{$detailsDB}` AS `details`, `{$preorderDB}` AS `flag`, `discountType`, `discount`, `video`, CASE WHEN FIND_IN_SET(id, '{$favouritesList}') > 0 THEN 1 ELSE 0 END AS `isLiked`,`brandId`,`categoryId`","products",[$_POST["id"]],"`id` = ? AND `hidden` = '1' AND `status` = '0'","") ){
        if( $images = selectDB2("CASE WHEN imageurl3 <> '' THEN CONCAT('https://coeoapp.com/logos/', imageurl3) WHEN imageurl2 <> '' THEN imageurl2 ELSE CONCAT('https://coeoapp.com/logos/', imageurl) END AS `imageurl`","images","`productId` = '{$_POST["id"]}' ORDER BY `id` ASC") ){
            for( $i = 0; $i < sizeof($images); $i++ ){
                $images[$i] = "{$images[$i]["imageurl"]}";
            }
        }else{
            $images = array();
        }
        $product[0]["images"] = $images;
        if( $category = selectDB2New("`{$titleDB}` AS `title`","categories",[$product[0]["categoryId"]],"`id` = ?","") ){
            $product[0]["category"] = $category[0]["title"];
        }else{
            $product[0]["category"] = "";
        }
        if( $brand = selectDB2New("`{$titleDB}` AS `title`","brands",[$product[0]["brandId"]],"`id` = ?","") ){
            $product[0]["brand"] = $brand[0]["title"];
        }else{
            $product[0]["brand"] = "";
        }
        if( $attibutes = selectDB2New("`id`,`{$titleDB}` AS `title`, `price`, `quantity`, `sku`","attributes_products",[$_POST["id"]],"`productId` = ? AND `status` = '0' AND `hidden` = '0'","") ){
            $product[0]["variant"] = "";
            if( $attributeVariant = selectDB("attributes_variants","`productId` = '{$_POST["id"]}'","") ){
                if( $variantTitle = selectDB2New("`{$titleDB}` AS `title`","attributes",[$attributeVariant[0]["attributeId"]],"`id` = ?","") ){
                    for( $i = 0; $i < sizeof($variantTitle); $i++ ){
                        if( $i > 0 ){
                            $product[0]["variant"] .= " / ";
                        }
                        $product[0]["variant"] .= $variantTitle[$i]["title"];
                    }
                }
            }
            for ( $i=0 ; $i< sizeof($attibutes); $i++){
                $attibutes[$i]["finalPrice"] = 0;
                $attibutes[$i]["price"] = ( isset($attibutes[$i]["price"]) && !empty($attibutes[$i]["price"]) ) ? $attibutes[$i]["price"] : 0;
                if( $product[0]["discountType"] == 0 ){
                    $attibutes[$i]["finalPrice"] = $attibutes[$i]["price"] * ( (100 - $product[0]["discount"]) / 100 );
                }else{
                    $attibutes[$i]["finalPrice"] = $attibutes[$i]["price"] - $product[0]["discount"];
                }
                $attibutes[$i]["finalPrice"] = numTo3Float($attibutes[$i]["finalPrice"]);
                $attibutes[$i]["price"] = numTo3Float($attibutes[$i]["price"]);
            }
            $product[0]["attributes"] = $attibutes;
            // add releated products
            $joinData["select"] = ["t.id","t5.id AS `attributeId`","t.{$titleDB} AS `productTitle`","t2.{$titleDB} AS `categoryTitle`","t3.{$titleDB} AS `brandTitle`","t4.imageurl3 AS `image`","t.{$preorderDB} AS `flag`","t.discountType","t.discount","t5.quantity","CAST(ROUND(t5.price, 3) AS CHAR) AS `price`","CASE WHEN FIND_IN_SET(t.id, '{$favouritesList}') > 0 THEN 1 ELSE 0 END AS `isLiked`","CAST(ROUND(CASE WHEN t.discountType = 0 THEN t5.price * ((100 - t.discount) / 100) ELSE t5.price - t.discount END, 3) AS CHAR) AS `finalPrice`"];
            $joinData["join"] = ["category_products","categories","brands","images","attributes_products"];
            $joinData["on"] = ["t.id = t1.productId","t1.categoryId = t2.id","t.brandId = t3.id","t.id = t4.productId","t.id = t5.productId"];
            if( $releated = selectJoinDB("products", $joinData, "t.hidden = 1 AND t.status = 0 AND t5.status = 0 AND t5.hidden = 0 AND t2.id = '{$product[0]["categoryId"]}' AND t.id != '{$_POST["id"]}' AND t5.price != '0' GROUP BY t.id ORDER BY RAND() LIMIT 6") ){
                for ( $i = 0; $i < sizeof($releated); $i++ ){
                    $releated[$i]["image"] = "https://coeo-102070017.imgix.net/{$releated[$i]["image"]}?w=400&h=400";
                    //$releated[$i]["price"] = numTo3Float($releated[$i]["price"]);
                    //$releated[$i]["finalPrice"] = numTo3Float($releated[$i]["finalPrice"]);
                }
                $product[0]["releated"] = $releated;
            }else{
                $product[0]["releated"] = array();
            }
            unset($product[0]["brandId"]);
            unset($product[0]["categoryId"]);
            echo outputData($product);die();
        }else{
            echo outputError(array("msg" => errorResponse($lang,"No products found","لم يتم العثور على منتجات") ));die();
        }
    }else{
        echo outputError(array("msg" => errorResponse($lang,"No products found","لم يتم العثور على منتجات") ));die();
    }
}
?>