<?php
if( $settings = selectDB2("`{$aboutDB}` as about, `{$termsDB}` as terms, `{$policyDB}` as policy, `version`","settings","`id` = '1'") ){
    if( empty($token) ){
        $settings[0]["wallet"] = numTo3Float(0);
        $settings[0]["userLogo"] = "";
    }else{
        if( $user = selectDBNew("users",[$token],"`keepMeAlive` = ?","") ){
            $settings[0]["wallet"] = numTo3Float($user[0]["wallet"]);
            $settings[0]["userLogo"] = $user[0]["image"];
        }else{
            $settings[0]["wallet"] = numTo3Float(0);
            $settings[0]["userLogo"] = "";
        }
    }
    echo outputData($settings);die();
}else{
    echo outputError(array("msg" => errorResponse($lang,"No settings found","لم يتم العثور على اعدادات") ));die();
}
?>