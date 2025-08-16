<?php
if( isset($_POST["code"]) && !empty($_POST["code"]) ){
    $_POST["code"] = trim($_POST["code"]);
    if ( $checkVoucher = selectDBNew("vouchers",[$_POST["code"]],"`code` LIKE ?","") ){
        if( $voucher = selectDBNew("vouchers",[$_POST["code"]],"`code` LIKE ? AND `endDate` >= '".date("Y-m-d")."' AND `startDate` <= '".date("Y-m-d")."'","") ){
            if( empty($token) ){
                echo outputError(array("msg" => errorResponse($lang,"Please login first","يرجى تسجيل الدخول اولا") ));die();
            }else{
                if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
                    $userId = $user[0]["id"];
                }else{
                    echo outputError(array("msg" => errorResponse($lang,"Not valid token","الكود غير صالح") ));die();
                }
            }
            $joinData["select"] = ["t1.discountType","t1.discount","t2.price","t.quantity"];
            $joinData["join"] = ["products","attributes_products"];
            $joinData["on"] = ["t1.id = t.productId","t2.id = t.attributeId"];
            if( $cart = selectJoinDB("cart",$joinData,"t.userId = '{$userId}'") ){
                for ( $i = 0; $i < sizeof($cart); $i++ ){
                    if( $cart[$i]["discountType"] == 0 ){
                        $cart[$i]["price"] = $cart[$i]["price"] * ( (100 - $cart[$i]["discount"]) / 100 );
                    }else{
                        $cart[$i]["price"] = $cart[$i]["price"] - $cart[$i]["discount"];
                    }
                    $cart[$i]["price"] = $cart[$i]["price"]*$cart[$i]["quantity"];
                    $beforeDiscount[] = $cart[$i]["price"];
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
                echo outputData(array("voucher" => "{$_POST["code"]}","priceBeforeDiscount" => numTo3Float(array_sum(($beforeDiscount))),"price" => numTo3Float(array_sum($total))));die();
            }else{
                echo outputError(array("msg" => errorResponse($lang,"Cart is empty","السلة فارغة") ));die();
            }
        }else{
            echo outputError(errorResponse($lang,"Voucher code has expired","كود الخصم منتهي"));die();
        }
    }else{
        echo outputError(errorResponse($lang,"Invalid voucher code","لا يوجد كود خصم بهذا الإسم"));die();
    }
}else{
    echo outputError(errorResponse($lang,"Please enter a correct code","الرجاء التحقق من الكود"));die();
}
?>