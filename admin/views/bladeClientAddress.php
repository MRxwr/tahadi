<?php 
if( isset($_GET["delId"]) && !empty($_GET["delId"]) ){
    $clientId = $_GET["num"];
    $user = selectDBNew("users",[$clientId],"`id` = ?","");
    $addresses = json_decode($user[0]["addresses"],true);
    unset($addresses[$_GET["delId"]]);
    $addresses = array_values($addresses);
    $addresses = json_encode($addresses);
    if( updateDB("users",array("addresses"=>$addresses),"`id` = '{$clientId}'") ){
        header("LOCATION: ?v=ClientAddress&num={$clientId}");
    }
}
if( isset($_POST["id"]) ){
	$id = $_POST["id"];
    $clientId = $_POST["num"];
    $update = $_POST["update"];
	unset($_POST["update"]);
	unset($_POST["num"]);
    $user = selectDBNew("users",[$clientId],"`id` = ?","");
    $addresses = json_decode($user[0]["addresses"],true);
	if ( $update == 1 ){
        $_POST["id"] = sizeof($addresses);
        array_push($addresses,$_POST);
        unset($_POST);
        $_POST["addresses"] = json_encode($addresses);
		if( updateDB("users", $_POST, "`id` = '{$clientId}'") ){
			header("LOCATION: ?v=ClientAddress&num={$clientId}");
		}else{
		?>
		<script>
			alert("Could not process your request, Please try again.");
		</script>
		<?php
		}
	}else{
        $addresses[$id] = $_POST;
        unset($_POST);
        $_POST["addresses"] = json_encode($addresses);
		if( updateDB("users", $_POST, "`id` = '{$clientId}'") ){
			header("LOCATION: ?v=ClientAddress&num={$clientId}");
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
	<h6 class="panel-title txt-dark"><?php echo direction("Address Details","تفاصيل العنوان") ?></h6>
</div>
	<div class="clearfix"></div>
</div>
<div class="panel-wrapper collapse in">
<div class="panel-body">
	<form class="" method="POST" action="" enctype="multipart/form-data">
		<div class="row m-0">
			<div class="col-md-6">
			<label><?php echo direction("Place","المكان") ?></label>
            <select name="place" class="form-control" required>
                <option value="1"><?php echo direction("Home","المنزل") ?></option>
                <option value="2"><?php echo direction("Work","العمل") ?></option>
            </select>
			</div>

			<div class="col-md-6">
			<label><?php echo direction("Country","الدولة") ?></label>
			<input type="text" name="country" class="form-control" value="kw" readonly required>
			</div>

			<div class="col-md-6">
			<label><?php echo direction("Area","المنطقة") ?></label>
			<select name="area" class="form-control" required>
                <?php
                $areas = selectDB("areas","`status` = '0'");
                foreach( $areas as $area ){
                    echo '<option value="'.$area["enTitle"].'">'.direction($area["enTitle"],$area["arTitle"]).'</option>';
                }
                ?>
            </select>
			</div>

			<div class="col-md-6">
			<label><?php echo direction("Block","القطعه") ?></label>
			<input type="text" name="block" class="form-control" required>
			</div>

            <div class="col-md-6">
			<label><?php echo direction("Street","الشارع") ?></label>
			<input type="text" name="street" class="form-control" required>
			</div>

            <div class="col-md-6">
			<label><?php echo direction("Avenue","الجاده") ?></label>
			<input type="text" name="avenue" class="form-control" >
			</div>

            <div class="col-md-6">
			<label><?php echo direction("Building","المبنى") ?></label>
			<input type="text" name="building" class="form-control" required>
			</div>

            <div class="col-md-6">
			<label><?php echo direction("Floor","الطابق") ?></label>
			<input type="text" name="floor" class="form-control" >
			</div>

            <div class="col-md-6">
			<label><?php echo direction("Apartment","الشقه") ?></label>
			<input type="text" name="apartment" class="form-control" >
			</div>

            <div class="col-md-6">
			<label><?php echo direction("Postal Code","الرمز البريدي") ?></label>
			<input type="text" name="postalCode" class="form-control" >
			</div>

            <div class="col-md-12">
			<label><?php echo direction("Note","ملاحظات") ?></label>
			<input type="text" name="notes" class="form-control" >
			</div>
			
			<div class="col-md-12" style="margin-top:10px">
			<input type="submit" class="btn btn-primary" value="<?php echo direction("Submit","أرسل") ?>">
			<input type="hidden" name="update" value="1">
			<input type="hidden" name="id" value="0">
			<input type="hidden" name="num" value="<?php echo $_GET["num"] ?>">
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
<div class="pull-left">
<h6 class="panel-title txt-dark"><?php echo direction("List of Addresses","قائمة العناوين") ?></h6>
</div>
<div class="clearfix"></div>
</div>
<div class="panel-wrapper collapse in">
<div class="panel-body">
<div class="table-wrap mt-40">
<div class="table-responsive">
	<table class="table display responsive product-overview mb-30" id="myTable">
		<thead>
		<tr>
		<th><?php echo direction("Place","المكان") ?></th>
        <th><?php echo direction("Country","الدولة") ?></th>
        <th><?php echo direction("Area","المنطقة") ?></th>
        <th><?php echo direction("Block","القطعه") ?></th>
        <th><?php echo direction("Street","الشارع") ?></th>
        <th><?php echo direction("Avenue","الجاده") ?></th>
        <th><?php echo direction("Building","المبنى") ?></th>
        <th><?php echo direction("Floor","الطابق") ?></th>
        <th><?php echo direction("Apartment","الشقه") ?></th>
        <th><?php echo direction("Postal Code","الرمز البريدي") ?></th>
        <th><?php echo direction("Note","ملاحظات") ?></th>
		<th class="text-nowrap"><?php echo direction("Actions","الخيارات") ?></th>
		</tr>
		</thead>
		
		<tbody>
		<?php 
		if( $users = selectDB("users","`id` = '{$_GET["num"]}'") ){
            $addresses = json_decode($users[0]["addresses"],true);
			for( $i = 0; $i < sizeof($addresses); $i++ ){	
                $place = ( $addresses[$i]["place"] == 1 ) ? "Home" : "Work";
				?>
				<tr>
                <td><?php echo $place ?></td>
                <td id="country<?php echo $addresses[$i]["id"]?>" ><?php echo $addresses[$i]["country"] ?></td>
                <td id="area<?php echo $addresses[$i]["id"]?>" ><?php echo $addresses[$i]["area"] ?></td>
                <td id="block<?php echo $addresses[$i]["id"]?>" ><?php echo $addresses[$i]["block"] ?></td>
                <td id="street<?php echo $addresses[$i]["id"]?>" ><?php echo $addresses[$i]["street"] ?></td>   
                <td id="avenue<?php echo $addresses[$i]["id"]?>" ><?php echo $addresses[$i]["avenue"] ?></td>
                <td id="building<?php echo $addresses[$i]["id"]?>" ><?php echo $addresses[$i]["building"] ?></td>
                <td id="floor<?php echo $addresses[$i]["id"]?>" ><?php echo $addresses[$i]["floor"] ?></td>
                <td id="apartment<?php echo $addresses[$i]["id"]?>" ><?php echo $addresses[$i]["apartment"] ?></td>
                <td id="postalCode<?php echo $addresses[$i]["id"]?>" ><?php echo $addresses[$i]["postalCode"] ?></td>
                <td id="notes<?php echo $addresses[$i]["id"]?>" ><?php echo $addresses[$i]["notes"] ?></td>
				</td>
				<td class="text-nowrap">
					<a id="<?php echo $addresses[$i]["id"] ?>" class="mr-25 edit" data-toggle="tooltip" data-original-title="<?php echo direction("Edit","تعديل") ?>"> <i class="fa fa-pencil text-inverse m-r-10"></i></a>
					<a href="<?php echo "?v={$_GET["v"]}&delId={$addresses[$i]["id"]}&num={$_GET["num"]}" ?>" data-toggle="tooltip" data-original-title="<?php echo direction("Delete","حذف") ?>"><i class="fa fa-close text-danger"></i></a>
					<div style="display:none">
						<label id="id<?php echo $addresses[$i]["id"]?>"><?php echo $addresses[$i]["id"] ?></label>
                        <label id="place<?php echo $addresses[$i]["id"]?>"><?php echo $addresses[$i]["place"] ?></label>
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
		$("input[name=update]").val(0);
		$("input[name=id]").val(id);
		$("select[name=place]").val($("#place"+id).html()).focus();
        $("input[name=country]").val($("#country"+id).html());
        $("select[name=area]").val($("#area"+id).html());
        $("input[name=block]").val($("#block"+id).html());
        $("input[name=street]").val($("#street"+id).html());
        $("input[name=avenue]").val($("#avenue"+id).html());
        $("input[name=building]").val($("#building"+id).html());
        $("input[name=floor]").val($("#floor"+id).html());
        $("input[name=apartment]").val($("#apartment"+id).html());
        $("input[name=postalCode]").val($("#postalCode"+id).html());
        $("input[name=notes]").val($("#notes"+id).html());
	})
</script>