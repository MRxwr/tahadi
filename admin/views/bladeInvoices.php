<?php
header ("Refresh:180");
if( isset($_GET["status"]) && isset($_GET["orderId"]) && !empty($_GET["orderId"]) ){
	updateDB("orders2",array("status" => "{$_GET["status"]}"),"`id` = '{$_GET["orderId"]}'");
    $order = selectDBNew("orders2",[$_GET["orderId"]],"`id` = ?","");
    $user = selectDBNew("users",[$order[0]["userId"]],"`id` = ?","");
    $arrayStatus = [1,2,3,4,5,6];
    $arrayText = ["is pending.","is successful.","is being prepared.","is on delivery.","has been delivered.","has been cancelled."];
    $notification["body"] = "Your order {$order[0]["id"]} {$arrayText[$_GET["status"]]}";
    $notification["title"] = "Order Status Update";
    $notification["image"] = "";
    $notification["userId"] = $user[0]["id"];
    insertDB("notifications", $notification);
    $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://coeoapp.com/requests/store/?endpoint=FirebaseNotification&language=en&action=sendToSingleUser',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $notification,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
	$_GET["v"] = ( isset($_GET["type"]) && !empty($_GET["type"]) ) ? "{$_GET["v"]}&type={$_GET["type"]}": "{$_GET["v"]}";
	header("LOCATION: ?v={$_GET["v"]}");
}
$array = [1,2,3,4,5,6];
if( isset($_GET["type"]) && in_array($_GET["type"],$array) ){
	$type = " AND `status` = '{$_GET["type"]}'";
	$tp=$_GET["type"];
}else{
	$type = "";
	$tp= "";
}
?>
<div class="row">
<div class="col-sm-12">
<div class="panel panel-default card-view">
<div class="panel-heading">
<div class="pull-left">
<h6 class="panel-title txt-dark"><?php echo direction("List of Invoices","قائمة الطلبات") ?></h6>
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
<th><?php echo direction("Date","التاريخ") ?></th>
<th><?php echo direction("Order ID","رقم الطلب") ?></th>
<th><?php echo direction("Mobile","الهاتف") ?></th>
<th><?php echo direction("Voucher","القسيمة") ?></th>
<th><?php echo direction("Price","السعر") ?></th>
<th><?php echo direction("Method","الطريقه") ?></th>
<th><?php echo direction("Status","الحاله") ?></th>
<th><?php echo direction("Actions","الخيارات") ?></th>
</tr>
</thead>
<tbody>
<?php
if( $orders = selectDB("orders2","`id` != '0' $type") ){
    $statusId = [0,1,2,3,4,5,6];
    $statusText = [direction("Pending","انتظار"),direction("Success","ناجح"),direction("Preparing","جاري التجهيز"), direction("On Delivery","جاري التوصيل"), direction("Delivered","تم تسليمها"), direction("Failed","فاشلة"),direction("Returned","مسترجعه")];
    $statusBgColor = ["default","success","default","warning","success","danger","default"];
    for( $i = 0; $i < sizeof($orders); $i++ ){
        $info = json_decode($orders[$i]["info"],true);
		$voucher = json_decode($orders[$i]["voucher"],true);
        $voucher[0]["voucher"] = ( !isset($voucher[0]["voucher"]) || empty($voucher[0]["voucher"])) ? "" : $voucher[0]["voucher"];
        $method = ( $orders[$i]["paymentMethod"] == 1 ) ? direction("Online Payment","دفع أونلاين") : direction("Cash Payment","دفع نقدي");
        for ( $y = 0; $y < sizeof($statusId); $y++ ){
			if( $statusId[$y] == $orders[$i]["status"] ){
			    $status = "<div class='bg-{$statusBgColor[$y]}' style='font-weight:700; color:black; padding:20px 15px;'>{$statusText[$y]}</div>";
			}
		}
        ?>
    <tr>
        <td><a target="_blank" href="<?php echo "?v=Order&orderId={$orders[$i]["id"]}" ?>"><?php echo substr($orders[$i]["date"],0,10) ?></a></td>
        <td><?php echo $orders[$i]["id"] ?></td>
        <td><?php echo $info["phone"] ?></td>
        <td><?php echo $voucher[0]["voucher"] ?></td>
        <td><?php echo numTo3Float($orders[$i]["price"]) . selectedCurr()?></td>
        <td><?php echo $method ?></td>
        <td><?php echo $status ?></td>
        <td>
            <div class="btn-group">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo "?v={$_GET["v"]}&orderId={$orders[$i]["id"]}&status=0"?>"><i class="fa fa-clock-o txt-default"></i> <?php echo direction("Pending","قيد التفقد") ?></a></li>
                    <li><a href="<?php echo "?v={$_GET["v"]}&orderId={$orders[$i]["id"]}&status=1" ?>"><i class="fa fa-check txt-success"></i> <?php echo direction("Success","ناجح") ?></a></li>
                    <li><a href="<?php echo "?v={$_GET["v"]}&orderId={$orders[$i]["id"]}&status=2" ?>"><i class="fa fa-clock-o txt-info"></i> <?php echo direction("Preparing","جاري التجهيز") ?></a></li>
                    <li><a href="<?php echo "?v={$_GET["v"]}&orderId={$orders[$i]["id"]}&status=3" ?>"><i class="fa fa-car txt-warning"></i> <?php echo direction("On Delivery","جاري التوصيل") ?></a></li>
                    <li><a href="<?php echo "?v={$_GET["v"]}&orderId={$orders[$i]["id"]}&status=4" ?>"><i class="fa fa-car txt-success"></i> <?php echo direction("Delivered","تم التوصيل") ?></a></li>
                    <li><a href="<?php echo "?v={$_GET["v"]}&orderId={$orders[$i]["id"]}&status=5" ?>"><i class="fa fa-close txt-danger"></i> <?php echo direction("Failed","فاشل") ?></a></li>
                    <li><a href="<?php echo "?v={$_GET["v"]}&orderId={$orders[$i]["id"]}&status=6" ?>"><i class="fa fa-retweet txt-default"></i> <?php echo direction("Returned","مسترجع") ?></a></li>
                </ul>
            </div>
			<button class='btn btn-primary btn-icon-anim btn-circle printNow' title='<?php echo direction("Print","طباعة") ?>' data-toggle='tooltip' id='<?php echo $orders[$i]["id"] ?>'>
				<i class='fa fa-print' style='font-size: 27px;margin-top: 5px;'></i>
			</button>
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
<audio id="my_audio">
    <source src="got-it-done.mp3" type="audio/mpeg">
</audio>
<?php
$sql = "SELECT * FROM `orders2` 
        WHERE 
        date >= now() + interval 177 minute 
        AND 
        status = 1
        ";
$result = $dbconnect->query($sql);
if ( $result->num_rows > 0 ){
    ?>
    <script>
        window.onload = function() {
    document.getElementById("my_audio").play();
    }
    </script>
    <?php
}
?>

<script>
$(function(){
	$(document).on('click','.takeMeToPrinter',function(e){
		w = window.open();
		$('.takeMeToPrinter').hide();
		w.document.write($('.printBill').html());
		w.print();
		w.close();
		$('.takeMeToPrinter').show();
	});
})

$(function(){
	$(document).on('click','.printNow',function(e){
		var printId = $(this).attr("id");
		var url = '<?php echo $settingsWebsite ?>';
		$("<iframe>")
        .hide()
        .attr("src", url+"/admin/print.php?info=view&orderId="+printId)
        .appendTo("body");
	});
})
</script>
<link href='https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css' rel='stylesheet' type='text/css'>

<!-- Datatable JS -->
<script>
    /*
$(document).ready(function(){
   $('#AjaxTable').DataTable({
      'processing': true,
      'serverSide': true,
      "pageLength": 25,
      'serverMethod': 'post',
      'ajax': {
          'url':'../admin/template/ajax/getInvoiceItems.php?v=<?=$_GET["v"]?>&type=<?=$tp?>'
      },
      'order': [[0, 'desc']],
      'columns': [
         { data: 'date' },
         { data: 'orderId' },
         { data: 'phone' },
         { data: 'voucher' },
         { data: 'price' },
         { data: 'method' },
         { data: 'status' },
         { data: 'action' },
      ]
   });
});
*/
</script>
