<?php
if( isset($_GET["action"]) && !empty($_GET["action"]) ){
    $data = $_POST;
    if( $_GET["action"] == "register" ){
        if( !isset($data["fName"]) || empty($data["fName"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter your first name","يجب إدخال الإسم الأول")));die();
        }
        if( !isset($data["lName"]) || empty($data["lName"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter your last name","يجب إدخال الأخير")));die();
        }
        if( !isset($data["email"]) || empty($data["email"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter your email","يجب أدخال البريد الألكتروني")));die();
        }elseif( selectDBNew("users",[$data["email"]],"`email` = ?","") ){
            echo outputError(array("msg" => errorResponse($lang,"Email is already registered","البريد الألكتروني مسجل مسبقا")));die();
        }
        if( !isset($data["password"]) || empty($data["password"]) ){
            if( $data["registrationType"] == 2 ){
                if( !isset($data["registrationToken"]) || empty($data["registrationToken"]) ){
                    echo outputError(array("msg" => errorResponse($lang,"Please enter Registration token ","يجب أدخال كود التسجيل")));die();
                }
                $data["password"] = "createCoeo123";
                goto jump;
            }
            echo outputError(array("msg" => errorResponse($lang,"Please enter your password","يجب أدخال كلمة المرور")));die();
        }
        if( !isset($data["confirmPassword"]) || empty($data["confirmPassword"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter your password Confirmation","يجب أدخال تأكيد كلمة المرور ")));die();
        }
        if( $data["password"] != $data["confirmPassword"] ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter your password correctly","يجب أدخال كلمة المرور بشكل صحيح")));die();
        }
        jump:
        if( !isset($data["phone"]) || empty($data["phone"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter your phone number","يجب أدخال رقم الهاتف")));die();
        }elseif( selectDBNew("users",[$data["phone"]],"`phone` = ?","") ){
            echo outputError(array("msg" => errorResponse($lang,"Phone number is already registered","رقم الهاتف مسجل مسبقا")));die();
        }
        if( !isset($data["countryCode"]) || empty($data["countryCode"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter your country code","يجب أدخال رمز البلد")));die();
        }
        if( !isset($data["registrationType"]) || empty($data["registrationType"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter Registration type ","يجب أدخال نوع التسجيل")));die();
        }
        if( $data["registrationType"] == 2 ){
            if( !isset($data["registrationToken"]) || empty($data["registrationToken"]) ){
                echo outputError(array("msg" => errorResponse($lang,"Please enter Registration token ","يجب أدخال كود التسجيل")));die();
            }
        }
        if( !isset($data["firebaseToken"]) || empty($data["firebaseToken"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter firebase token ","يجب أدخال كود الفايربيز")));die();
        }
        unset($data["confirmPassword"]);
        $data["password"] = sha1($data["password"]);
        if( insertDB("users",$data) ){
            $user = selectDB("users","`phone` = '{$data["phone"]}' ORDER BY id DESC LIMIT 1");
            $userToken = generateRandomToken();
            updateDB("users",array("keepMeAlive" => "{$userToken}","firebaseToken" => "{$data["firebaseToken"]}"),"`id` = '{$user[0]["id"]}'");
            $curl = curl_init();
                curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://tahadi.createkuwait.com/requests/store/index.php?a=FirebaseNotification&action=register',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array('deviceToken' => "{$data["firebaseToken"]}",),
                ));
            $response = curl_exec($curl);
            curl_close($curl);
            echo outputData(array("msg" => errorResponse($lang,"Registered successfully","تم التسجيل بنجاح"),"token" => $userToken));die();
        }else{
            echo outputError(array("msg" => errorResponse($lang,"Could not register you","لم نستطع تسجيلك")));die();
        }
    }elseif( $_GET["action"] == "login" ){
        if( !isset($data["firebaseToken"]) || empty($data["firebaseToken"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter firebase token ","يجب أدخال كود الفايربيز")));die();
        }
        if( !isset($data["registrationType"]) || empty($data["registrationType"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter login type ","يجب أدخال نوع التسجيل")));die();
        }elseif( $data["registrationType"] == 1 ){
            if( !isset($data["email"]) || empty($data["email"]) ){
                echo outputError(array("msg" => errorResponse($lang,"Please enter your email","يجب أدخال البريد الألكتروني")));die();
            }
            if( !isset($data["password"]) || empty($data["password"]) ){
                echo outputError(array("msg" => errorResponse($lang,"Please enter your password","يجب أدخال كلمة المرور")));die();
            }
            if( selectDBNew("users",[$data["email"]],"`email` = ?","") ){
                if( $user = selectDBNew("users",[$data["email"],sha1($data["password"])],"`email` = ? AND `password` = ?","") ){
                    if( $user[0]["registrationType"] == 3 ){
                        echo outputError(array("msg" => errorResponse($lang,"This is a guest account, you can't login","هذا حساب غير مسجل، لا يمكنك تسجيل الدخول")));die();
                    }
                    if( $user[0]["status"] == 0 ){
                        if( $user[0]["hidden"] == 0 ){
                            $userToken = generateRandomToken();
                            updateDB("users",array("keepMeAlive" => "{$userToken}","firebaseToken" => "{$data["firebaseToken"]}"),"`id` = '{$user[0]["id"]}'");
                            $curl = curl_init();
                                curl_setopt_array($curl, array(
                                CURLOPT_URL => 'https://tahadi.createkuwait.com/requests/store/index.php?a=FirebaseNotification&action=register',
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_ENCODING => '',
                                CURLOPT_MAXREDIRS => 10,
                                CURLOPT_TIMEOUT => 0,
                                CURLOPT_FOLLOWLOCATION => true,
                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                CURLOPT_CUSTOMREQUEST => 'POST',
                                CURLOPT_POSTFIELDS => array('deviceToken' => "{$data["firebaseToken"]}",),
                                ));
                            $response = curl_exec($curl);
                            curl_close($curl);
                            echo outputData(array("msg" => errorResponse($lang,"Logged in successfully","تم تسجيل الدخول بنجاح"),"token" => $userToken));die();
                        }else{
                            echo outputError(array("msg" => errorResponse($lang,"Your account has been blocked","تم حظر حسابك")));die();
                        }
                    }else{
                        echo outputError(array("msg" => errorResponse($lang,"Email is not registered","البريد الألكتروني غير مسجل")));die();
                    }
                }else{
                    echo outputError(array("msg" => errorResponse($lang,"Password is incorrect, please try again","كلمة المرور غير صحيحة، الرجاء المحاولة مرة أخرى")));die();
                }
            }else{
                echo outputError(array("msg" => errorResponse($lang,"Email is not registered","البريد الألكتروني غير مسجل")));die();
            }
        }elseif( $data["registrationType"] == 2 ){
            if( !isset($data["registrationToken"]) || empty($data["registrationToken"]) ){
                echo outputError(array("msg" => errorResponse($lang,"Please enter Registration token ","يجب أدخال كود التسجيل")));die();
            }
            if( $user = selectDBNew("users",[$data["registrationToken"]],"`registrationToken` = ?","") ){
                $userToken = generateRandomToken();
                updateDB("users",array("keepMeAlive" => "{$userToken}","firebaseToken" => "{$data["firebaseToken"]}"),"`id` = '{$user[0]["id"]}'");
                $curl = curl_init();
                    curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://tahadi.createkuwait.com/requests/store/index.php?a=FirebaseNotification&action=register',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => array('deviceToken' => "{$data["firebaseToken"]}",),
                    ));
                $response = curl_exec($curl);
                curl_close($curl);
                echo outputData(array("msg" => errorResponse($lang,"Logged in successfully","تم تسجيل الدخول بنجاح"),"token" => $userToken));die();
            }else{
                echo outputError(array("msg" => errorResponse($lang,"Account not found, Please sign up first.","حساب غير موجود، الرجاء التسجيل اولا")));die();
            }
        }elseif( $data["registrationType"] == 3 ){
            $guestData = array(
                "firebaseToken" => $data["firebaseToken"],
                "keepMeAlive" => generateRandomToken(),
                "registrationType" => 3,
                "phone" => "",
                "password" => sha1("coeo1234"),
                "email" => "guest-".date("YmdHis").time()."@tahadi.createkuwait.com",
                "fName" => "guest-".date("YmdHis").time(),
                "lName" => "",
                "countryCode" => "965",
            );
            if( insertDB("users",$guestData) ){
                $curl = curl_init();
                    curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://tahadi.createkuwait.com/requests/store/index.php?a=FirebaseNotification&action=register',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => array('deviceToken' => "{$data["firebaseToken"]}",),
                    ));
                $response = curl_exec($curl);
                curl_close($curl);
                echo outputData(array("msg" => errorResponse($lang,"Logged in successfully","تم تسجيل الدخول بنجاح"),"token" => $guestData["keepMeAlive"]));die();
            }else{
                echo outputError(array("msg" => errorResponse($lang,"Could not register you","لم نستطع تسجيلك")));die();
            }
        }
    }elseif( $_GET["action"] == "forgot" ){
        if( !isset($data["email"]) || empty($data["email"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter your email","يجب أدخال البريد الألكتروني")));die();
        }
        if( $user = selectDBNew("users",[$data["email"]],"`email` = ?","") ){
            if( $user[0]["status"] == 0 ){
                if( $user[0]["hidden"] == 0 ){
                    $newPassword = rand(00000000,99999999);
                    $newData = array("password" => $newPassword,"email" => $data["email"]);
                    if ( forgetPass($newData) ){
                        updateDB("users",array("password" => sha1($newPassword)),"`id` = '{$user[0]["id"]}'");
                        echo outputData(array("msg" => errorResponse($lang,"Your password has been reset, Please check your email","تم إعادة تعيين كلمة المرور الخاصة بك، يرجى التحقق من بريدك الألكتروني")));die();
                    }else{
                        echo outputError(array("msg" => errorResponse($lang,"Something went wrong, please try again","حدث خطأ ما، الرجاء المحاولة مرة أخرى")));die();
                    }
                }else{
                    echo outputError(array("msg" => errorResponse($lang,"Your account has been blocked","تم حظر حسابك")));die();
                }
            }else{
                echo outputError(array("msg" => errorResponse($lang,"Email is not registered","البريد الألكتروني غير مسجل")));die();
            }
        }else{
            echo outputError(array("msg" => errorResponse($lang,"Email is not registered","البريد الألكتروني غير مسجل")));die();
        }
    }elseif( $_GET["action"] == "reset" ){
        if( !isset($data["password"]) || empty($data["password"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter your password","يجب أدخال كلمة المرور")));die();
        }
        if( !isset($data["confirmPassword"]) || empty($data["confirmPassword"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter your password Confirmation","يجب أدخال تأكيد كلمة المرور ")));die();
        }
        if( $data["password"] != $data["confirmPassword"] ){
            echo outputError(array("msg" => errorResponse($lang,"Please enter your password correctly","يجب أدخال كلمة المرور بشكل صحيح")));die();
        }
        if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
            updateDB("users",array("password" => sha1($data["password"])),"`id` = '{$user[0]["id"]}'");
            echo outputData(array("msg" => errorResponse($lang,"Your password has been reset successfully","تم إعادة تعيين كلمة المرور الخاصة بك بنجاح")));die();
        }else{
            echo outputError(array("msg" => errorResponse($lang,"Not valid token","الكود غير صالح")));die();
        }
    }elseif( $_GET["action"] == "profile" ){
        if( !isset($_POST["update"]) ){
            echo outputError(array("msg" => errorResponse($lang,"Please add update to your body","يجب إضافة update للطلب")));die();
        }
        if( $_POST["update"] == 0 ){
            if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
                $response = array(
                    "fName" => $user[0]["fName"],
                    "lName" => $user[0]["lName"],
                    "email" => $user[0]["email"],
                    "phone" => $user[0]["phone"],
                    "countryCode" => $user[0]["countryCode"],
                    "image" => $user[0]["image"],
                );
                echo outputData(array($response));die();
            }else{
                echo outputError(array("msg" => errorResponse($lang,"Not valid token","الكود غير صالح")));die();
            }
        }else{
            if( !isset($data["fName"]) || empty($data["fName"]) ){
                echo outputError(array("msg" => errorResponse($lang,"Please enter your first name","يجب إدخال الإسم الأول")));die();
            }
            if( !isset($data["lName"]) || empty($data["lName"]) ){
                echo outputError(array("msg" => errorResponse($lang,"Please enter your last name","يجب إدخال الأخير")));die();
            }
            if( !isset($data["email"]) || empty($data["email"]) ){
                echo outputError(array("msg" => errorResponse($lang,"Please enter your email","يجب أدخال البريد الألكتروني")));die();
            }
            if( !isset($data["phone"]) || empty($data["phone"]) ){
                echo outputError(array("msg" => errorResponse($lang,"Please enter your phone number","يجب أدخال رقم الهاتف")));die();
            }
            if( !isset($data["countryCode"]) || empty($data["countryCode"]) ){
                echo outputError(array("msg" => errorResponse($lang,"Please enter your country code","يجب أدخال رمز البلد")));die();
            }
            if( isset($_FILES["image"]['tmp_name']) && is_uploaded_file($_FILES["image"]['tmp_name']) ){
                $data["image"] = uploadImageFreeImageHostAPI($_FILES["image"]['tmp_name'],"profiles");
                if( empty($data["image"]) ){
                    echo outputError(array("msg" => errorResponse($lang,"Image upload failed","فشل تحميل الصورة")));die();
                }else{
                    $data["image"] = "profiles/{$data["image"]}";
                }
            }
            unset($data["update"]);
            if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
                updateDB("users",$data,"`id` = '{$user[0]["id"]}'");
                echo outputData(array("msg" => errorResponse($lang,"Your profile has been updated successfully","تم تحديث الملف الشخصي بنجاح")));die();
            }else{
                echo outputError(array("msg" => errorResponse($lang,"Not valid token","الكود غير صالح")));die();
            }
        }
    }elseif( $_GET["action"] == "delete" ){
        if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
            updateDB("users",array("phone" => "deleted - {$user[0]["phone"]}","email" => "deleted - {$user[0]["email"]}","status" => 1,"keepMeAlive" => ""),"`id` = '{$user[0]["id"]}'");
            echo outputData(array("msg" => errorResponse($lang,"Your account has been deleted successfully","تم حذف حسابك بنجاح")));die();
        }else{
            echo outputError(array("msg" => errorResponse($lang,"Not valid token","الكود غير صالح")));die();
        }
    }elseif( $_GET["action"] == "logout" ){
        if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
            updateDB("users",array("keepMeAlive" => ""),"`id` = '{$user[0]["id"]}'");
            echo outputData(array("msg" => errorResponse($lang,"Logged out successfully","تم تسجيل الخروج بنجاح")));die();
        }else{
            echo outputError(array("msg" => errorResponse($lang,"Not valid token","الكود غير صالح")));die();
        }
    }elseif( $_GET["action"] == "wallet" ){
        if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
            echo outputData(array("wallet" => $user[0]["wallet"]));die();
        }else{
            echo outputError(array("msg" => errorResponse($lang,"Not valid token","الكود غير صالح")));die();
        }
    }elseif( $_GET["action"] == "notifications" ){
        if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
            if( $notifications = selectDB2New("`date`, `title`, `body`, `image`, `status`","notifications",[$user[0]["id"]],"`userId` = ? ORDER BY `id` DESC","") ){
                updateDB("notifications",array("status" => 1),"`userId` = '{$user[0]["id"]}'");
                echo outputData(array("notifications" => $notifications));die();
            }else{
                echo outputError(array("msg" => errorResponse($lang,"No notifications found","لم يتم العثور على الاشعارات")));die();
            }
        }else{
            echo outputError(array("msg" => errorResponse($lang,"Not valid token","الكود غير صالح")));die();
        }
    }elseif( $_GET["action"] == "getOTP" ){
        if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
            $otp = rand(100000, 999999);
            if( updateDB("users",array("otp" => $otp),"`id` = '{$user[0]["id"]}'") ){
                whatsappUltraMsgOTP($user[0]["id"], $otp);
                echo outputData(array("msg" => errorResponse($lang,"OTP sent successfully","تم ارسال OTP بنجاح")));die();
            }else{
                echo outputError(array("msg" => errorResponse($lang,"Not valid token","الكود غير صالح")));die();
            }
        }else{
            echo outputError(array("msg" => errorResponse($lang,"Not valid token","الكود غير صالح")));die();
        }
    }elseif( $_GET["action"] == "verifyOTP" ){
        if( !isset($_POST["otp"]) || empty($_POST["otp"]) ){
            echo outputError(array("msg" => errorResponse($lang,"OTP is required","OTP مطلوب")));die();
        }
        if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
            if( $user[0]["otp"] == $_POST["otp"] ){
                updateDB("users",array("otp" => "", "isVerified" => 1),"`id` = '{$user[0]["id"]}'");
                echo outputData(array("msg" => errorResponse($lang,"OTP verified successfully","تم التحقق من OTP بنجاح")));die();
            }else{
                echo outputError(array("msg" => errorResponse($lang,"Invalid OTP","OTP غير صالح")));die();
            }
        }else{
            echo outputError(array("msg" => errorResponse($lang,"Not valid token","الكود غير صالح")));die();

        }
    }else{
        echo outputError(array("msg" => "404 action not available"));die();
    }
}
?>