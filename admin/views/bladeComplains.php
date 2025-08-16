<?php 
if( isset($_GET["hide"]) && !empty($_GET["hide"]) ){
	if( updateDB("complains",array('hidden'=> '2'),"`id` = '{$_GET["hide"]}'") ){
		header("LOCATION: ?v=Complains");
	}
}

if( isset($_GET["show"]) && !empty($_GET["show"]) ){
	if( updateDB("complains",array('hidden'=> '1'),"`id` = '{$_GET["show"]}'") ){
		header("LOCATION: ?v=Complains");
	}
}

if( isset($_GET["delId"]) && !empty($_GET["delId"]) ){
	if( updateDB("complains",array('status'=> '1'),"`id` = '{$_GET["delId"]}'") ){
		header("LOCATION: ?v=Complains");
	}
}
?>
<div class="col-sm-12">
<div class="panel panel-default card-view">
<div class="panel-heading">
<div class="pull-left">
<h6 class="panel-title txt-dark"><?php echo direction("Complains List","قائمة الشكاوي") ?></h6>
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
		<th><?php echo direction("Client","الزبون") ?></th>
		<th><?php echo direction("Mobile","الهاتف") ?></th>
        <th><?php echo direction("Email","البريد الإلكتروني") ?></th>
		<th><?php echo direction("Type","النوع") ?></th>
		<th><?php echo direction("Order Id","رقم الطلب") ?></th>
		<th><?php echo direction("Message","الرسالة") ?></th>
		<th class="text-nowrap"><?php echo direction("Actions","الخيارات") ?></th>
		</tr>
		</thead>
		
		<tbody>
		<?php 
        $joinData = array(
            "select" => ["t.id","t.orderId","t.hidden","t.msg","t1.enTitle","t1.arTitle","t2.fName","t2.lName","t2.email","t2.countryCode","t2.phone"],
            "join" => ["complains_types","users"],
            "on" => ["t.type = t1.id","t.userId = t2.id"]
        );
		if( $complains = selectJoinDB("complains", $joinData, "t.status = 0 ORDER BY t.id DESC") ){
			for( $i = 0; $i < sizeof($complains); $i++ ){
			if ( $complains[$i]["hidden"] == 2 ){
				$icon = "fa fa-times text-danger";
				$link = "?v={$_GET["v"]}&show={$complains[$i]["id"]}";
				$hide = direction("Show","إظهار");
			}else{
				$icon = "fa fa-check text-success";;
				$link = "?v={$_GET["v"]}&hide={$complains[$i]["id"]}";
				$hide = direction("Done","إنتهاء");
			}
            
			?>
			<tr>
            <td><?php echo $complains[$i]["fName"]." ".$complains[$i]["lName"] ?></td>
            <td><a href="https://wa.me/<?php echo $complains[$i]["countryCode"] . $complains[$i]["phone"] ?>" target="_blank"><?php echo $complains[$i]["phone"] ?></a></td>
            <td><a href="mailto:<?php echo $complains[$i]["email"] ?>"><?php echo $complains[$i]["email"] ?></td>
			<td><?php echo direction($complains[$i]["enTitle"],$complains[$i]["arTitle"]) ?></td>
            <td><a href="?v=Order&orderId=<?php echo $complains[$i]["orderId"] ?>" target="_blank"><?php echo $complains[$i]["orderId"] ?></a></td>
			<td><?php echo $complains[$i]["msg"] ?></td>
			<td class="text-nowrap">
			    <a href="<?php echo $link ?>" class="mr-25" data-toggle="tooltip" data-original-title="<?php echo $hide ?>"> <i class="<?php echo $icon ?> m-r-10"></i></a>
                <a href="?v=Complains&delId=<?php echo $complains[$i]["id"] ?>" class="mr-25" data-toggle="tooltip" data-original-title="<?php echo direction("Delete","حذف") ?>"> <i class="fa fa-trash text-inverse m-r-10"></i></a>
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