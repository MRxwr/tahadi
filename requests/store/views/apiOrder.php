<?php
if( !isset($_GET["action"]) || empty($_GET["action"]) ){
    echo outputError(array("msg" => errorResponse($lang,"Invalid action","خطأ في الإجراء") ));die();
}else{
    if( empty($token) ){
        echo outputError(array("msg" => errorResponse($lang,"Please login first","يرجى تسجيل الدخول اولا") ));die();
    }else{
        if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
            $userId = $user[0]["id"];
            $addresses = json_decode($user[0]["addresses"],true);
        }else{
            echo outputError(array("msg" => errorResponse($lang,"Not valid token","الكود غير صالح") ));die();
        }
    }
    if( isset($_GET["page"]) && !empty($_GET["page"]) ){
        $_GET["page"] = $_GET["page"] * 10;
    }else{
        $_GET["page"] = 0;
    }
    if( $_GET["action"] == "success" ){
        if( isset($_GET["orderId"]) && !empty($_GET["orderId"]) ){
            if( $order = selectDBNew("orders2",[$_GET["orderId"]],"`gatewayId` = ?","") ){
                if( $order[0]["status"] == 0 ){
                    updateDB("orders2",array("status" => "1"),"`id` = '{$order[0]["id"]}'","");
                    $cart = selectDBNew("cart",[$order[0]["userId"]],"`userId` = ?","");
                    whatsappUltraMsg($order[0]["id"]);
                    for($i=0;$i<count($cart);$i++){
                        $attribute = selectDB("attributes_products","`id` = '{$cart[$i]["attributeId"]}'");
                        updateDB("attributes_products",array("quantity" => $attribute[0]["quantity"] - $cart[$i]["quantity"]),"`id` = '{$cart[$i]["attributeId"]}'");
                        deleteDB("cart","`id` = '{$cart[0]["id"]}'");
                    }
                }
                $finalOrder = selectDB2("`orderId`,`address`,`paymentMethod`,`price`,`voucher`,`items`,`info`","orders2","`id` = '{$order[0]["id"]}'");
                $response["orderId"] = (STRING)str_pad($finalOrder[0]["orderId"], 8, "0", STR_PAD_LEFT);
                $response["price"] = (STRING)$finalOrder[0]["price"];
                $response["paymentMethod"] = (STRING)$finalOrder[0]["paymentMethod"];
                $items[0]["items"] = json_decode($finalOrder[0]["items"],true);
                $response["info"] = json_decode($finalOrder[0]["info"],true);
                $response["voucher"] = json_decode($finalOrder[0]["voucher"],true);
                $response["address"] = json_decode($finalOrder[0]["address"],true);
                for($i=0;$i<count($items[0]["items"]);$i++){
                    if( $product = selectDB("products","`id` = '{$items[0]["items"][$i]["productId"]}'") ){
                        $attribute = selectDB("attributes_products","`id` = '{$items[0]["items"][$i]["attributeId"]}'");
                        $image = selectDB2("CASE WHEN imageurl3 <> '' THEN CONCAT('https://coeo-102070017.imgix.net/', imageurl3,'?w=400&h=400') WHEN imageurl2 <> '' THEN imageurl2 ELSE CONCAT('https://coeo-102070017.imgix.net/', imageurl, '?w=400&h=400') END AS `imageurl`","images","`productId` = '{$items[0]["items"][$i]["productId"]}' ORDER BY `id` ASC LIMIT 1");
                        $response["items"][$i]["title"] = ( $lang == "ar" ) ? "{$product[0]["arTitle"]} {$attribute[0]["arTitle"]}" : "{$product[0]["enTitle"]} {$attribute[0]["enTitle"]}";
                        if( $product[0]["discountType"] == 0 ){
                            $response["items"][$i]["finalPrice"] = $attribute[0]["price"] * ((100-$product[0]["discount"])/100);
                        }else{
                            $response["items"][$i]["finalPrice"] = $attribute[0]["price"] - $product[0]["discount"];
                        }
                        //check voucher
                        if ( $product[0]["discount"] == 0 ){
                            if( isset($response["voucher"][0]["discountType"]) ){
                                if( $response["voucher"][0]["discountType"] == 1 ){
                                    $response["items"][$i]["finalPrice"] = $response["items"][$i]["finalPrice"] * ((100-$response["voucher"][0]["discount"])/100);
                                }else{
                                    $response["items"][$i]["finalPrice"] = $response["items"][$i]["finalPrice"] - $response["voucher"][0]["discount"];
                                }
                            }
                        }
                        $response["items"][$i]["quantity"] = $items[0]["items"][$i]["quantity"];
                        $response["items"][$i]["price"] = numTo3Float($attribute[0]["price"]);
                        $response["items"][$i]["finalPrice"] = numTo3Float($response["items"][$i]["finalPrice"]);
                        $response["items"][$i]["totalPrice"] = numTo3Float($response["items"][$i]["finalPrice"]*$items[0]["items"][$i]["quantity"]);
                        $response["items"][$i]["image"] = (STRING)$image[0]["imageurl"];
                    }
                }
                echo outputData($response);die();
            }else{
                echo outputError(array("msg" => errorResponse($lang,"Order not found","لم يتم العثور على الطلب") ));die();
            }
        }else{
            echo outputError(array("msg" => errorResponse($lang,"Invalid order id","خطأ في رقم الطلب") ));die();
        }
    }elseif( $_GET["action"] == "failure" ){
        if( isset($_GET["orderId"]) && !empty($_GET["orderId"]) ){
            if( $order = selectDBNew("orders2",[$_GET["orderId"]],"`gatewayId` = ?","") ){
                if( $order[0]["status"] == 0 ){
                    updateDB("orders2",array("status" => "5"),"`id` = '{$order[0]["id"]}'","");
                    //whatsappUltraMsg($order[0]["id"]);
                }
                echo outputData(errorResponse($lang,"Failed Payment. Please try again","فشل الدفع. يرجى المحاولة مرة أخرى"));die();
            }else{
                echo outputError(array("msg" => errorResponse($lang,"Order not found","لم يتم العثور على الطلب") ));die();
            }
        }else{
            echo outputError(array("msg" => errorResponse($lang,"Invalid order id","خطأ في رقم الطلب") ));die();
        }
    }elseif( $_GET["action"] == "view" ){
        if( isset($_GET["orderId"]) && !empty($_GET["orderId"]) ){
            if( $order = selectDBNew("orders2",[$_GET["orderId"]],"`gatewayId` = ?","") ){
                $finalOrder = selectDB2("`orderId`,`address`,`paymentMethod`,`price`,`voucher`,`items`,`info`,`status`","orders2","`id` = '{$order[0]["id"]}'");
                $response["orderId"] = (STRING)str_pad($finalOrder[0]["orderId"], 8, "0", STR_PAD_LEFT);
                $response["price"] = (STRING)$finalOrder[0]["price"];
                $response["paymentMethod"] = (STRING)$finalOrder[0]["paymentMethod"];
                $items[0]["items"] = json_decode($finalOrder[0]["items"],true);
                $response["items"] = json_decode($finalOrder[0]["items"],true);
                $response["info"] = json_decode($finalOrder[0]["info"],true);
                $response["voucher"] = json_decode($finalOrder[0]["voucher"],true);
                $response["address"] = json_decode($finalOrder[0]["address"],true);
                if( isset($response["address"]["shipping"]) ){
                    $deliveryCharges = numTo3Float($response["address"]["shipping"]);
                }else{
                    $deliveryCharges = numTo3Float(0);
                }
                for( $i = 0; $i < count($items[0]["items"]); $i++ ){
                    if( $product = selectDB("products","`id` = '{$items[0]["items"][$i]["productId"]}'") ){
                        $attribute = selectDB("attributes_products","`id` = '{$items[0]["items"][$i]["attributeId"]}'");
                        $image = selectDB2("CASE WHEN imageurl3 <> '' THEN CONCAT('https://coeo-102070017.imgix.net/', imageurl3, '?w=400&h=400') WHEN imageurl2 <> '' THEN imageurl2 ELSE CONCAT('https://coeo-102070017.imgix.net/', imageurl, '?w=400&h=400') END AS `imageurl`","images","`productId` = '{$items[0]["items"][$i]["productId"]}' ORDER BY `id` ASC LIMIT 1");
                        $response["items"][$i]["title"] = ( $lang == "ar" ) ? "{$product[0]["arTitle"]} {$attribute[0]["arTitle"]}" : "{$product[0]["enTitle"]} {$attribute[0]["enTitle"]}";
                        if( $product[0]["discountType"] == 0 ){
                            $items[0]["items"][$i]["finalPrice"] = $items[0]["items"][$i]["price"] * ((100-$items[0]["items"][$i]["discount"])/100);
                        }else{
                            $items[0]["items"][$i]["finalPrice"] = $items[0]["items"][$i]["price"] - $items[0]["items"][$i]["discount"];
                        }
                        //check voucher
                        if ( $product[0]["discount"] == 0 ){
                            if( isset($response["voucher"][0]["discountType"]) ){
                                if( $response["voucher"][0]["discountType"] == 1 ){
                                    $items[0]["items"][$i]["finalPrice"] = $items[0]["items"][$i]["price"] * ((100-$response["voucher"][0]["discount"])/100);
                                }else{
                                    $items[0]["items"][$i]["finalPrice"] = $items[0]["items"][$i]["price"] - $response["voucher"][0]["discount"];
                                }
                            }
                        }
                        $response["items"][$i]["price"] = numTo3Float($items[0]["items"][$i]["price"]);
                        $response["items"][$i]["finalPrice"] = numTo3Float($items[0]["items"][$i]["finalPrice"]);
                        $response["items"][$i]["totalPrice"] = numTo3Float($items[0]["items"][$i]["finalPrice"] * $items[0]["items"][$i]["quantity"]);
                        $response["items"][$i]["image"] = (STRING)"{$image[0]["imageurl"]}";
                        $subtotal[] = $response["items"][$i]["totalPrice"];
                    }
                }
                
                $response["priceBreakdown"] = array(
                    "subTotal" => numTo3Float(array_sum($subtotal)),
                    "delivery" => numTo3Float($deliveryCharges),
                    "total" => numTo3Float(array_sum($subtotal) + $deliveryCharges),
                );
                $listOfStatus = array(
                    0 => errorResponse($lang,"Pending","قيد الانتظار"),
                    1 => errorResponse($lang,"Confirmed","تم التأكيد"),
                    2 => errorResponse($lang,"Preparing","جاري التجهيز"),
                    3 => errorResponse($lang,"Delivering","جاري التوصيل"),
                    4 => errorResponse($lang,"Delivered","تم التوصيل"),
                    5 => errorResponse($lang,"Cancelled","تم الالغاء"),
                );
                for( $i = 0; $i < count($listOfStatus); $i++ ){
                    $response["track"][$i] = array(
                        "title" => $listOfStatus[$i],
                        "check" => 0,
                    );
                    if( $i <= $finalOrder[0]["status"] ){
                       $response["track"][$i]["check"] = 1;
                    }
                }
                echo outputData($response);die();
            }else{
                echo outputError(array("msg" => errorResponse($lang,"Order not found","لم يتم العثور على الطلب") ));die();
            }
        }else{
            echo outputError(array("msg" => errorResponse($lang,"Invalid order id","خطأ في رقم الطلب") ));die();
        }
    }elseif( $_GET["action"] == "list" ){
        $statusList = [0,1,2,3,4,5];
        $statusResponse = [errorResponse($lang,"Pending","قيد الانتظار"),errorResponse($lang,"Confirmed","تم التأكيد"),errorResponse($lang,"Preparing","جاري التجهيز"),errorResponse($lang,"Delivering","جاري التوصيل"),errorResponse($lang,"Delivered","تم التوصيل"),errorResponse($lang,"Cancelled","تم الالغاء")];
        if( $orders = selectDBNew("orders2",[$userId, $_GET["page"]],"`userId` = ?","`id` DESC LIMIT ?,10") ){
            $response = array();
            for($i=0;$i<count($orders);$i++){
                $finalOrder = selectDB("orders2","`id` = '{$orders[$i]["id"]}'");
                $response[$i]["orderId"] = (STRING)str_pad($finalOrder[0]["orderId"], 8, "0", STR_PAD_LEFT);
                $response[$i]["gatewayId"] = (STRING)$finalOrder[0]["gatewayId"];
                $response[$i]["price"] = (STRING)$finalOrder[0]["price"];
                $response[$i]["paymentMethod"] = (STRING)$finalOrder[0]["paymentMethod"];
                $items[0]["items"] = json_decode($finalOrder[0]["items"],true);
                $response[$i]["info"] = json_decode($finalOrder[0]["info"],true);
                $response[$i]["voucher"] = json_decode($finalOrder[0]["voucher"],true);
                $response[$i]["address"] = json_decode($finalOrder[0]["address"],true);
                $response[$i]["status"] = errorResponse($lang,"Invalid status","خطأ في حالة الطلب");
                for( $j = 0; $j < count($statusList); $j++ ){
                    if( $statusList[$j] == $finalOrder[0]["status"] ){
                        $response[$i]["status"] = $statusResponse[$statusList[$j]];
                    }
                }
                for($j=0;$j<count($items[0]["items"]);$j++){
                    if( $product = selectDB("products","`id` = '{$items[0]["items"][$j]["productId"]}'") ){
                        $attribute = selectDB("attributes_products","`id` = '{$items[0]["items"][$j]["attributeId"]}'");
                        $image = selectDB2("CASE WHEN imageurl3 <> '' THEN CONCAT('https://coeo-102070017.imgix.net/', imageurl3, '?w=400&h=400') WHEN imageurl2 <> '' THEN imageurl2 ELSE CONCAT('https://coeo-102070017.imgix.net/', imageurl, '?w=400&h=400') END AS `imageurl`","images","`productId` = '{$product[0]["id"]}' ORDER BY `id` ASC LIMIT 1");
                        $response[$i]["items"][$j]["title"] = ( $lang == "ar" ) ? "{$product[0]["arTitle"]} {$attribute[0]["arTitle"]}" : "{$product[0]["enTitle"]} {$attribute[0]["enTitle"]}";
                        if( $product[0]["discountType"] == 0 ){
                            $response[$i]["items"][$j]["price"] = $attribute[0]["price"] * ((100-$product[0]["discount"])/100);
                        }else{
                            $response[$i]["items"][$j]["price"] = $attribute[0]["price"] - $product[0]["discount"];
                        }
                        //check voucher
                        if ( $product[0]["discount"] == 0 ){
                            if( isset($response[$i]["voucher"][0]["discountType"]) ){
                                if( $response[$i]["voucher"][0]["discountType"] == 1 ){
                                    $response[$i]["items"][$j]["price"] = $response[$i]["items"][$j]["price"] * ((100-$response[$i]["voucher"][0]["discount"])/100);
                                }else{
                                    $response[$i]["items"][$j]["price"] = $response[$i]["items"][$j]["price"] - $response[$i]["voucher"][0]["discount"];
                                }
                            }
                        }
                        $response[$i]["items"][$j]["price"] = (STRING)$response[$i]["items"][$j]["price"];
                        $response[$i]["items"][$j]["image"] = (STRING)"{$image[0]["imageurl"]}";
                    }
                }
            }
            echo outputData($response);die();
        }else{
            echo outputError(array("msg" => errorResponse($lang,"No orders found","لم يتم العثور على الطلبات") ));die();
        }
    }elseif( $_GET["action"] == "filter" ){
        $statusList = [0,1,2,3,4,5];
        $statusResponse = [errorResponse($lang,"Pending","قيد الانتظار"),errorResponse($lang,"Confirmed","تم التأكيد"),errorResponse($lang,"Preparing","جاري التجهيز"),errorResponse($lang,"Delivering","جاري التوصيل"),errorResponse($lang,"Delivered","تم التوصيل"),errorResponse($lang,"Cancelled","تم الالغاء")];
        if( !isset($_GET["status"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Invalid status","خطأ في حالة الطلب") ));die();
        }elseif( $_GET["status"] == "" ){
            $orders = selectDBNew("orders2",[$userId, $_GET["page"]],"`userId` = ?","`id` DESC LIMIT ?,10");
        }else{
            $orders = selectDBNew("orders2",[$userId, $_GET["status"], $_GET["page"]],"`userId` = ? AND `status` = ?","`id` DESC LIMIT ?,10");
        }
        if( $orders ){
            $response = array();
            for($i=0;$i<count($orders);$i++){
                $finalOrder = selectDB("orders2","`id` = '{$orders[$i]["id"]}'");
                $response[$i]["orderId"] = (STRING)str_pad($finalOrder[0]["orderId"], 8, "0", STR_PAD_LEFT);
                $response[$i]["gatewayId"] = (STRING)$finalOrder[0]["gatewayId"];
                $response[$i]["price"] = (STRING)$finalOrder[0]["price"];
                $response[$i]["paymentMethod"] = (STRING)$finalOrder[0]["paymentMethod"];
                $items[0]["items"] = json_decode($finalOrder[0]["items"],true);
                $response[$i]["info"] = json_decode($finalOrder[0]["info"],true);
                $response[$i]["voucher"] = json_decode($finalOrder[0]["voucher"],true);
                $response[$i]["address"] = json_decode($finalOrder[0]["address"],true);
                $response[$i]["status"] = errorResponse($lang,"Invalid status","خطأ في حالة الطلب");
                for( $j = 0; $j < count($statusList); $j++ ){
                    if( $statusList[$j] == $finalOrder[0]["status"] ){
                        $response[$i]["status"] = $statusResponse[$statusList[$j]];
                    }
                }
                for($j=0;$j<count($items[0]["items"]);$j++){
                    if( $product = selectDB("products","`id` = '{$items[0]["items"][$j]["productId"]}'") ){
                        $attribute = selectDB("attributes_products","`id` = '{$items[0]["items"][$j]["attributeId"]}'");
                        $image = selectDB2("CASE WHEN imageurl3 <> '' THEN CONCAT('https://coeo-102070017.imgix.net/', imageurl3, '?w=400&h=400') WHEN imageurl2 <> '' THEN imageurl2 ELSE CONCAT('https://coeo-102070017.imgix.net/', imageurl, '?w=400&h=400') END AS `imageurl`","images","`productId` = '{$product[0]["id"]}' ORDER BY `id` ASC LIMIT 1");
                        $response[$i]["items"][$j]["title"] = ( $lang == "ar" ) ? "{$product[0]["arTitle"]} {$attribute[0]["arTitle"]}" : "{$product[0]["enTitle"]} {$attribute[0]["enTitle"]}";
                        if( $product[0]["discountType"] == 0 ){
                            $response[$i]["items"][$j]["price"] = $attribute[0]["price"] * ((100-$product[0]["discount"])/100);
                        }else{
                            $response[$i]["items"][$j]["price"] = $attribute[0]["price"] - $product[0]["discount"];
                        }
                        //check voucher
                        if ( $product[0]["discount"] == 0 ){
                            if( isset($response[$i]["voucher"][0]["discountType"]) ){
                                if( $response[$i]["voucher"][0]["discountType"] == 1 ){
                                    $response[$i]["items"][$j]["price"] = $response[$i]["items"][$j]["price"] * ((100-$response[$i]["voucher"][0]["discount"])/100);
                                }else{
                                    $response[$i]["items"][$j]["price"] = $response[$i]["items"][$j]["price"] - $response[$i]["voucher"][0]["discount"];
                                }
                            }
                        }
                        $response[$i]["items"][$j]["price"] = (STRING)$response[$i]["items"][$j]["price"];
                        $response[$i]["items"][$j]["image"] = (STRING)"{$image[0]["imageurl"]}";
                    }
                }
            }
            echo outputData($response);die();
        }else{
            echo outputError(array("msg" => errorResponse($lang,"No orders found","لم يتم العثور على الطلبات") ));die();
        }
    }elseif( $_GET["action"] == "statusList" ){
        $array = array(
            errorResponse($lang,"Pending","قيد الانتظار"),
            errorResponse($lang,"Confirmed","تم التأكيد"),
            errorResponse($lang,"Preparing","جاري التجهيز"),
            errorResponse($lang,"Delivering","جاري التوصيل"),
            errorResponse($lang,"Delivered","تم التوصيل"),
            errorResponse($lang,"Cancelled","تم الالغاء"),
        );
        echo outputData($array);die();
    }else{
        echo outputError(array("msg" => errorResponse($lang,"Invalid action","خطأ في العملية") ));die();
    }
}
?>