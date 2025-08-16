<?php 
if( isset($_GET["action"]) && !empty($_GET["action"]) ){
    if( empty($token) ){
        echo outputError(array("msg" => errorResponse($lang,"Please login first","يرجى تسجيل الدخول اولا") ));die();
    }else{
        if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
            $userId = $user[0]["id"];
        }else{
            echo outputError(array("msg" => errorResponse($lang,"Not valid token","الكود غير صالح") ));die();
        }
    }
    if ($_GET["action"] == "list") {
        if( $favo = selectDBNew("users", [$userId], "`id` = ?", "") ){
            $response["favo"] = [];
            if (is_array(json_decode($favo[0]["favo"], true)) && !empty(json_decode($favo[0]["favo"], true))) {
                $favouritesList = "";
                $favourites = json_decode($user[0]["favo"], true);
                if (is_array($favourites)) {
                    $favouritesList = implode(",", array_map('intval', $favourites));
                } else {
                    $favouritesList = "";
                }
                $joinData["select"] = ["t.id","t5.id AS `attributeId`","t.{$titleDB} AS `productTitle`","t2.{$titleDB} AS `categoryTitle`","t3.{$titleDB} AS `brandTitle`","CASE WHEN t4.imageurl3 <> '' THEN CONCAT('https://coeo-102070017.imgix.net/', t4.imageurl3, '?w=400&h=400') WHEN t4.imageurl2 <> '' THEN t4.imageurl2 ELSE CONCAT('https://coeo-102070017.imgix.net/', t4.imageurl, '?w=400&h=400') END AS `image`","t.{$preorderDB} AS `flag`","t.discountType","t.discount","t5.quantity","CASE WHEN FIND_IN_SET(t.id, '{$favouritesList}') > 0 THEN 1 ELSE 0 END AS `isLiked`","CAST(ROUND(t5.price, 3) AS CHAR) AS `price`","CASE WHEN FIND_IN_SET(t.id, '{$favouritesList}') > 0 THEN 1 ELSE 0 END AS `isLiked`","CAST(ROUND(CASE WHEN t.discountType = 0 THEN t5.price * ((100 - t.discount) / 100) ELSE t5.price - t.discount END, 3) AS CHAR) AS `finalPrice`"];
                $joinData["join"] = ["category_products","categories","brands","images","attributes_products"];
                $joinData["on"] = ["t.id = t1.productId","t1.categoryId = t2.id","t.brandId = t3.id","t.id = t4.productId","t.id = t5.productId"];
                $condition = "t.hidden = 1 AND t.status = 0 AND t5.status = 0 AND t5.hidden = 0 AND t5.price != '0' AND t.id IN ($favouritesList) GROUP BY t.id ORDER BY t.id DESC";
                $products = selectJoinDB("products", $joinData, $condition);
                if ($products) {
                    for ( $i = 0; $i < sizeof($products); $i++ ){
                        $products[$i]["image"] = "{$products[$i]["image"]}";
                    }
                    $response["favo"] = $products;
                }
            }
            echo outputData($response);
            die();
        }else{
            $error["msg"] = errorResponse($lang, "User not found", "لم يتم العثور على المستخدم");
            echo outputError($error);
            die();
        }
    }elseif( $_GET["action"] == "add" ){
        if( !isset($_POST["productId"]) || empty($_POST["productId"]) ){
            $error["msg"] = errorResponse($lang,"Please enter product","الرجاء ادخال المنتج");
            echo outputError($error);die();
        }else{
            if( $favo = selectDBNew("users",[$userId],"`id` = ?","") ){
                $favourites = json_decode($favo[0]["favo"],true);
                if( !is_array($favourites) ){
                    $favourites = array();
                }
                if( in_array($_POST["productId"],$favourites) ){
                    $response["msg"] = errorResponse($lang,"Product already added to favourites","المنتج موجود بالفعل في المفضلة");
                    echo outputData($response);die();
                }
                array_push($favourites,$_POST["productId"]);
                $favourites = array_values($favourites);
                if( updateDB("users",["favo"=>json_encode($favourites)],"`id` = '{$userId}'","") ){
                    $response["msg"] = errorResponse($lang,"Product added to favourites","تمت إضافة المنتج إلى المفضلة");
                    echo outputData($response);die();
                }else{
                    $error["msg"] = errorResponse($lang,"Please try again","الرجاء المحاولة مرة أخرى");
                    echo outputError($error);die();
                }
            }else{
                $error["msg"] = errorResponse($lang,"User not found","لم يتم العثور على المستخدم");
                echo outputError($error);die();
            }
        }
    }elseif( $_GET["action"] == "remove" ){
        if( !isset($_POST["productId"]) || empty($_POST["productId"]) ){
            $error["msg"] = errorResponse($lang,"Please enter product","الرجاء ادخال المنتج");
            echo outputError($error);die();
        }else{
            if( $favo = selectDBNew("users",[$userId],"`id` = ?","") ){
                $favourites = json_decode($favo[0]["favo"],true);
                if( !is_array($favourites) ){
                    $favourites = array();
                }
                if( !in_array($_POST["productId"],$favourites) ){
                    $response["msg"] = errorResponse($lang,"Product not found in favourites","المنتج غير موجود في المفضلة");
                    echo outputData($response);die();
                }
                $keys = array_keys($favourites,$_POST["productId"]);
                unset($favourites[$keys[0]]);
                $favourites = array_values($favourites);
                if( updateDB("users",["favo"=>json_encode($favourites)],"`id` = '{$userId}'") ){
                    $response["msg"] = errorResponse($lang,"Product removed from favourites","تمت إزالة المنتج من المفضلة");
                    echo outputData($response);die();
                }else{
                    $error["msg"] = errorResponse($lang,"Please try again","الرجاء المحاولة مرة أخرى");
                    echo outputError($error);die();
                }
            }else{
                $error["msg"] = errorResponse($lang,"User not found","لم يتم العثور على المستخدم");
                echo outputError($error);die();
            }
        }
    }else{
        $error["msg"] = errorResponse($lang,"Please enter a correct action","الرجاء التحقق من العملية");
        echo outputError($error);die();
    }
}else{
    $error["msg"] = errorResponse($lang,"Please enter a correct action","الرجاء التحقق من العملية");
    echo outputError($error);die();
}

?>