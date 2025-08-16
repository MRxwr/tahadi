<?php
/*
if ( isset($_GET["action"]) && !empty($_GET["action"]) ){
    if( $_GET["action"] == "list" ){
        if( $images = selectDB2("imageurl, id","images","`imageurl` != '' AND `imageurl2` = '' AND `id` >= '588' GROUP BY `productId` ORDER BY `id` ASC") ){
            for( $i = 0; $i < sizeof($images); $i++ ){
                $images[$i]["imageurl"] = "https://coeoapp.com/logos/{$images[$i]["imageurl"]}";
            }
            $response["images"] = $images;
        }else{
            $response["images"] = array();
        }
        echo outputData($response);die();
    }elseif( $_GET["action"] == "update" ){
        if( isset($_GET["link"]) && !empty($_GET["link"]) && isset($_GET["imageId"]) && !empty($_GET["imageId"]) ){
            updateDB("images",array("imageurl2" => "{$_GET['link']}"),"`id` = '{$_GET["imageId"]}'");
            $response["msg"] = "Image updated";
            echo outputData($response);die();
        }else{
            $response["msg"] = "Error updating image";
            echo outputError($response);die();
        }
    }
}else{
    $response["msg"] = "Error";
    echo outputError($response);die();
}
*/
/*
function uploadImageUR($url){
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api.imgur.com/3/upload',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => array('image' => "{$url}",'type' => 'url'),
      CURLOPT_HTTPHEADER => array(
        'Authorization: Client-ID 386563124e58e6c'
      ),
    ));
    $response = curl_exec($curl);
    $response = json_decode($response,true);
    curl_close($curl);
    if( isset($response['data']['link']) ){
        return $response['data']['link'];
    }else{
        return false;
    }
}


if( $images = selectDB2("imageurl, id","images","`imageurl` != '' AND `imageurl2` = '' AND `id` >= 588 GROUP BY `productId` ORDER BY `id` DESC") ){
    foreach($images as $imageToBeUploaded){
        if ( $image = uploadImageUR("https://coeoapp.com/logos/{$imageToBeUploaded['imageurl']}") ){
            if( $image ){
                updateDB("images",array("imageurl2" => "{$image}",),"`id` = '{$imageToBeUploaded['id']}'");
            }else{
                echo outputError(array("msg" => "Error uploading image"));die();
            }
        }else{
            echo outputError(array("msg" => "Error getting image"));die();
        }
        sleep(2);
    }
}
*/

function uploadImageToServer($imageLocation){
	if( isset($imageLocation) && !empty($imageLocation) ){
		$imageSizes = [""];//,"b","m"];
		for( $i = 0; $i < sizeof($imageSizes); $i++ ){
			// Your file
			$file = $imageLocation;
			$newFile = str_lreplace(".","{$imageSizes[0]}.",$file);
			//get File Name
			$fileTitle = str_replace("https://i.imgur.com/","",$newFile);
			$fileTitle = str_replace("{$imageSizes[0]}.",".",$fileTitle);
			// Open the file to get existing content
			$data = file_get_contents($newFile);
			// New file
			$new = "../../logos/{$imageSizes[0]}".$fileTitle;
			// Write the contents back to a new file
			file_put_contents($new, $data);
		}
		return $fileTitle; 
	}else{
		return false;
	}
}

if( $images = selectDB2("imageurl2, id","images","`imageurl2` != '' AND `imageurl3` = '' ORDER BY `id` ASC") ){
    foreach($images as $imageToBeUploaded){
        if ( $image = uploadImageToServer("{$imageToBeUploaded['imageurl2']}") ){
            if( $image ){
                updateDB("images",array("imageurl3" => "{$image}",),"`id` = '{$imageToBeUploaded['id']}'");
            }else{
                echo outputError(array("msg" => "Error uploading image"));die();
            }
        }else{
            echo outputError(array("msg" => "Error getting image"));die();
        }
        sleep(2);
    }
}
?>