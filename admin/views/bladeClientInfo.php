<?php 
if( $user = selectDB("users","`id` = '{$_GET["id"]}'")){
	if( $settings = selectDB("settings","`id` = '1'") ){
		$defaultCurr = $settings[0]["currency"];
	}
}else{
	header("LOCATION: ?v=ListOfusers");die();
}
?>
<div class="row">
	<div class="col-sm-12">
		<div class="panel panel-default card-view">
		<div class="panel-wrapper collapse in">
		<div class="panel-body">
		<div class="form-wrap">
			<form action="#">
				<h6 class="txt-dark capitalize-font"><i class="zmdi zmdi-info-outline mr-10"></i><?php echo direction("User Details","معلومات العضو") ?></h6>
				<hr class="light-grey-hr"/>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
						<label class="control-label mb-10">Name</label>
						<input type="text" id="enname" class="form-control" value="<?php echo $user[0]["fName"] . " " . $user[0]["lName"];?>" disabled>
						</div>
					</div>
					<!--/span-->
					<div class="col-md-6">
						<div class="form-group">
						<label class="control-label mb-10">E-mail</label>
						<input type="text" id="arname" class="form-control" value="<?php echo $user[0]["email"];?>" disabled>
						</div>
					</div>
					<!--/span-->
				</div>

				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
						<label class="control-label mb-10">Phone</label>
						<input type="text" id="enname" class="form-control" value="<?php echo $user[0]["phone"];?>" disabled>
						</div>
					</div>
					<!--/span-->
					<div class="col-md-6">
						<div class="form-group">
						<label class="control-label mb-10">Joinging Date</label>
						<input type="text" id="arname" class="form-control" value="<?php $date = explode(" ",$user[0]["date"]); echo $date[0];?>" disabled>
						</div>
					</div>
					<!--/span-->
				</div>
			</form>
		</div>
		</div>
		</div>
		</div>
	</div>

<?php

$sql = "SELECT *
		FROM `orders2`
		WHERE
		JSON_UNQUOTE(JSON_EXTRACT(info,'$.name')) LIKE '%{$user[0]["fName"]}%'
		AND
		JSON_UNQUOTE(JSON_EXTRACT(info,'$.name')) LIKE '%{$user[0]["lName"]}%'
		AND
		JSON_UNQUOTE(JSON_EXTRACT(info,'$.email')) LIKE '%{$user[0]["email"]}%'
		AND
		JSON_UNQUOTE(JSON_EXTRACT(info,'$.phone')) LIKE '%{$user[0]["phone"]}%'
		";
$result = $dbconnect->query($sql);
?>
<div class="col-sm-12">
<div class="panel panel-default card-view">
<div class="panel-wrapper collapse in">
<div class="panel-body row">
<div class="table-wrap">
<div class="table-responsive">
<table class="table display responsive product-overview mb-30" id="myTable">
<thead>
<tr>
<th><?php echo direction("Date","التاريخ") ?></th>
<th><?php echo "#" ?></th>
<th><?php echo direction("Voucher","كود الخصم") ?></th>
<th><?php echo direction("Total","المجموع") ?></th>
<th><?php echo direction("Payment Method","طريقة الدفع") ?></th>
<th><?php echo direction("Status","الحاله") ?></th>
<th><?php echo direction("Actions","الخيارات") ?></th>
</tr>
</thead>
<tbody>
<?php 
while ( $row = $result->fetch_assoc() ){
	$info = json_decode($row["info"],true);
	$voucher = json_decode($row["voucher"],true);
	$address = json_decode($row["address"],true);
	$items = json_decode($row["items"],true);
	$orederID = $row["orderId"];
	?>
	<tr>
		<td><?php echo $row["date"] ?></td>
		<td class="txt-dark"><?php echo $row["orderId"] ?></td>
		<td><?php echo $voucher[0]["voucher"] ?></td>
		<td><?php echo numTo3Float($row["price"]+$address["shipping"]) . $defaultCurr ?></td>
		<td>
			<?php 
			if( $row["paymentMethod"] == 1 ){
				echo "<b style='color:darkblue'>Online Payment</b>";
			}else{
				echo "<b style='color:darkgreen'>CASH</b>";
			}
			?>
		</td>
		<td>
			<?php 
			if( $row["status"] == 5 ){
				echo "<span class='label label-warning font-weight-100'>".direction("On Delivery","جاري التوصيل")."</span>";
			}elseif( $row["status"] == 4 ){
				echo "<span class='label label-success font-weight-100'>".direction("Delivered","تم التوصيل")."</span>";
			}elseif( $row["status"] == 3 ){
				//echo "<span class='label label-danger font-weight-100'>$Returned</span>";
			}elseif( $row["status"] == 2 ){
				echo "<span class='label label-default font-weight-100'>".direction("Failed","فشل")."</span>";
			}elseif( $row["status"] == 1 ){
				echo "<span class='label label-primary font-weight-100'>".direction("Paid","تم الدفع")."</span>";
			}elseif( $row["status"] == 0 ){
				echo "<span class='label label-default font-weight-100'>".direction("Pending","قيد الانتظار")."</span>";
			}
			?>
		</td>
		<td>
			<a target="_blank" href="?v=Order&orderId=<?php echo $orederID ?>">
			<button class="btn btn-info btn-rounded"><?php echo direction("View","عرض") ?>
			</button>
		</td>
	</tr>
	<?php
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