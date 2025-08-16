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
    if( $_GET["action"] == "add" ){
        if( !isset($_POST["orderId"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter your order id","يجب ادخال رقم الطلب")));die();
        }
        if( !isset($_POST["type"]) || empty($_POST["type"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter your complain type","يجب ادخال نوع الشكوى")));die();
        }
        if( !isset($_POST["msg"]) || empty($_POST["msg"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter your complain message","يجب ادخال نص الشكوى")));die();
        }
        $_POST["userId"] = $userId;
        if ( insertDB("complains",$_POST) ){
            $response["msg"] = errorResponse($lang,"Your complain has been sent successfully","تم ارسال الشكوى بنجاح");
            echo outputData($response);die();
        }else{
            $response["msg"] = errorResponse($lang,"Something went wrong, please try again later","حدث خطأ ما يرجى المحاولة مرة اخرى");
            echo outputError($response);die();
        }
    }elseif( $_GET["action"] == "list" ){
        if( $orders = selectDB2("`orderId`","orders2","`userId` = '{$userId}'" ) ){
            for( $i = 0; $i < sizeof($orders); $i++ ){
                $response["orders"][$i] = (STRING)STR_PAD($orders[$i]["orderId"], 8, "0", STR_PAD_LEFT);
            }
        }else{
            $response["orders"] = array();
        }
        if( $complainsTypes = selectDB2("`id`,{$titleDB} AS `title`","complains_types","`status` = '0' AND `hidden` = '1' ORDER BY `rank` ASC") ){
            $response["complainsTypes"] = $complainsTypes;
        }else{
            $response["complainsTypes"] = array(
                array(
                    "id" => 0,
                    "title" => "Others"
                )
            );
        }
        echo outputData($response);
    }else{
        $error["msg"] = errorResponse($lang,"Please enter a correct action","الرجاء التحقق من العملية");
        echo outputError($error);die();
    }
}else{
    $error["msg"] = errorResponse($lang,"Please enter a correct action","الرجاء التحقق من العملية");
    echo outputError($error);die();
}

?>