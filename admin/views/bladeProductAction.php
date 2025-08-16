<?php
if ( isset($_GET["imgdel"]) ){
	deleteDB("images","`id` = '{$_GET["imgdel"]}'");
}
if ( isset($_GET["id"]) AND !empty($_GET["id"]) && $product = selectDB("products","`id` = {$_GET["id"]}") ){
	$product = $product[0];
	$price = 0;
	$cost = 0;
	$quantity = 0;
	$sku = 0;
	if ( $product["type"] == 1 ){
		$productAttr = selectDB("attributes_products","`productId` = {$_GET["id"]} AND `hidden` LIKE '0' ORDER BY `id` DESC");
		if( sizeof($productAttr) > 0 ){
			$row = $productAttr[0];
			$price = $row["price"];
			$cost = $row["cost"];
			$quantity = $row["quantity"];
			$sku = $row["sku"];
		}
	}
	$actionLink = "includes/products/edit.php?id={$_GET["id"]}";
	$typeDisabled = "disabled";
}else{
	$actionLink = "includes/products/add.php";
	$typeDisabled = "";
	$price = 0;
	$cost = 0;
	$quantity = 0;
	$sku = 0;
}
?>
<div class="row">
<div class="col-sm-12">
<div class="panel panel-default card-view">
<div class="panel-wrapper collapse in">
<div class="panel-body">
<div class="form-wrap">
<form action="<?php echo $actionLink ?>" method="POST" enctype="multipart/form-data">
<input name="onlineQuantity" type="hidden" class="form-control" value="<?php echo $onlineQuantity = (isset($product["onlineQuantity"])) ? $product["onlineQuantity"] : 0; ?>">
<h6 class="txt-dark capitalize-font"><i class="zmdi zmdi-info-outline mr-10"></i><?php echo direction("Product Details","تفاصيل المنتج") ?></h6>
<hr class="light-grey-hr"/>
<div class="row">

<div class="col-md-12">
	<div class="form-group">
		<label class="control-label mb-10"><?php echo direction("Product Type","نوع المنتج")?></label>
		<select name="type" class="form-control" <?php echo $typeDisabled ?>>
		<?php
		$simpleText = direction("Simple","بسيط");
		$variantText = direction("Variant","متغير");
		if( isset($product["type"]) && $product["type"] == 1 ){
			echo "<option value='1'>{$simpleText}</option><option value='0'>{$variantText}</option>";
		}else{
			echo "<option value='0'>{$variantText}</option><option value='1'>{$simpleText}</option>";
		}
		?>
		</select>
	</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label class="control-label mb-10"><?php echo direction("English Title","العنوان الإنجليزي") ?></label>
<input type="text" name="enTitle" class="form-control" value="<?php echo $enTitle = (isset($product["enTitle"])) ? $product["enTitle"] : ""; ?>">
</div>
</div>

<!--/span-->
<div class="col-md-6">
<div class="form-group">
<label class="control-label mb-10"><?php echo direction("Arabic Title","العنوان العربي") ?></label>
<input type="text" name="arTitle" class="form-control" value="<?php echo $arTitle = (isset($product["arTitle"])) ? $product["arTitle"] : ""; ?>">
</div>
</div>

<div class="col-md-3">
<div class="form-group">
<label class="control-label mb-10"><?php echo direction("Shop","المتجر") ?></label>
<select name="shopId" class="selectpicker" data-style="form-control btn-default btn-outline" required>
<?php
	if( $shops = selectDB("shops", "`status` = '0'") ){
		for( $i = 0; $i < sizeof($shops); $i++ ){
			$checked = "";
			$title = direction($shops[$i]["enTitle"],$shops[$i]["arTitle"]);
			if( $shops[$i]["id"] == $product["shopId"] ){
				$checked = "selected";
			}
			echo "<option value='{$shops[$i]["id"]}' {$checked}>{$title}</option>";
		}
	}
?>
</select>
</div>
</div>

<div class="col-md-3">
<div class="form-group">
<label class="control-label mb-10"><?php echo direction("Category","القسم") ?></label>
<select name="categoryId[]" class="selectpicker" data-style="form-control btn-default btn-outline" multiple required>
<?php
	if( $categories = selectDB("categories", "`status` = '0'") ){
		for( $i = 0; $i < sizeof($categories); $i++ ){
			$checked = "";
			$title = direction($categories[$i]["enTitle"],$categories[$i]["arTitle"]);
			if( isset($_GET["id"]) && selectDB("category_products","`categoryId` = '{$categories[$i]["id"]}' AND `productId` = '{$_GET["id"]}'") ){
				$checked = "selected";
			}
			echo "<option value='{$categories[$i]["id"]}' {$checked}>{$title}</option>";
		}
	}
 
?>
</select>
</div>
</div>

<div class="col-md-3">
<div class="form-group">
<label class="control-label mb-10"><?php echo direction("Brand","الماركه") ?></label>
<select name="brandId" class="selectpicker" data-style="form-control btn-default btn-outline" required>
<?php
	if( $brands = selectDB("brands", "`status` = '0' ORDER BY `rank` ASC") ){
		for( $i = 0; $i < sizeof($brands); $i++ ){
			$checked = "";
			$title = direction($brands[$i]["enTitle"],$brands[$i]["arTitle"]);
			if( $brands[$i]["id"] == $product["brandId"] ){
				$checked = "selected";
			}
			echo "<option value='{$brands[$i]["id"]}' {$checked}>{$title}</option>";
		}
	}

?>
</select>
</div>
</div>

<div class="col-md-3">
<div class="form-group">
<label class="control-label mb-10"><?php echo direction("Add-ons","الإضافات") ?></label>
<select name="extras[]" class="selectpicker" data-style="form-control btn-default btn-outline" multiple>
<?php
	if( $extras = selectDB("extras", "`status` = '0'") ){ 
		for( $i = 0; $i < sizeof($extras); $i++ ){
			$checked = "";
			$title = direction($extras[$i]["enTitle"],$extras[$i]["arTitle"]);
			$productExtras = (isset($product["extras"]) && json_decode($product["extras"],true) ) ? json_decode($product["extras"],true): array() ;
			if( in_array($extras[$i]["id"],$productExtras) ){
				$checked = "selected";
			}
			echo "<option value='{$extras[$i]["id"]}' {$checked}>{$title}</option>";
		}
	}

?>
</select>
</div>
</div>

<div class="col-md-12">
<div class="form-group">
<hr style="border-color:#c7c7c7" >
</div>
</div>

<!--/span-->
</div>
<!-- Row -->
<!-- Row -->
<div class="row">

<div class="col-md-4">
	<div class="form-group">
		<label class="control-label mb-10"><?php echo direction("Pre-Order","طلب مسبق") ?></label>
		<select name="preorder" class="form-control">
		<?php
		$yesText = direction("Yes","نعم");
		$noText = direction("No","لا");
		if( isset($product["preorder"]) && $product["preorder"] == 1 ){
			echo "<option value='1'>{$yesText}</option><option value='0'>{$noText}</option>";
		}else{
			echo "<option value='0'>{$noText}</option><option value='1'>{$yesText}</option>";
		}
		?>
		</select>
	</div>
</div>

<div class="col-md-4">
	<div class="form-group">
		<label class="control-label mb-10"><?php echo direction("English Tag","شعار بالإنجليزي") ?></label>
		<input type="text" name="preorderText" class="form-control" value="<?php echo $preorderText = (isset($product["preorderText"])) ? $product["preorderText"] : ""; ?>">
	</div>
</div>

<div class="col-md-4">
	<div class="form-group">
		<label class="control-label mb-10"><?php echo direction("Arabic Tag","شعار بالعربي") ?></label>
		<input type="text" name="preorderTextAr" class="form-control" value="<?php echo $preorderTextAr = (isset($product["preorderTextAr"])) ? $product["preorderTextAr"] : ""; ?>">
	</div>
</div>

<!--/span-->
<!--/span-->
<div class="hideMeSoon">

<div class="col-md-3">
<div class="form-group">
<label class="control-label mb-10"><?php echo direction("Price","السعر") ?></label>
<div class="input-group">
<div class="input-group-addon"><i class="ti-money"></i></div>
<input name="price" type="float" class="form-control" id="exampleInputuname" value="<?php echo $price ?>">
</div>
</div>
</div>

<div class="col-md-3">
<div class="form-group">
<label class="control-label mb-10"><?php echo direction("Cost","التكلفة") ?></label>
<div class="input-group">
<div class="input-group-addon"><i class="ti-money"></i></div>
<input name="cost" type="float" class="form-control" id="exampleInputuname_1" value="<?php echo $cost ?>">
</div>
</div>
</div>

<div class="col-md-3">
<div class="form-group">
<label class="control-label mb-10"><?php echo direction("Quantity","الكمية") ?></label>
<div class="input-group">
<div class="input-group-addon"><i class="ti-money"></i></div>
<input name="quantity" type="number" class="form-control" id="exampleInputuname_1" value="<?php echo $quantity ?>">
</div>
</div>
</div>

<div class="col-md-3">
<div class="form-group">
<label class="control-label mb-10">SKU</label>
<div class="input-group">
<div class="input-group-addon"><i class="ti-money"></i></div>
<input name="sku" type="text" class="form-control" id="exampleInputuname_1" value="<?php echo $sku ?>">
</div>
</div>
</div>

</div>

<div class="col-md-6">
	<div class="form-group">
		<label class="control-label mb-10"><?php echo direction("Discount Type","نوع الخصم") ?></label>
		<select name="discountType" class="form-control">
		<?php
		if( isset($product["discountType"]) && $product["discountType"] == 1 ){
			echo "<option value='1'>".direction("Fixed","قيمة ثابته")."</option>
			<option value='0'>".direction("Percentage","نسبة مؤوية")."</option>";
		}else{
			echo "<option value='0'>".direction("Percentage","نسبة مؤوية")."</option>
			<option value='1'>".direction("Fixed","قيمة ثابته")."</option>";
		}
		?>
		</select>
	</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label class="control-label mb-10"><?php echo direction("Discount","الخصم") ?></label>
<input name="discount" type="text" name="discount" class="form-control" max="100" min="0" step="1" value="<?php echo $discount = (isset($product["discount"])) ? $product["discount"] : ""; ?>">
</div>
</div>

<div class="col-md-2">
<div class="form-group">
<label class="control-label mb-10"><?php echo direction("Video Link","رابط الفيديو") ?> (YOUTUBE)</label>
<input name="video" type="text" class="form-control"  value="<?php echo $video = (isset($product["video"])) ? $product["video"] : ""; ?>">
</div>
</div>

<div class="col-md-2">
	<div class="form-group">
		<label class="control-label mb-10"><?php echo direction("Size Chart","لوحة المقاسات") ?></label>
		<select name="sizeChart" class="form-control">
			<?php
			$yesText = direction("Yes","نعم");
			$noText = direction("No","لا");
			if( isset($product["sizeChart"]) && $product["sizeChart"] == 1 ){
				echo "<option value='1'>{$yesText}</option><option value='0'>{$noText}</option>";
			}else{
				echo "<option value='0'>{$noText}</option><option value='1'>{$yesText}</option>";
			}
			?>
		</select>
	</div>
</div>

<div class="col-md-2">
	<div class="form-group">
		<label class="control-label mb-10"><?php echo direction("One Time Add","إضافة لمرة واحده") ?></label>
		<select name="oneTime" class="form-control">
			<?php
			$yesText = direction("Yes","نعم");
			$noText = direction("No","لا");
			if( isset($product["oneTime"]) && $product["oneTime"] == 1 ){
				echo "<option value='1'>{$yesText}</option><option value='0'>{$noText}</option>";
			}else{
				echo "<option value='0'>{$noText}</option><option value='1'>{$yesText}</option>";
			}
			?>
		</select>
	</div>
</div>

<div class="col-md-2">
	<div class="form-group">
		<label class="control-label mb-10"><?php echo direction("Require Image", "إرفاق صورة") ?></label>
		<select name="isImage" class="form-control">
			<?php
			$yesText = direction("Yes","نعم");
			$noText = direction("No","لا");
			if( isset($product["isImage"]) && $product["isImage"] == 1 ){
				echo "<option value='1'>{$yesText}</option><option value='0'>{$noText}</option>";
			}else{
				echo "<option value='0'>{$noText}</option><option value='1'>{$yesText}</option>";
			}
			?>
		</select>
	</div> 
</div>

<div class="col-md-2">
	<div class="form-group">
		<label class="control-label mb-10"><?php echo direction("Collection","المجموعة") ?></label>
		<select name="collection" class="form-control">
			<?php
			$yesText = direction("Yes","نعم");
			$noText = direction("No","لا");
			if( isset($product["collection"]) && $product["collection"] == 1 ){
				echo "<option value='1'>{$yesText}</option><option value='0'>{$noText}</option>";
			}else{
				echo "<option value='0'>{$noText}</option><option value='1'>{$yesText}</option>";
			}
			?>
		</select>
	</div>
</div>

<div class="col-md-2">
	<div class="form-group">
		<label class="control-label mb-10"><?php echo direction("Gift Card","كرت هديه") ?></label>
		<select name="giftCard" class="form-control">
			<?php
			$yesText = direction("Yes","نعم");
			$noText = direction("No","لا");
			if( isset($product["giftCard"]) && $product["giftCard"] == 1 ){
				echo "<option value='1'>{$yesText}</option><option value='0'>{$noText}</option>";
			}else{
				echo "<option value='0'>{$noText}</option><option value='1'>{$yesText}</option>";
			}
			?>
		</select>
	</div>
</div>

<div class="col-sm-3">
<div class="form-group">
<label class="control-label mb-10"><?php echo direction("Width","العرض") ?></label>
<input name="width" type="float" class="form-control" value="<?php echo $width = (isset($product["width"])) ? $product["width"] : ""; ?>">
</div>
</div>

<div class="col-sm-3">
<div class="form-group">
<label class="control-label mb-10"><?php echo direction("Height","الطول") ?></label>
<input name="height" type="float" class="form-control" value="<?php echo $height = (isset($product["height"])) ? $product["height"] : ""; ?>">
</div>
</div>

<div class="col-sm-3">
<div class="form-group">
<label class="control-label mb-10"><?php echo direction("Depth","العمق") ?></label>
<input name="depth" type="float" class="form-control" value="<?php echo $depth = (isset($product["depth"])) ? $product["depth"] : ""; ?>">
</div>
</div>

<div class="col-sm-3">
<div class="form-group">
<label class="control-label mb-10"><?php echo direction("Weight","الوزن") ?></label>
<input name="weight" type="float" class="form-control" value="<?php echo $weight = (isset($product["weight"])) ? $product["weight"] : ""; ?>">
</div>
</div>

</div>

<div class="row">
<div class="col-md-6">
<h6 class="txt-dark capitalize-font"><i class="zmdi zmdi-comment-text mr-10"></i><?php echo direction("English Description","وصف باللغة الانجليزية") ?></h6>
<hr class="light-grey-hr"/>
<div class="form-group">
<textarea name="enDetails" class="tinymce"><?php echo $enDetails = (isset($product["enDetails"])) ? $product["enDetails"] : ""; ?></textarea>
</div>
</div>

<div class="col-md-6">
<h6 class="txt-dark capitalize-font"><i class="zmdi zmdi-comment-text mr-10"></i><?php echo direction("Arabic Description","وصف باللغة العربية") ?></h6>
<hr class="light-grey-hr"/>
<div class="form-group">
<textarea name="arDetails" class="tinymce"><?php echo $arDetails = (isset($product["arDetails"])) ? $product["arDetails"] : ""; ?></textarea>
</div>
</div>
</div>
<!--/row-->
<h6 class="txt-dark capitalize-font"><i class="zmdi zmdi-collection-image mr-10"></i><?php echo direction("Images","الصور") ?></h6>
<hr class="light-grey-hr"/>
<div class="row">
<div class="col-lg-12">
<?php 
if ( isset($_GET["id"]) && $images = selectDB("images","`productId` = '{$_GET["id"]}'") ){
	for( $i = 0; $i < sizeof($images); $i++ ){
		?>
		<div class="img-upload-wrap">
		<table style="width:100%">
		<tr>
		<td style="width:300px">
		<img class="img-responsive" style="width:300px;height:300px" src="../logos/<?php echo $images[$i]["imageurl"];?>" alt="upload_img">
		</td>
		</tr>
		<tr>
		<td class="btn btn-info btn-icon left-icon">
		<a href="<?php echo "?v=ProductAction&id={$_GET["id"]}&imgdel={$images[$i]["id"]}" ?>" target="" style="text-decoration:none;color:white"><?php echo direction("Delete","حذف") ?></a>
		</td>
		</tr>
		</table>
		</div>
		<?php
	}
}else{
	?>
	<div class="img-upload-wrap">
	<img class="img-responsive" src="../img/slide1.jpg" alt="upload_img"> 
	</div>
	<?php
}
?>
<div style="padding-top:10px"></div>
<div class="fileupload btn btn-info btn-anim"><i class="fa fa-upload"></i><span class="btn-text"><?php echo direction("Upload","تحميل") ?></span>
<input type="file" name="logo[]" class="upload" multiple="multiple">
</div>
</div>
</div>
<hr class="light-grey-hr"/>

<div class="form-actions">
<button class="btn btn-success btn-icon left-icon mr-10 pull-left"> <i class="fa fa-check"></i> <span><?php echo direction("Save","حفظ") ?></span></button>
<a href="?v=Product"><button type="button" class="btn btn-warning pull-left"><?php echo direction("Cancel","الغاء") ?></button></a>
<div class="clearfix"></div>
</div>
</form>
</div>
</div>
</div>
</div>
</div>
</div>
<script>
$(function(){
	$(".hideMeSoon").hide();
	$("select[name=type]").on("change", function(){
		var selectType = $(this).val();
		if ( selectType == 1 ){
			$(".hideMeSoon").show();
		}else{
			$(".hideMeSoon").hide();
		}
	});
	
	<?php
	if ( isset($_GET["id"]) && !empty($_GET["id"]) ){
		if ( $type == 1 ){
			?> $(".hideMeSoon").show();<?php
		}
	}
	?>
});
</script>

<!-- Tinymce JavaScript -->
<script src="../vendors/bower_components/tinymce/tinymce.min.js"></script>
					
<!-- Tinymce Wysuhtml5 Init JavaScript -->
<script src="dist/js/tinymce-data.js"></script>