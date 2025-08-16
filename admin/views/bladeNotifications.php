<?php 
if( isset($_POST["title"]) ){
    if( is_uploaded_file($_FILES['image']['tmp_name']) ){
        $_POST["image"] = uploadImageBannerFreeImageHost($_FILES['image']['tmp_name']);
    }else{
        $_POST["image"] = "";
    }
    $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://coeoapp.com/requests/store/?endpoint=FirebaseNotification&language=en&action=sendNotification',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('title' => "{$_POST["title"]}",'body' => "{$_POST["body"]}",'image' => "{$_POST["image"]}"),
        ));
    $response = curl_exec($curl);
    curl_close($curl);
    $response = json_decode($response,true);
    var_dump($response);

    if( $response["ok"] == true ){
        echo "<script>alert('Notification sent successfully!');</script>";
    }else{
        echo "<script>alert('Failed to send notification!');</script>";
    }
    ?>
    <script>window.location.href = '?v=Notifications';</script>
    <?php
}
?>
<div class="row">			
<div class="col-sm-12">
<div class="panel panel-default card-view">
<div class="panel-heading">
<div class="pull-left">
	<h6 class="panel-title txt-dark"><?php echo direction("Notification Details","تفاصيل الاشعار") ?></h6>
</div>
	<div class="clearfix"></div>
</div>
<div class="panel-wrapper collapse in">
<div class="panel-body">
	<form class="" method="POST" action="" enctype="multipart/form-data">
		<div class="row m-0">
			<div class="col-md-4">
			<label><?php echo direction("Title","العنوان") ?></label>
			<input type="text" name="title" class="form-control" required>
			</div>
			
			<div class="col-md-4">
			<label><?php echo direction("Message","الرسالة") ?></label>
			<input type="text" name="body" class="form-control" required>
			</div>
			
			<div class="col-md-4">
			<label><?php echo direction("Image","صورة") ?></label>
			<input type="file" name="image" class="form-control" >
			</div>
			
			<div class="col-md-12" style="margin-top:10px">
			<input type="submit" class="btn btn-primary" value="<?php echo direction("Send","ارسل") ?>">
			<input type="hidden" name="update" value="0">
			</div>
		</div>
	</form>
</div>
</div>
</div>
</div>
				
</div>