<?php
if( $shop = selectDBNew("shops",[$_GET["id"]],"`id` = ? AND `status` = '0'","") ){
    $shopName = $shop[0]["enTitle"];
}else{
    header("Location: /404.php");
    exit;
}
?>
<!-- Custom styles for export buttons -->
<style>
.export-buttons {
    margin-bottom: 20px;
}
.export-buttons .btn {
    margin-right: 8px;
    font-size: 13px;
    padding: 8px 15px;
    font-weight: 600;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}
.export-buttons .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
.export-buttons .btn:last-child {
    margin-right: 0;
}
.export-buttons .btn i {
    margin-right: 6px;
}
.table-responsive {
    margin-top: 15px;
}
.dataTables_wrapper .dataTables_length {
    margin-bottom: 20px;
}
.dataTables_wrapper .dataTables_length select {
    padding: 5px 10px;
    border-radius: 4px;
    border: 1px solid #ddd;
    font-weight: 600;
}
.dataTables_wrapper .dataTables_length select option:last-child {
    font-weight: bold;
    color: #007bff;
    background-color: #f8f9fa;
}
.dataTables_wrapper .dataTables_length label {
    font-weight: 600;
    color: #495057;
}

/* Title column styling for long text wrapping */
#shopItemsTable td.title-column {
    white-space: normal !important;
    word-wrap: break-word;
    max-width: 250px;
    min-width: 150px;
    line-height: 1.4;
    padding: 12px 8px;
    vertical-align: top;
}

#shopItemsTable th:nth-child(2) {
    width: 250px;
    min-width: 150px;
}

/* Ensure other columns maintain their structure */
#shopItemsTable td:not(.title-column) {
    white-space: nowrap;
    vertical-align: middle;
}

/* Responsive handling for smaller screens */
@media (max-width: 768px) {
    #shopItemsTable td.title-column {
        max-width: 200px;
        min-width: 120px;
        font-size: 13px;
    }
}
</style>

<!-- Bordered Table -->
<div class="col-sm-12">
<div class="panel panel-default card-view">
<div class="panel-heading">
<div class="pull-left">
<h6 class="panel-title txt-dark"><?php echo direction("List of Shop Products","قائمة منتجات المتجر") ?></h6>
</div>
<div class="clearfix"></div>
</div>
<div class="panel-wrapper collapse in">
<div class="panel-body">
<!-- Export Actions Section -->
<div class="row mb-20">
<div class="col-sm-12">
<div class="export-section" style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #007bff;">
<h6 class="mb-10" style="margin: 0 0 10px 0; color: #495057;">
<i class="fa fa-download"></i> <?php echo direction("Export Options","خيارات التصدير") ?>
</h6>
<div class="export-buttons">
<button type="button" class="btn btn-danger" onclick="exportToPDF()" title="Export as PDF">
<i class="fa fa-file-pdf-o"></i> <?php echo direction("Export PDF","تصدير PDF") ?>
</button>
<button type="button" class="btn btn-success" onclick="exportToExcel()" title="Export as Excel">
<i class="fa fa-file-excel-o"></i> <?php echo direction("Export Excel","تصدير إكسل") ?>
</button>
<button type="button" class="btn btn-info" onclick="exportToCSV()" title="Export as CSV">
<i class="fa fa-file-text-o"></i> <?php echo direction("Export CSV","تصدير CSV") ?>
</button>
<button type="button" class="btn btn-warning" onclick="printTable()" title="Print Table">
<i class="fa fa-print"></i> <?php echo direction("Print","طباعة") ?>
</button>
</div>
</div>
</div>
</div>
<div class="table-wrap mt-40">
<div class="table-responsive">
	<table class="table display responsive product-overview mb-30" id="shopItemsTable">
		<thead>
		<tr>
		<th><i class="fa fa-tag"></i> <?php echo direction("ID","الرقم") ?></th>
		<th><i class="fa fa-tag"></i> <?php echo direction("Title","الإسم") ?></th>
		<th><i class="fa fa-list"></i> <?php echo direction("Category","التصنيف") ?></th>
		<th><i class="fa fa-trademark"></i> <?php echo direction("Brand","العلامة التجارية") ?></th>
        <th><i class="fa fa-barcode"></i> <?php echo direction("SKU","رمز المنتج") ?></th>
		<th><i class="fa fa-cubes"></i> <?php echo direction("Quantity","الكمية") ?></th>
        <th><i class="fa fa-money"></i> <?php echo direction("Cost Price","سعر التكلفة") ?></th>
		</tr>
		</thead>
		
		<tbody>
		<?php 
        $JoinData = array(
            "select" => ["t.enTitle as productTitle", "t.id as productId", "t1.enTitle as categoryTitle","t2.enTitle as brandTitle","t3.quantity","t3.sku","CAST(ROUND(t3.cost, 3) AS CHAR) AS costPrice"],
            "join" => ["categories", "brands", "attributes_products"],
            "on" => ["t1.id = t.categoryId", "t2.id = t.brandId", "t3.productId = t.id"]
        );
		if( $products = selectJoinDBNew("products", $JoinData,[$_GET["id"]],"t.id != 0 AND t.shopId = ? ORDER BY t.id DESC") ){
			for( $i = 0; $i < sizeof($products); $i++ ){
				?>
				<tr>
				<td><strong><?php echo $products[$i]["productId"] ?></strong></td>
				<td class="title-column">
					<strong style="display: block; margin-bottom: 2px;">
						<?php echo htmlspecialchars($products[$i]["productTitle"]) ?>
					</strong>
				</td>
				<td><span class="label label-info"><?php echo $products[$i]["categoryTitle"] ?></span></td>
				<td><span class="label label-primary"><?php echo $products[$i]["brandTitle"] ?></span></td>
                <td class="text-center"><code><?php echo $products[$i]["sku"] ?></code></td>
				<td class="text-center"><span class="badge badge-secondary"><?php echo $products[$i]["quantity"] ?></span></td>
				<td class="text-right"><strong><?php echo number_format($products[$i]["costPrice"], 3) ?></strong></td>
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

<script>
$(document).ready(function() {
    // Generate filename with current date and shop name
    var currentDate = new Date();
    var day = String(currentDate.getDate()).padStart(2, '0');
    var month = String(currentDate.getMonth() + 1).padStart(2, '0');
    var year = currentDate.getFullYear();
    var shopName = '<?php echo isset($shopName) ? preg_replace("/[^a-zA-Z0-9]/", "", $shopName) : "shop"; ?>';
    var shopNameDisplay = '<?php echo isset($shopName) ? addslashes($shopName) : "Shop"; ?>';
    var dateString = day + '.' + month + '-' + year;
    var baseFilename = dateString + '-' + shopName;
    var headerTitle = shopNameDisplay + ' - Products List (' + dateString + ')';
    
    // Initialize DataTable with export functionality
    var table = $('#shopItemsTable').DataTable({
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'pdfHtml5',
                className: 'btn btn-danger btn-sm',
                text: '<i class="fa fa-file-pdf-o"></i> PDF',
                title: headerTitle,
                filename: baseFilename + '-products',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: {
                    columns: ':visible'
                },
                customize: function(doc) {
                    doc.defaultStyle.fontSize = 10;
                    doc.styles.tableHeader.fontSize = 12;
                    doc.styles.title.fontSize = 18;
                    doc.styles.title.alignment = 'center';
                    doc.styles.title.margin = [0, 0, 0, 20];
                    
                    // Add shop name and date as main title
                    doc.content.splice(0, 1); // Remove default title
                    doc.content.unshift({
                        text: headerTitle,
                        style: 'title',
                        alignment: 'center',
                        margin: [0, 0, 0, 20]
                    });
                    
                    // Add subtitle with current date
                    doc.content.splice(1, 0, {
                        text: 'Generated on: ' + new Date().toLocaleDateString(),
                        style: 'subheader',
                        alignment: 'center',
                        fontSize: 12,
                        margin: [0, 0, 0, 15]
                    });
                    
                    // Set column widths
                    if (doc.content[2] && doc.content[2].table) {
                        doc.content[2].table.widths = ['8%', '30%', '15%', '15%', '12%', '10%', '10%'];
                    }
                }
            },
            {
                extend: 'excelHtml5',
                className: 'btn btn-success btn-sm',
                text: '<i class="fa fa-file-excel-o"></i> Excel',
                title: headerTitle,
                filename: baseFilename + '-products',
                exportOptions: {
                    columns: ':visible'
                },
                customize: function(xlsx) {
                    var sheet = xlsx.xl.worksheets['sheet1.xml'];
                    
                    // Add header rows
                    var headerRow1 = '<row r="1"><c r="A1" t="inlineStr"><is><t>' + headerTitle + '</t></is></c></row>';
                    var headerRow2 = '<row r="2"><c r="A2" t="inlineStr"><is><t>Generated on: ' + new Date().toLocaleDateString() + '</t></is></c></row>';
                    var emptyRow = '<row r="3"></row>';
                    
                    // Insert header rows before the existing content
                    var sheetData = sheet.getElementsByTagName('sheetData')[0];
                    var rows = sheetData.getElementsByTagName('row');
                    
                    // Update row numbers for existing rows
                    for (var i = 0; i < rows.length; i++) {
                        var currentRow = parseInt(rows[i].getAttribute('r'));
                        rows[i].setAttribute('r', currentRow + 3);
                        
                        // Update cell references
                        var cells = rows[i].getElementsByTagName('c');
                        for (var j = 0; j < cells.length; j++) {
                            var cellRef = cells[j].getAttribute('r');
                            var newRow = parseInt(cellRef.match(/\d+/)[0]) + 3;
                            var newCellRef = cellRef.replace(/\d+/, newRow);
                            cells[j].setAttribute('r', newCellRef);
                        }
                    }
                    
                    // Insert new header rows
                    sheetData.innerHTML = headerRow1 + headerRow2 + emptyRow + sheetData.innerHTML;
                }
            },
            {
                extend: 'csvHtml5',
                className: 'btn btn-info btn-sm',
                text: '<i class="fa fa-file-text-o"></i> CSV',
                title: headerTitle,
                filename: baseFilename + '-products',
                exportOptions: {
                    columns: ':visible'
                },
                customize: function(csv) {
                    // Add header lines to CSV
                    var header = headerTitle + '\n';
                    header += 'Generated on: ' + new Date().toLocaleDateString() + '\n';
                    header += '\n'; // Empty line
                    return header + csv;
                }
            },
            {
                extend: 'print',
                className: 'btn btn-warning btn-sm',
                text: '<i class="fa fa-print"></i> Print',
                title: headerTitle,
                exportOptions: {
                    columns: ':visible'
                },
                customize: function(win) {
                    // Add custom header styling for print
                    $(win.document.body)
                        .css('font-size', '12pt')
                        .prepend(
                            '<div style="text-align:center; margin-bottom: 20px;">' +
                            '<h2 style="margin: 0; font-size: 18pt; font-weight: bold;">' + headerTitle + '</h2>' +
                            '<p style="margin: 5px 0; font-size: 12pt;">Generated on: ' + new Date().toLocaleDateString() + '</p>' +
                            '</div>'
                        );
                    
                    // Style the table
                    $(win.document.body).find('table')
                        .addClass('compact')
                        .css('font-size', '10pt');
                }
            }
        ],
        responsive: true,
        pageLength: 25,
        lengthMenu: [
            [10, 25, 50, 100, -1], 
            [
                "10", 
                "25", 
                "50", 
                "100", 
                "<?php echo direction('All Records','جميع السجلات') ?>"
            ]
        ],
        language: {
            lengthMenu: "<?php echo direction('Display _MENU_ records per page','عرض _MENU_ سجل في الصفحة') ?>",
            search: "<?php echo direction('Search in table:','البحث في الجدول:') ?>",
            info: "<?php echo direction('Showing _START_ to _END_ of _TOTAL_ records','عرض _START_ إلى _END_ من _TOTAL_ سجل') ?>",
            infoEmpty: "<?php echo direction('No records available','لا توجد سجلات متاحة') ?>",
            infoFiltered: "<?php echo direction('(filtered from _MAX_ total records)','(تم التصفية من _MAX_ سجل إجمالي)') ?>",
            paginate: {
                first: "<?php echo direction('First','الأول') ?>",
                last: "<?php echo direction('Last','الأخير') ?>",
                next: "<?php echo direction('Next','التالي') ?>",
                previous: "<?php echo direction('Previous','السابق') ?>"
            },
            zeroRecords: "<?php echo direction('No matching records found','لم يتم العثور على سجلات مطابقة') ?>",
            emptyTable: "<?php echo direction('No data available in table','لا توجد بيانات متاحة في الجدول') ?>"
        },
        order: [[0, 'asc']],
        columnDefs: [
            { targets: [1], className: 'title-column', orderable: true }, // Title column with wrapping
            { targets: [4, 6], className: 'text-right' }, // SKU and Cost Price columns
            { targets: [5], className: 'text-center' }    // Quantity column
        ],
        autoWidth: false,
        columns: [
            { width: "8%" },   // ID
            { width: "30%" },  // Title (wider for long text)
            { width: "15%" },  // Category
            { width: "15%" },  // Brand
            { width: "12%" },  // SKU
            { width: "10%" },  // Quantity
            { width: "10%" }   // Cost Price
        ]
    });
    
    // Hide the DataTables buttons initially as we have custom buttons
    $('.dt-buttons').hide();
    
    // Enhance the "All Records" option appearance
    setTimeout(function() {
        $('.dataTables_length select option').each(function() {
            if ($(this).val() == '-1') {
                $(this).css({
                    'font-weight': 'bold',
                    'color': '#007bff'
                });
            }
        });
    }, 100);
});

// Custom export functions
function exportToPDF() {
    $('#shopItemsTable').DataTable().button('.buttons-pdf').trigger();
}

function exportToExcel() {
    $('#shopItemsTable').DataTable().button('.buttons-excel').trigger();
}

function exportToCSV() {
    $('#shopItemsTable').DataTable().button('.buttons-csv').trigger();
}

function printTable() {
    $('#shopItemsTable').DataTable().button('.buttons-print').trigger();
}
</script>