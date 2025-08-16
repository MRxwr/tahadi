<?php
if( !isset($_GET["action"]) || empty($_GET["action"]) ){
    echo outputError(errorResponse($lang,"Invalid action request","طلب غير صالح"));die();
}else{
    if($_GET["action"] == "setDefault"){
        if( empty($token) ){
            echo outputError(array("msg" => errorResponse($lang,"Please login first","يرجى تسجيل الدخول اولا") ));die();
        }else{
            if( !isset($_POST["id"]) ){
                echo outputError(array("msg" => errorResponse($lang,"Please enter address id","يجب ادخال رقم العنوان")));die();
            }
            if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
                $addresses = json_decode($user[0]["addresses"],true); 
                if( !is_array($addresses) ){
                    $addresses = array();
                }
                $defaultAddress = $addresses[$_POST["id"]];
                unset($addresses[$_POST["id"]]);
                array_unshift($addresses,$defaultAddress);
                $addresses = array_values($addresses);
                if( updateDB("users",["addresses"=>json_encode($addresses)],"`id` = '{$user[0]["id"]}'","") ){
                    echo outputData(array("msg" => errorResponse($lang,"Address set as default","تم تثبيت العنوان بنجاح")));die();
                }else{
                    echo outputError(array("msg" => errorResponse($lang,"Please try again","الرجاء المحاولة مرة اخرى")));die();
                }
            }else{
                echo outputError(array("msg" => errorResponse($lang,"Not valid token","الكود غير صالح") ));die();
            }
        }
    }elseif( $_GET["action"] == "getDefault" ){
        if( empty($token) ){
            echo outputError(array("msg" => errorResponse($lang,"Please login first","يرجى تسجيل الدخول اولا") ));die();
        }else{
            if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
                $addresses = json_decode($user[0]["addresses"],true); 
                if( !is_array($addresses) ){
                    $addresses = array();
                }
                echo outputData($addresses[0]);die();
            }else{
                echo outputError(array("msg" => errorResponse($lang,"Not valid token","الكود غير صالح")));die();
            }
        }
    }elseif($_GET["action"] == "areas"){
        if( empty($token) ){
            echo outputError(array("msg" => errorResponse($lang,"Please login first","يرجى تسجيل الدخول اولا") ));die();
        }else{
           if( $areas = selectDB2("`id`,`enTitle`, `arTitle`","areas","`status` = '0'") ){
                echo outputData($areas);die();
            }else{
                echo outputError(array("msg" => errorResponse($lang,"No Areas found","لا يوجد مناطق")));die();
            }
        }
    }elseif( $_GET["action"] == "list" ){
        if( empty($token) ){
            echo outputError(array("msg" => errorResponse($lang,"Please login first","يرجى تسجيل الدخول اولا") ));die();
        }else{
            if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
                $addresses = json_decode($user[0]["addresses"],true);
                if( !is_array($addresses) ){
                    $addresses = array();
                }else{
                    for( $i = 0; $i < count($addresses); $i++ ){
                        if( $price = selectDB("areas","`enTitle` LIKE '%{$addresses[$i]["area"]}%' OR `arTitle` LIKE '%{$addresses[$i]["area"]}%'") ){
                            $addresses[$i]["charges"] = numTo3Float($price[0]["charges"]);
                        }else{
                            $addresses[$i]["charges"] = numTo3Float(0);
                        }
                    }
                    echo outputData($addresses);die();  
                }
            }else{
                echo outputError(array("msg" => errorResponse($lang,"Not valid token","الكود غير صالح")));die();
            }
        }
    }elseif( $_GET["action"] == "add" ){
        if( empty($token) ){
            echo outputError(array("msg" => errorResponse($lang,"Please login first","يرجى تسجيل الدخول اولا") ));die();
        }else{
            if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
                $addresses = json_decode($user[0]["addresses"],true); 
                if( !is_array($addresses) ){
                    $addresses = array();
                }
            }else{
                echo outputError(array("msg" => errorResponse($lang,"Not valid token","الكود غير صالح")));die();
            }
        }
        if( !isset($_POST["place"]) || empty($_POST["place"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter your place type","يجب ادخال نوع العنوان")));die();
        }
        if( !isset($_POST["country"]) || empty($_POST["country"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter your country","يجب أدخال بلدك")));die();
        }
        if( !isset($_POST["area"]) || empty($_POST["area"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter your area","يجب ادخال المنطقه")));die();
        }
        if( !isset($_POST["block"]) || empty($_POST["block"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter your block","يجب ادخال القطعة")));die();
        }
        if( !isset($_POST["street"]) || empty($_POST["street"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter your street","يجب ادخال الشارع")));die();
        }
        if( !isset($_POST["building"]) || empty($_POST["building"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter your building","يجب أدخال المبنى")));die();
        }
        $dataUpdate = array(
            "place" => $_POST["place"],
            "country" => $_POST["country"],
            "area" => $_POST["area"],
            "block" => $_POST["block"],
            "street" => $_POST["street"],
            "avenue" => ( !isset($_POST["avenue"]) || empty($_POST["avenue"]) ) ? "" : $_POST["avenue"],
            "building" => $_POST["building"],
            "floor" => ( !isset($_POST["floor"]) || empty($_POST["floor"]) ) ? "" : $_POST["floor"],
            "apartment" => ( !isset($_POST["apartment"]) || empty($_POST["apartment"]) ) ? "" : $_POST["apartment"],
            "postalCode" => ( !isset($_POST["postalCode"]) || empty($_POST["postalCode"]) ) ? "" : $_POST["postalCode"],
            "notes" => ( !isset($_POST["notes"]) || empty($_POST["notes"]) ) ? "" : $_POST["notes"],
        );
        array_push($addresses,$dataUpdate);
        $addresses = array_values($addresses);
        for( $i = 0; $i < count($addresses); $i++ ){
            $addresses[$i]["id"] = $i;
        }
        $data["addresses"] = json_encode($addresses);
        if( updateDB("users",$data,"`id` = '{$user[0]["id"]}'") ){
            echo outputData(array("msg" => errorResponse($lang,"Your address has been added successfully","تم أضافة العنوان بنجاح")));die();
        }else{
            echo outputError(array("msg" => errorResponse($lang,"Something went wrong","حدث خطأ ما")));die();
        }
    }elseif( $_GET["action"] == "delete" ){
        if( empty($token) ){
            echo outputError(array("msg" => errorResponse($lang,"Please login first","يرجى تسجيل الدخول اولا") ));die();
        }else{
            if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
                $addresses = json_decode($user[0]["addresses"],true); 
                if( !is_array($addresses) ){
                    $addresses = array();
                }
            }else{
                echo outputError(array("msg" => errorResponse($lang,"Not valid token","الكود غير صالح")));die();
            }
        }
        if( !isset($_POST["id"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter index","الرقم غير صالح")));die();
        }
        array_splice($addresses, $_POST["id"], 1);
        $addresses = array_values($addresses);
        for( $i = 0; $i < count($addresses); $i++ ){
            $addresses[$i]["id"] = $i;
        }
        if( updateDB("users",array("addresses" => json_encode($addresses)),"`id` = '{$user[0]["id"]}'") ){
            echo outputData(array("msg" => errorResponse($lang,"Your address has been deleted successfully","تم حذف العنوان بنجاح")));die();
        }else{
            echo outputError(array("msg" => errorResponse($lang,"Something went wrong","حدث خطأ ما")));die();
        }    
    }elseif( $_GET["action"] == "update" ){
        if( empty($token) ){
            echo outputError(array("msg" => errorResponse($lang,"Please login first","يرجى تسجيل الدخول اولا") ));die();
        }else{
            if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
                $addresses = json_decode($user[0]["addresses"],true); 
                if( !is_array($addresses) ){
                    $addresses = array();
                }
            }else{
                echo outputError(array("msg" => errorResponse($lang,"Not valid token","الكود غير صالح")));die();
            }
        }
        if( !isset($_POST["id"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter index","الرقم غير صالح")));die();
        }
        if( !isset($_POST["place"]) || empty($_POST["place"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter your place","يجب أدخال مكانك")));die();
        }
        if( !isset($_POST["country"]) || empty($_POST["country"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter your country","يجب أدخال بلدك")));die();
        }
        if( !isset($_POST["area"]) || empty($_POST["area"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter your area","يجب أدخال المنطقه")));die();
        }
        if( !isset($_POST["block"]) || empty($_POST["block"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter your block","يجب أدخال القطعة")));die();
        }
        if( !isset($_POST["street"]) || empty($_POST["street"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter your street","يجب ادخال الشارع")));die();
        }
        if( !isset($_POST["building"]) || empty($_POST["building"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter your building","يجب أدخال المبنى")));die();
        }
        $dataUpdate = [
            "place" => $_POST["place"],
            "country" => $_POST["country"],
            "area" => $_POST["area"],
            "block" => $_POST["block"],
            "street" => $_POST["street"],   
            "avenue" => ( !isset($_POST["avenue"]) || empty($_POST["avenue"]) ) ? "" : $_POST["avenue"],
            "building" => $_POST["building"],
            "floor" => ( !isset($_POST["floor"]) || empty($_POST["floor"]) ) ? "" : $_POST["floor"],
            "apartment" => ( !isset($_POST["apartment"]) || empty($_POST["apartment"]) ) ? "" : $_POST["apartment"],
            "postalCode" => ( !isset($_POST["postalCode"]) || empty($_POST["postalCode"]) ) ? "" : $_POST["postalCode"],
            "notes" => ( !isset($_POST["notes"]) || empty($_POST["notes"]) ) ? "" : $_POST["notes"],
        ];
        $addresses[$_POST["id"]] = $dataUpdate;
        $addresses = array_values($addresses);
        for( $i = 0; $i < count($addresses); $i++ ){
            $addresses[$i]["id"] = $i;
        }
        if( updateDB("users",array("addresses" => json_encode($addresses)),"`id` = '{$user[0]["id"]}'") ){
            echo outputData(array("msg" => errorResponse($lang,"Your address has been updated successfully","تم تحديث العنوان بنجاح")));die();
        }else{
            echo outputError(array("msg" => errorResponse($lang,"Something went wrong","حدث خطأ ما")));die();
        }
    }else{
        echo outputError(array("msg" => errorResponse($lang,"Wrong action","عملية غير صالحة")));die();
    }
}
?>