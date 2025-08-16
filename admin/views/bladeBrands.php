<?php 
if( isset($_GET["hide"]) && !empty($_GET["hide"]) ){
	if( updateDB("brands",array('hidden'=> '2'),"`id` = '{$_GET["hide"]}'") ){
		header("LOCATION: ?v=Brands");
	}
}

if( isset($_GET["show"]) && !empty($_GET["show"]) ){
	if( updateDB("brands",array('hidden'=> '1'),"`id` = '{$_GET["show"]}'") ){
		header("LOCATION: ?v=Brands");
	}
}

if( isset($_GET["delId"]) && !empty($_GET["delId"]) ){
	if( updateDB("brands",array('status'=> '1'),"`id` = '{$_GET["delId"]}'") ){
		header("LOCATION: ?v=Brands");
	}
}

if( isset($_POST["updateRank"]) ){
	for( $i = 0; $i < sizeof($_POST["rank"]); $i++){
		updateDB("brands",array("rank"=>$_POST["rank"][$i]),"`id` = '{$_POST["id"][$i]}'");
	}
	header("LOCATION: ?v=Brands");
}

if( isset($_POST["arTitle"]) ){
	$id = $_POST["update"];
	unset($_POST["update"]);
	if ( $id == 0 ){
		if (is_uploaded_file($_FILES['imageurl']['tmp_name'])) {
			$ext = pathinfo($_FILES['imageurl']['name'], PATHINFO_EXTENSION);
			$_POST["imageurl"] = uploadImageBannerFreeImageHost($_FILES['imageurl']['tmp_name']);
			$_POST["imageurl2"] = uploadImageBannerFreeImageHost($_FILES['imageurl']['tmp_name']);
		} else {
			$_POST["imageurl"] = "";
			$_POST["imageurl2"] = "";
		}
		
		if (is_uploaded_file($_FILES['header']['tmp_name'])) {
			$ext = pathinfo($_FILES['header']['name'], PATHINFO_EXTENSION);
			$_POST["header"] = uploadImageBannerFreeImageHost($_FILES['header']['tmp_name']);
		} else {
			$_POST["header"] = "";
		}
		
		
		if( insertDB("brands", $_POST) ){
			header("LOCATION: ?v=Brands");
		}else{
		?>
		<script>
			alert("Could not process your request, Please try again.");
		</script>
		<?php
		}
	}else{
		if (is_uploaded_file($_FILES['imageurl']['tmp_name'])) {
			$ext = pathinfo($_FILES['imageurl']['name'], PATHINFO_EXTENSION);
			$_POST["imageurl"] = uploadImageBannerFreeImageHost($_FILES['imageurl']['tmp_name']);
			$_POST["imageurl2"] = uploadImageBannerFreeImageHost($_FILES['imageurl']['tmp_name']);
		} else {
			unset($_POST["imageurl"]);
			unset($_POST["imageurl2"]);
		}
		
		if (is_uploaded_file($_FILES['header']['tmp_name'])) {
			$ext = pathinfo($_FILES['header']['name'], PATHINFO_EXTENSION);
			$_POST["header"] = uploadImageBannerFreeImageHost($_FILES['header']['tmp_name']);
		} else {
			unset($_POST["header"]);
		}
		
		if( updateDB("brands", $_POST, "`id` = '{$id}'") ){
			header("LOCATION: ?v=Brands");
		}else{
		?>
		<script>
			alert("Could not process your request, Please try again.");
		</script>
		<?php
		}
	}
}
?>
<div class="row">
<div class="col-sm-12">
<div class="panel panel-default card-view">
<div class="panel-heading">
<div class="pull-left">
	<h6 class="panel-title txt-dark"><?php echo direction("Brand Details","تفاصيل القسم") ?></h6>
</div>
	<div class="clearfix"></div>
</div>
<div class="panel-wrapper collapse in">
<div class="panel-body">
	<form class="" method="POST" action="" enctype="multipart/form-data">
		<div class="row m-0">
			<div class="col-md-4">
			<label><?php echo direction("Arabic Title","العنوان بالعربي") ?></label>
			<input type="text" name="arTitle" class="form-control" required>
			</div>
			
			<div class="col-md-4">
			<label><?php echo direction("English Title","العنوان بالإنجليزي") ?></label>
			<input type="text" name="enTitle" class="form-control" required>
			</div>
			
			<div class="col-md-4">
			<label><?php echo direction("Hide Brand","أخفي القسم") ?></label>
			<select name="hidden" class="form-control">
				<option value="1">No</option>
				<option value="2">Yes</option>
			</select>
			</div>
			
			<div class="col-md-6">
			<label><?php echo direction("Logo","الشعار") ?></label>
			<input type="file" name="imageurl" class="form-control" >
			</div>
			
			<div class="col-md-6">
			<label><?php echo direction("Header","الصورة الكبيرة") ?></label>
			<input type="file" name="header" class="form-control" >
			</div>
			
			<div id="images" style="margin-top: 10px; display:none">
				<div class="col-md-6">
				<img id="logoImg" src="" style="width:250px;height:250px">
				</div>
				
				<div class="col-md-6">
				<img id="headerImg" src="" style="width:250px;height:250px">
				</div>
			</div>
			
			
			<div class="col-md-6" style="margin-top:10px">
			<input type="submit" class="btn btn-primary" value="<?php echo direction("Submit","أرسل") ?>">
			<input type="hidden" name="update" value="0">
			</div>
		</div>
	</form>
</div>
</div>
</div>
</div>
				
				<!-- Bordered Table -->
<form method="post" action="">
<input name="updateRank" type="hidden" value="1">
<div class="col-sm-12">
<div class="panel panel-default card-view">
<div class="panel-heading">
<div class="pull-left">
<h6 class="panel-title txt-dark"><?php echo direction("Brands List","قائمة الماركات") ?></h6>
</div>
<div class="clearfix"></div>
</div>
<div class="panel-wrapper collapse in">
<div class="panel-body">
<button class="btn btn-primary">
<?php echo direction("Submit rank","أرسل الترتيب") ?>
</button>  
<div class="table-wrap mt-40">
<div class="table-responsive">
	<table class="table display responsive product-overview mb-30" id="myTable">
		<thead>
		<tr>
		<th>#</th>
		<th><?php echo direction("Logo","الشعار") ?></th>
		<th><?php echo direction("English Title","العنوان بالإنجليزي") ?></th>
		<th><?php echo direction("Arabic Title","العنوان بالعربي") ?></th>
		<th class="text-nowrap"><?php echo direction("Actions","الخيارات") ?></th>
		</tr>
		</thead>
		
		<tbody>
		<?php 
		if( $brands = selectDB("brands","`status` = '0' ORDER BY `rank` ASC") ){
			for( $i = 0; $i < sizeof($brands); $i++ ){
				$counter = $i + 1;
			if ( $brands[$i]["hidden"] == 2 ){
				$icon = "fa fa-eye";
				$link = "?v={$_GET["v"]}&show={$brands[$i]["id"]}";
				$hide = direction("Show","إظهار");
			}else{
				$icon = "fa fa-eye-slash";
				$link = "?v={$_GET["v"]}&hide={$brands[$i]["id"]}";
				$hide = direction("Hide","إخفاء");
			}
			?>
			<tr>
			<td>
			<input name="rank[]" class="form-control" type="number" value="<?php echo $counter ?>">
			<input name="id[]" class="form-control" type="hidden" value="<?php echo $brands[$i]["id"] ?>">
			</td>
			<td><img src="../logos/<?php echo $brands[$i]["imageurl2"] ?>" style="width: 75px; height: 75px;"></td>
			<td id="enTitle<?php echo $brands[$i]["id"]?>" ><?php echo $brands[$i]["enTitle"] ?></td>
			<td id="arTitle<?php echo $brands[$i]["id"]?>" ><?php echo $brands[$i]["arTitle"] ?></td>
			<td class="text-nowrap">
			
			<a id="<?php echo $brands[$i]["id"] ?>" class="mr-25 edit" data-toggle="tooltip" data-original-title="<?php echo direction("Edit","تعديل") ?>"> <i class="fa fa-pencil text-inverse m-r-10"></i>
			</a>
			<a href="<?php echo $link ?>" class="mr-25" data-toggle="tooltip" data-original-title="<?php echo $hide ?>"> <i class="<?php echo $icon ?> text-inverse m-r-10"></i>
			</a>
			<a href="<?php echo "?v={$_GET["v"]}&delId={$brands[$i]["id"]}" ?>" data-toggle="tooltip" data-original-title="<?php echo direction("Delete","حذف") ?>"><i class="fa fa-close text-danger"></i>
			</a>
			<div style="display:none"><label id="hidden<?php echo $brands[$i]["id"]?>"><?php echo $brands[$i]["hidden"] ?></label></div>
			<div style="display:none"><label id="logo<?php echo $brands[$i]["id"]?>"><?php echo $brands[$i]["imageurl"] ?></label></div>
			<div style="display:none"><label id="header<?php echo $brands[$i]["id"]?>"><?php echo $brands[$i]["header"] ?></label></div>
			
			</td>
			</tr>
			<?php
			}
		}
		?>
		</tbody>
		
	</table>
</div>
</div>
</div>
</div>
</div>
</div>
</form>
</div>
<script>
	$(document).on("click",".edit", function(){
		var id = $(this).attr("id");
		var arTitle = $("#arTitle"+id).html();
		var enTitle = $("#enTitle"+id).html();
		var hidden = $("#hidden"+id).html();
		var logo = $("#logo"+id).html();
		var header = $("#header"+id).html();
		$("input[type=file]").prop("required",false);
		$("input[name=arTitle]").val(arTitle).focus();
		$("input[name=update]").val(id);
		$("input[name=enTitle]").val(enTitle);
		$("select[name=hidden]").val(hidden);
		$("#headerImg").attr("src","../logos/"+header);
		$("#logoImg").attr("src","../logos/"+logo);
		$("#images").attr("style","margin-top:10px;display:block");
	})
</script>