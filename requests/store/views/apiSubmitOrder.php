<?php
if( empty($token) ){
    echo outputError(array("msg" => errorResponse($lang,"Please login first","يرجى تسجيل الدخول اولا") ));die();
}else{
    if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
        $userId = $user[0]["id"];
        $addresses = json_decode($user[0]["addresses"],true);
    }else{
        echo outputError(array("msg" => errorResponse($lang,"Not valid token","الكود غير صالح") ));die();
    }
    if( isset($_POST["voucher"]) && !empty($_POST["voucher"]) ){
        $_POST["voucher"] = trim($_POST["voucher"]);
        if( $voucherDetails = selectDBNew("vouchers",[$_POST["voucher"]],"`code` = ?","") ){
            $voucher[0] = array(
                "id" => $voucherDetails[0]["id"],
                "voucher" => $voucherDetails[0]["code"],
                "discount" => $voucherDetails[0]["discount"],
                "discountType" => $voucherDetails[0]["discountType"],
                "percentage" => $voucherDetails[0]["percentage"]
            );
        }else{
            $voucher[0] = array(
                "id" => "",
                "voucher" => "",
                "discount" => 0,
                "discountType" => 0,
                "percentage" => 0
            );
        }
    }else{
        $voucher[0] = array(
            "id" => "",
            "voucher" => "",
            "discount" => 0,
            "discountType" => 0,
            "percentage" => 0
        );
    }
    $info = array(
        "name" => "{$user[0]["fName"]} {$user[0]["lName"]}",
        "email" => "{$user[0]["email"]}",
        "phone" => "{$user[0]["countryCode"]}{$user[0]["phone"]}",
        "civilId" => "",
    );
    $items = selectDBNew("cart",[$user[0]["id"]],"`userId` = ?","");
    $joinData["select"] = ["t1.discountType","t1.discount","t2.price","t.quantity"];
    $joinData["join"] = ["products","attributes_products"];
    $joinData["on"] = ["t1.id = t.productId","t2.id = t.attributeId"];
    if( $cart = selectJoinDB("cart",$joinData,"t.userId = '{$userId}' GROUP BY t1.id ORDER BY t.id DESC") ){
        for ( $i = 0; $i < sizeof($cart); $i++ ){
            if( $cart[$i]["discountType"] == 0 ){
                $cart[$i]["price"] = $cart[$i]["price"] * ( (100 - $cart[$i]["discount"]) / 100 );
            }else{
                $cart[$i]["price"] = $cart[$i]["price"] - $cart[$i]["discount"];
            }
            $cart[$i]["price"] = $cart[$i]["price"]*$cart[$i]["quantity"];
            if ( empty($cart[$i]["discount"]) ){
                if( $voucher[0]["discountType"] == 1 ){
                    $total[] = $cart[$i]["price"] * ( (100 - $voucher[0]["discount"]) / 100 );
                }else{
                    $total[] = $cart[$i]["price"] - $voucher[0]["discount"];
                }
            }else{
                $total[] = $cart[$i]["price"];
            }
        }
        if( $delivery = selectDB("areas","`enTitle` LIKE '%{$addresses[$_POST["addressId"]]["area"]}%' OR `arTitle` LIKE '%{$addresses[$_POST["addressId"]]["area"]}%'") ){
            $deliveryCharges = numTo3Float($delivery[0]["charges"]);
        }else{
            $deliveryCharges = numTo3Float(0);
        }
        $addresses[$_POST["addressId"]]["shipping"] = $deliveryCharges;
        $data = array(
            "userId" => $userId,
            "orderId" => generateOrderId(),
            "address" => json_encode($addresses[$_POST["addressId"]],JSON_UNESCAPED_UNICODE),
            "paymentMethod" => $_POST["paymentMethod"],
            "price" => array_sum($total)+(float)$deliveryCharges,
            "voucher" => json_encode($voucher,JSON_UNESCAPED_UNICODE),
            "items" => json_encode($items,JSON_UNESCAPED_UNICODE),
            "info" => json_encode($info,JSON_UNESCAPED_UNICODE),
        );
        if( $response = upaymentGateway($data) ){
            if( isset($response["status"]) && $response["status"] == true && isset($response["data"]["link"]) && !empty($response["data"]["link"]) ){
                $data["gatewayId"] = $response["orderId"];
                if( insertDB("orders2",$data) ){
                    echo outputData(array("url" => $response["data"]["link"],"orderId" => $response["orderId"]));die();
                }else{
                    echo outputError(array("msg" => errorResponse($lang,"Could not create order","لا يمكن انشاء الطلب") ));die();
                }
            }else{
                echo outputError(array("msg" => errorResponse($lang,"Payment gateway error","خطأ في بوابة الدفع") ));die();
            }
        }else{
            echo outputError(array("msg" => errorResponse($lang,"Something went wrong","حدث خطأ ما") ));die();
        }
    }else{
        echo outputError(array("msg" => errorResponse($lang,"Cart is empty","السلة فارغة") ));die();
    }
}
?>