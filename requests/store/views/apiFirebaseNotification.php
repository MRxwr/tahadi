<?php
if( isset($_GET["action"]) & !empty($_GET["action"]) ){
    if( $_GET["action"] == "register" ){
        if( !isset($_POST["deviceToken"]) || empty($_POST["deviceToken"]) ){
            $error["msg"] = errorResponse($lang,"Please enter device token","الرجاء ادخال رمز الجهاز");
            echo outputError($error);die();
        }else{
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://createapi.link/api/v1/register_a_device',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'firebase_json'=> new CURLFILE('../../../coeo-da7c5-firebase-adminsdk-pf16g-32a335b8bb.json'),
                'deviceToken' => "{$_POST["deviceToken"]}",
                'topic' => 'news'
            ), 
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $output["msg"] = errorResponse($lang,"Device has been registered successfully","تم تسجيل الجهاز بنجاح");
            echo outputData($output);die();
        }
    }elseif( $_GET["action"] == "sendNotification" ){
        
        if( !isset($_POST["title"]) || empty($_POST["title"]) ){
            $error["msg"] = errorResponse($lang,"Please enter title","الرجاء ادخال العنوان");
            echo outputError($error);die();
        }
        if( !isset($_POST["body"]) || empty($_POST["body"]) ){
            $error["msg"] = errorResponse($lang,"Please enter body","الرجاء ادخال الوصف");
            echo outputError($error);die();
        }
        if( !isset($_POST["image"]) || empty($_POST["image"]) ){
            $_POST["image"] = "";
        }else{
            /*
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://createapi.link/api/v1/send_to_topic',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'firebase_json'=> new CURLFILE('../../../coeo-da7c5-firebase-adminsdk-pf16g-32a335b8bb.json'),
                'topic' => 'news',
                'title' => "{$_POST["title"]}",
                'body' => "{$_POST["body"]}",
                'image'=> "{$_POST["image"]}"
            ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $output["msg"] = errorResponse($lang,"Notification has been sent successfully","تم ارسال الاشعار بنجاح");
            echo outputData($output);die();
        }
            */
            $notificationData = array(
                "message" => array(
                    "notification" => array( 
                        "title" => "{$_POST["title"]}",
                        "body"  => "{$_POST["body"]}",
                        "image" => "{$_POST["image"]}",
                    )
                )
            );

            $curl = curl_init();
                curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://createapi.link/api/v1/request_token',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array('firebase_json'=> new CURLFILE('../../../coeo-da7c5-firebase-adminsdk-pf16g-32a335b8bb.json')),
                ));
            $response = curl_exec($curl);
            curl_close($curl);
            $bearer = json_decode($response);
            $bearer = $bearer->data->access_token;
            if( $users = selectDB("users", "1=1 AND `firebaseToken` != '' GROUP BY `firebaseToken` ORDER BY `id` ASC") ){
                foreach( $users as $user ){
                    $notificationData["message"]["token"] = $user["firebaseToken"];
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://fcm.googleapis.com/v1/projects/coeo-da7c5/messages:send',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => json_encode($notificationData),
                        CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $bearer
                        ),
                    ));
                    $response = curl_exec($curl);
                    curl_close($curl);
                }
                $output["msg"] = errorResponse($lang,"Notification has been sent successfully","تم ارسال الاشعار بنجاح");
                echo outputData($output);die();
            }
        }
    }elseif( $_GET["action"] == "sendToSingleUser" ){
        if( !isset($_POST["title"]) || empty($_POST["title"]) ){
            $error["msg"] = errorResponse($lang,"Please enter title","الرجاء ادخال العنوان");
            echo outputError($error);die();
        }
        if( !isset($_POST["body"]) || empty($_POST["body"]) ){
            $error["msg"] = errorResponse($lang,"Please enter body","الرجاء ادخال الوصف");
            echo outputError($error);die();
        }
        if( !isset($_POST["image"]) || empty($_POST["image"]) ){
            $_POST["image"] = "";
        }
        
        if( !isset($_POST["userId"]) || empty($_POST["userId"]) ){
            $error["msg"] = errorResponse($lang,"Please enter user","الرجاء ادخال المستخدم");
            echo outputError($error);die();
        }else{
            $notificationData = array(
                "message" => array(
                    "notification" => array( 
                        "title" => "{$_POST["title"]}",
                        "body"  => "{$_POST["body"]}",
                        "image" => "{$_POST["image"]}",
                    )
                )
            );

            $curl = curl_init();
                curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://createapi.link/api/v1/request_token',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array('firebase_json'=> new CURLFILE('../../../coeo-da7c5-firebase-adminsdk-pf16g-32a335b8bb.json')),
                ));
            $response = curl_exec($curl);
            curl_close($curl);
            $bearer = json_decode($response);
            $bearer = $bearer->data->access_token;
            if( $users = selectDB("users", "`id` = '{$_POST["userId"]}'") ){
                $notificationData["message"]["token"] = $users[0]["firebaseToken"];
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://fcm.googleapis.com/v1/projects/coeo-da7c5/messages:send',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode($notificationData),
                    CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $bearer
                    ),
                ));
                $response = curl_exec($curl);
                curl_close($curl);
                echo outputData(array("msg" => errorResponse($lang,"Notification has been sent successfully","تم ارسال الاشعار بنجاح"), "response" => $response));die();
            }
        }
    }
}
?>