<?php
if( !isset($_GET["action"]) || empty($_GET["action"]) ){
    echo outputError(array("msg" => errorResponse($lang,"Please set action","يرجى تحديد نوع العملية") ));die();
}else{
    if( empty($token) ){
        echo outputError(array("msg" => errorResponse($lang,"Please login first","يرجى تسجيل الدخول اولا") ));die();
    }else{
        if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
            $userId = $user[0]["id"];
        }else{
            echo outputError(array("msg" => errorResponse($lang,"Not valid token","الكود غير صالح") ));die();
        }
    }
    if( $_GET["action"] == 1 /* add */ ){
        if( !isset($_POST["productId"]) || empty($_POST["productId"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please set product id","يرجى تحديد رقم المنتج") ));die();
        }elseif(!isset($_POST["attributeId"]) || empty($_POST["attributeId"])){
            echo outputError(array("msg" => errorResponse($lang,"Please set attribute id","يرجى تحديد رقم السمة") ));die();
        }elseif(!isset($_POST["quantity"]) || empty($_POST["quantity"])){
            echo outputError(array("msg" => errorResponse($lang,"Please set quantity","يرجى تحديد الكمية") ));die();
        }else{
            $product = selectDBNew("products",[$_POST["productId"]],"`id` = ?","");
            $attribute = selectDBNew("attributes_products",[$_POST["attributeId"]],"`id` = ?","");
            $dataInsert = array(
                "userId" => $userId,
                "productId" => $_POST["productId"],
                "attributeId" => $_POST["attributeId"],
                "quantity" => $_POST["quantity"],
                "price" => $attribute[0]["price"],
                "discountType" => $product[0]["discountType"],
                "discount" => $product[0]["discount"],
                "collections" => json_encode(array(),JSON_UNESCAPED_UNICODE),
                "giftCard" => json_encode(array(),JSON_UNESCAPED_UNICODE),
                "note" => "",
                "image" => "",
                "extras" => json_encode(array(array("id" => array(), "variant"=> array())),JSON_UNESCAPED_UNICODE),
            );
            if( $checkProduct = selectDBNew("cart",[$userId,$_POST["productId"],$_POST["attributeId"]],"`userId` = ? AND `productId` = ? AND `attributeId` = ?","") ){
                if ( $attribute[0]["quantity"] >= $checkProduct[0]["quantity"] + $_POST["quantity"] ) {
                    $dataInsert["quantity"] = $checkProduct[0]["quantity"] + $_POST["quantity"];
                }else{
                    echo outputError(array("msg" => errorResponse($lang,"Not valid quantity","الكمية غير صالحة") ));die();
                }
                deleteDB("cart","`id` = '{$checkProduct[0]["id"]}'");
            }
            if( insertDB("cart",$dataInsert) ){
                if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
                    if( $cartItems = selectDB2("SUM(`quantity`) AS `quantity`","cart","`userId` = '{$user[0]["id"]}'") ){
                        $response["cartItems"] = $cartItems[0]["quantity"];
                    }else{
                        $response["cartItems"] = 0;
                    }
                }else{
                    $response["cartItems"] = 0;
                }
                echo outputData(array("msg" => errorResponse($lang,"Added successfully","تمت الاضافة بنجاح"),"cartItems" => $response["cartItems"]));die();
            }else{
                if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
                    if( $cartItems = selectDB2("SUM(`quantity`) AS `quantity`","cart","`userId` = '{$user[0]["id"]}'") ){
                        $response["cartItems"] = $cartItems[0]["quantity"];
                    }else{
                        $response["cartItems"] = 0;
                    }
                }else{
                    $response["cartItems"] = 0;
                }
                echo outputError(array("msg" => errorResponse($lang,"Failed to add","فشلت الاضافة"),"cartItems" => $response["cartItems"]));die();
            }
        }
    }elseif( $_GET["action"] == 2 /* update */ ){
        if( !isset($_POST["productId"]) || empty($_POST["productId"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please set product id","يرجى تحديد رقم المنتج") ));die();
        }elseif(!isset($_POST["attributeId"]) || empty($_POST["attributeId"])){
            echo outputError(array("msg" => errorResponse($lang,"Please set attribute id","يرجى تحديد رقم السمة") ));die();
        }elseif(!isset($_POST["quantity"]) || empty($_POST["quantity"])){
            echo outputError(array("msg" => errorResponse($lang,"Please set quantity","يرجى تحديد الكمية") ));die();
        }else{
            $dataUpdate = array(
                "quantity" => $_POST["quantity"]
            );
            if( updateDB("cart",$dataUpdate,"`userId` = '{$userId}' AND `productId` = '{$_POST["productId"]}' AND `attributeId` = '{$_POST["attributeId"]}'") ){
                echo outputData(array("msg" => errorResponse($lang,"Updated successfully","تم التعديل بنجاح") ));die();
            }else{
                echo outputError(array("msg" => errorResponse($lang,"Failed to update","فشل التعديل") ));die();
            }
        }
    }elseif( $_GET["action"] == 3 /* delete */ ){
        if( !isset($_POST["productId"]) || empty($_POST["productId"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please set product id","يرجى تحديد رقم المنتج") ));die();
        }elseif(!isset($_POST["attributeId"]) || empty($_POST["attributeId"])){
            echo outputError(array("msg" => errorResponse($lang,"Please set attribute id","يرجى تحديد رقم السمة") ));die();
        }else{
            if( deleteDB("cart","`userId` = '{$userId}' AND `productId` = '{$_POST["productId"]}' AND `attributeId` = '{$_POST["attributeId"]}'") ){
                if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
                    if( $cartItems = selectDB2("SUM(`quantity`) AS `quantity`","cart","`userId` = '{$user[0]["id"]}'") ){
                        $response["cartItems"] = $cartItems[0]["quantity"];
                    }else{
                        $response["cartItems"] = 0;
                    }
                }else{
                    $response["cartItems"] = 0;
                }
                echo outputData(array("msg" => errorResponse($lang,"Deleted successfully","تم الحذف بنجاح"),"cartItems" => $response["cartItems"]));die();
            }else{
                echo outputError(array("msg" => errorResponse($lang,"Failed to delete","فشل الحذف"),"cartItems" => $response["cartItems"]));die();
            }
        }
    }elseif( $_GET["action"] == 4 /* clear */ ){
        if( deleteDB("cart","`userId` = '{$userId}'") ){
            if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
                if( $cartItems = selectDB2("SUM(`quantity`) AS `quantity`","cart","`userId` = '{$user[0]["id"]}'") ){
                    $response["cartItems"] = $cartItems[0]["quantity"];
                }else{
                    $response["cartItems"] = 0;
                }
            }else{
                $response["cartItems"] = 0;
            }
            echo outputData(array("msg" => errorResponse($lang,"Cleared successfully","تم الحذف بنجاح"),"cartItems" => $response["cartItems"]));die();
        }else{
            echo outputError(array("msg" => errorResponse($lang,"Failed to clear cart","فشل الحذف السله"),"cartItems" => $response["cartItems"]));die();
        }
    }elseif( $_GET["action"] == 5 /* list */ ){
        if( $cart = selectDB("cart","`userId` = '{$userId}' ORDER BY id DESC") ){
            for ( $i = 0; $i < sizeof($cart); $i++ ){
                $product = selectDB2("`id`, {$titleDB} AS `productTitle`,{$preorderDB} AS `flag`, `discountType`, `discount`, `brandId`","products","`id` = '{$cart[$i]["productId"]}'");
                $attibute = selectDB("attributes_products","`id` = '{$cart[$i]["attributeId"]}'");
                $image = selectDB("images","`productId` = '{$cart[$i]["productId"]}' LIMIT 1");
                $image = selectDB2("CASE WHEN imageurl3 <> '' THEN CONCAT('https://coeo-102070017.imgix.net/', imageurl3,'?w=400&h=400') WHEN imageurl2 <> '' THEN imageurl2 ELSE CONCAT('https://coeo-102070017.imgix.net/', imageurl,'?w=400&h400') END AS `imageurl`","images","`productId` = '{$cart[$i]["productId"]}' ORDER BY `id` ASC LIMIT 1");
                $brand = selectDB2("{$titleDB} AS `brandTitle`","brands","`id` = '{$product[0]["brandId"]}'");
                $categoryProduct = selectDB("category_products","`productId` = '{$product[0]["id"]}' LIMIT 1");
                $category = selectDB2("{$titleDB} AS `categoryTitle`","categories","`id` = '{$categoryProduct[0]["categoryId"]}'");
                if( $product[0]["discountType"] == 0 ){
                    $cart[$i]["finalPrice"] = $attibute[0]["price"] * ( (100 - $product[0]["discount"]) / 100 );
                }else{
                    $cart[$i]["finalPrice"] = $attibute[0]["price"] - $product[0]["discount"];
                }
                $response[] = array(
                    "id" => $cart[$i]["id"],
                    "productId" => $cart[$i]["productId"],
                    "attributeId" => $cart[$i]["attributeId"],
                    "quantity" => $cart[$i]["quantity"],
                    "flag" => $product[0]["flag"],
                    "discountType" => $cart[$i]["discountType"],
                    "discount" => $cart[$i]["discount"],
                    "productTitle" => $product[0]["productTitle"],
                    "brandTitle" => $brand[0]["brandTitle"],
                    "categoryTitle" => $category[0]["categoryTitle"],
                    "image" => $image[0]["imageurl"],
                    "price" => numTo3Float($attibute[0]["price"]),
                    "finalPrice" => numTo3Float($cart[$i]["finalPrice"]),
                    "totalPrice" => numTo3Float($cart[$i]["finalPrice"] * $cart[$i]["quantity"])
                );
            }
            //$response["total"] = array_sum(array_column($response,"totalPrice"));
            echo outputData($response);die();
        }else{
            echo outputError(array("msg" => errorResponse($lang,"Cart is empty","السلة فارغة") ));die();
        }
    }else{
        echo outputError(array("msg" => errorResponse($lang,"Not valid action","العملية غير صالحة") ));die();
    }
}
?>