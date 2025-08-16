<?php 
if( isset($_POST["enTitle"]) ){
	if( isset($_POST["id"]) && !empty($_POST["id"])){
		$product = selectDB("products","`id` = '{$_POST["id"]}'");
		$updateArray = array(
			"categoryId" => "{$_POST["categoryId"][0]}",
			"brandId" => "{$_POST["brandId"]}",
			"arTitle" => ($_POST["arTitle"]),
			"enTitle" => ($_POST["enTitle"]),
			"arDetails" => ($_POST["arDetails"]),
			"enDetails" => ($_POST["enDetails"]),
			"price" => "{$_POST["price"]}",
			"cost" => "{$_POST["cost"]}",
		);
		updateDB("products",$updateArray,"`id` LIKE '{$_POST["id"]}'");
		deleteDB("category_products","`productId` = {$_POST["id"]}");
		for( $i =0; $i < sizeof($_POST["categoryId"]) ; $i++ ){
			$data = array(
				"productId" => $_POST["id"],
				"categoryId" => $_POST["categoryId"][$i],
			);
			insertDB("category_products",$data);
		}
		if( $product[0]["type"] == 1 ){
			$oldId = selectDB("attributes_products","`productId` = '{$_POST["id"]}'");
			deleteDB("attributes_products","`productId` = {$_POST["id"]}");
			$dataInsert = array(
				"productId" => $_POST["id"],
				"price" => "{$_POST["price"]}",
				"cost" => "{$_POST["cost"]}",
				"quantity" => "{$_POST["quantity"]}",
				"sku" => "{$_POST["sku"]}"
			);
			insertDB("attributes_products",$dataInsert);
			$newId = selectDB("attributes_products","`productId` = '{$_POST["id"]}'");
			updateDB("cart",array("attributeId" => $newId[0]["id"]),"`attributeId` = '{$oldId[0]["id"]}'");
		}
		for( $i = 0; $i < sizeof($_FILES['logo']['tmp_name']); $i++ ){
			if( is_uploaded_file($_FILES['logo']['tmp_name'][$i]) ){
				$filenewname = uploadImageBannerFreeImageHost($_FILES["logo"]["tmp_name"][$i]);
				insertDB("images",array("productId" => $_POST["id"],"imageurl" => $filenewname));
			}
		}
		header("LOCATION: index.php?v=FastAdd");
	}else{
		$files = array();
		foreach ($_FILES["logo"]["tmp_name"] as $key => $tmp_name) {
			$files[] = new CURLFile($tmp_name, $_FILES["logo"]["type"][$key], $_FILES["logo"]["name"][$key]);
		}
		$data = array(
			'categoryId' => $_POST["categoryId"],
			'brandId' => $_POST["brandId"],
			'enTitle' => $_POST["enTitle"],
			'arTitle' => $_POST["arTitle"],
			'enDetails' => $_POST["enDetails"],
			'arDetails' => $_POST["arDetails"],
			'price' => $_POST["price"],
			'cost' => $_POST["cost"],
			'quantity' => $_POST["quantity"],
			'sku' => $_POST["sku"],
		);
		foreach ($files as $key => $file) {
			$data["logo[$key]"] = $file;
		}
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://coeoapp.com/requests/dashboard/index.php?a=Product&action=add',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => $data,
		));
		$response = curl_exec($curl);
		$response = json_decode($response, true);
		curl_close($curl);
		echo $response["data"]["msg"];
	}
}
?>
<div class="row">
<div class="col-sm-12">
<div class="panel panel-default card-view">
<div class="panel-heading">
<div class="pull-left">
	<h6 class="panel-title txt-dark"><?php echo direction("Product Express","المنتج سريع") ?></h6>
</div>
	<div class="clearfix"></div>
</div>
<div class="panel-wrapper collapse in">
<div class="panel-body">
	<form class="" method="POST" action="" enctype="multipart/form-data">
		<div class="row m-0">
            <div class="col-md-6">
			<label><?php echo direction("Category","القسم") ?></label>
				<select name="categoryId[]" class="selectpicker" data-style="form-control btn-default btn-outline" multiple required>
					<?php
                    if( $categories = selectDB("categories","`status` = '0' AND `hidden` = '1'") ){
                        for( $i = 0; $i < sizeof($categories); $i++ ){
                            $title = direction($categories[$i]["enTitle"],$categories[$i]["arTitle"]);
                            echo "<option value='{$categories[$i]["id"]}'>{$title}</option>";
                        }
                    }
                    ?>
				</select>
			</div>

			<div class="col-md-6">
			<label><?php echo direction("Brand","الماركة") ?></label>
				<select name="brandId" class="selectpicker" data-style="form-control btn-default btn-outline" required>
					<?php
                    if( $categories = selectDB("brands","`status` = '0' AND `hidden` = '1'") ){
                        for( $i = 0; $i < sizeof($categories); $i++ ){
                            $title = direction($categories[$i]["enTitle"],$categories[$i]["arTitle"]);
                            echo "<option value='{$categories[$i]["id"]}'>{$title}</option>";
                        }
                    }
                    ?>
				</select>
			</div>

			<div class="col-md-6">
			<label><?php echo direction("English Title","العنوان بالإنجليزي") ?></label>
			<input type="text" name="enTitle" class="form-control" value=""required>
			</div>
			
			<div class="col-md-6">
			<label><?php echo direction("Arabic Title","العنوان بالعربي") ?></label>
			<input type="text" name="arTitle" class="form-control" value="" required>
			</div>
			
			<div class="col-md-3">
			<label><?php echo direction("Cost","التكلفة") ?></label>
			<input type="float" step="any" min="0"  name="cost" class="form-control" value="0">
			</div>

			<div class="col-md-3">
			<label><?php echo direction("Price","القيمة") ?></label>
			<input type="float" step="any" min="0" name="price" class="form-control" value="0" >
			</div>
			
			<div class="col-md-3">
			<label><?php echo direction("Quantity","الكمية") ?></label>
			<input type="number" step="1" min="0" name="quantity" class="form-control" value="0">
			</div>
			
			<div class="col-md-3">
			<label><?php echo direction("SKU","SKU") ?></label>
			<input type="text" name="sku" class="form-control" required>
			</div>

			<div class="col-md-6">
			<label><?php echo direction("English Details","التفاصيل بالإنجليزي") ?></label>
			<textarea name="enDetails" class="tinymce"></textarea>
			</div>

			<div class="col-md-6">
			<label><?php echo direction("Arabic Details","التفاصيل بالعربي") ?></label>
			<textarea name="arDetails" class="tinymce"></textarea>
			</div>

			<div class="col-md-12">
			<label><?php echo direction("Image","صورة") ?></label>
			<input type="file" name="logo[]" class="form-control" multiple>
			</div>

			<div id="images" class="col-md-12" style="display:none">
			
			</div>
			
			<div class="col-md-12" style="margin-top:10px">
			<input type="submit" class="btn btn-primary" value="<?php echo direction("Submit","أرسل") ?>" onclick="showLoading()">
			<input type="hidden" name="id" value="0">
			</div>
		</div>
	</form>
</div>
</div>
</div>
</div>

<div class="col-sm-12">
<div class="panel panel-default card-view">
<div class="panel-heading">
<div class="pull-left" style="width: 100%;">
	<h6 class="panel-title txt-dark"><?php echo direction("Products List","قائمة المنتجات") ?></h6>
	</div>
		<div class="clearfix"></div>
	</div>
	<div class="panel-wrapper collapse in">
	<div class="panel-body row">
	<div class="table-wrap">
	<div class="table-responsive">
	<table class="table display responsive product-overview mb-30" id="myTable">
		<thead>
			<tr>
			<th>#</th>
			<th><?php echo direction("Image","صورة") ?></th>
			<th><?php echo direction("English Title","العنوان بالإنجليزي") ?></th>
			<th><?php echo direction("Arabic Title","العنوان بالعربي") ?></th>
			<th><?php echo direction("Action","الخيارات") ?></th>
			</tr>
		</thead>
		<tbody>
		<?php 
		if( $products = selectDB("products","`status` = '0' AND `hidden` != '2' ORDER BY `id` DESC LIMIT 10") ){
			for( $i = 0; $i < sizeof($products); $i++ ){
				if($image = selectDB2("`imageurl3` AS imageurl","images","`productId` = '{$products[$i]["id"]}' ORDER BY `id` ASC")){
				}else{
					$image[0]["imageurl"] = "noimage.png";
				}
				$attributes = selectDB("attributes_products","`productId` = '{$products[$i]["id"]}' ORDER BY `id` ASC LIMIT 1");
				$categories = selectDB("category_products","`productId` = '{$products[$i]["id"]}'");
			if ( $products[$i]["hidden"] == 2 ){
				$icon = "fa fa-eye";
				$link = "?v={$_GET["v"]}&show={$products[$i]["id"]}";
				$hide = direction("Show","إظهار");
			}else{
				$icon = "fa fa-eye-slash";
				$link = "?v={$_GET["v"]}&hide={$products[$i]["id"]}";
				$hide = direction("Hide","إخفاء");
			}
			?>
			<tr>
				<td><?php echo str_pad($products[$i]["id"], 4, "0", STR_PAD_LEFT) ?></td>
				<td><img src="../logos/<?php echo $image[0]["imageurl"] ?>" style="width: 75px; height: 75px;"></td>
				<td id="enTitle<?php echo $products[$i]["id"] ?>" class="titleLink" data-link="<?php echo $products[$i]["enTitle"] ?>"><?php echo $products[$i]["enTitle"] ?></td>
				<td id="arTitle<?php echo $products[$i]["id"] ?>"><?php echo $products[$i]["arTitle"] ?></td>
				<td class="text-nowrap">
					<a class="btn btn-default edit" id="<?php echo $products[$i]["id"] ?>" href="javascript:void(0)"><i class="zmdi zmdi-edit"></i></a>
					<div style="display: none;">
						<label id="enDetails<?php echo $products[$i]["id"]?>"><?php echo $products[$i]["enDetails"] ?></label>
						<label id="arDetails<?php echo $products[$i]["id"]?>"><?php echo $products[$i]["arDetails"] ?></label>
						<label id="brandId<?php echo $products[$i]["id"]?>"><?php echo $products[$i]["brandId"] ?></label>
						<label id="price<?php echo $products[$i]["id"]?>"><?php echo $attributes[0]["price"] ?></label>
						<label id="cost<?php echo $products[$i]["id"]?>"><?php echo $attributes[0]["cost"] ?></label>
						<label id="sku<?php echo $products[$i]["id"]?>"><?php echo $attributes[0]["sku"] ?></label>
						<label id="quantity<?php echo $products[$i]["id"]?>"><?php echo $attributes[0]["quantity"] ?></label>
						<label id="image<?php echo $products[$i]["id"]?>"><?php echo json_encode($image)?></label>
						<label id="category<?php echo $products[$i]["id"]?>"><?php echo json_encode($categories) ?></label>
					</div>
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

</div>

<script>
	$(document).on("click",".edit", function(){
		var id = $(this).attr("id");
		$("input[name=id]").val(id);
		$("input[type=submit").val("<?php echo direction("Update","تحديث") ?>");
		$("input[name=enTitle]").val($("#enTitle"+id).html()).focus();;
		$("input[name=arTitle]").val($("#arTitle"+id).html());
		tinymce.get('enDetails').setContent($("#enDetails"+id).html());
		tinymce.get('arDetails').setContent($("#arDetails"+id).html());
		$("input[name=cost]").val($("#cost"+id).html());
		$("input[name=quantity]").val($("#quantity"+id).html());
		$("input[name=price]").val($("#price"+id).html());
		$("input[name=sku]").val($("#sku"+id).html());
		$("select[name=brandId] option").prop("selected", false);
		$("select[name=brandId]").val($("#brandId"+id).html()).selectpicker('refresh');
		$("select[name='categoryId[]'] option").prop("selected", false);
		var data = JSON.parse($("#category"+id).html());
		$.each(data, function(index, value){
			$("select[name='categoryId[]'] option[value='"+value["categoryId"]+"']").prop("selected", true);
		});
		$("select[name='categoryId[]']").selectpicker('refresh');
		$("#images").empty().attr("style","margin-top:10px;display:block"); // Clear the div
		$.each(JSON.parse($("#image"+id).html()), function(index, value){
			var img = $("<img>").attr({
				src: "../logos/" + value["imageurl"],
				width: 100,
				height: 100,
			});
			$("#images").append(img);
		});
	})

	$(document).ready(function () {
		$(".titleLink").on("click", function (e) {
			var link = $(this).attr("data-link");
			e.preventDefault();
			// Open the first website
			window.open("https://www.google.com/search?q="+link+" amazon", "_blank");
		});
	});
</script>
