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
        $wallet = $user[0]["wallet"];
        if( isset($_GET["checkoutTotal"]) && !empty($_GET["checkoutTotal"]) ){
            if( $wallet >= $_GET["checkoutTotal"] ){
                $is_enabled = 1;
            }else{
                $is_enabled = 0;
            }
        }else{
            $is_enabled = 0;
        }
        $paymentMethods = array(
            0 => array(
                "id" => 1,
                "title" => errorResponse($lang,"Online Payment","الدفع عبر الانترنت"),
                "is_enabled" => 1,
                "is_hidden" => 0,
                "amount" => numTo3Float(0),
            ),
            1 => array(
                "id" => 2,
                "title" => errorResponse($lang,"Cash on delivery","الدفع عند الاستلام"),
                "is_enabled" => 1,
                "is_hidden" => 0,
                "amount" => numTo3Float(0),
            ),
            2 => array(
                "id" => 3,
                "title" => errorResponse($lang,"Wallet","المحفظة"),
                "is_enabled" => $is_enabled,
                "is_hidden" => 0,
                "amount" => numTo3Float($wallet),
            ),
        );
        echo outputData(array("paymentMethods" => $paymentMethods));die();
    }else{
        $error["msg"] = errorResponse($lang,"Please enter a correct action","الرجاء التحقق من العملية");
        echo outputError($error);die();
    }
}else{
    $error["msg"] = errorResponse($lang,"Please enter a correct action","الرجاء التحقق من العملية");
    echo outputError($error);die();
}

?>