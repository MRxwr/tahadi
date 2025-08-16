<?php
if( $Services = selectDB2("`id`,{$titleDB} AS `title`,`imageurl`, {$detailsDB} AS `details`, `whatsappMsg`, `whatsappNumber`","services","`status` = '0' AND `hidden` = '1' ORDER BY `rank` ASC") ){
    for( $i = 0; $i < sizeof($Services); $i++ ){
        $Services[$i]["imageurl"] = "https://coeoapp.com/logos/{$Services[$i]["imageurl"]}";
    }
    $response["services"] = $Services;
}else{
    $response["services"] = array();
}

echo outputData($response);die();
?>