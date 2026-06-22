<!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Типоразмеры</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?=ADMIN;?>">Главная</a></li>
			  <li class="breadcrumb-item"><a href="<?=ADMIN;?>/plagins">Компоненты</a></li>
              <li class="breadcrumb-item active">Типоразмеры</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->
<?php ?>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
				<div class="card-header d-flex p-0">
                    <h3 class="card-title p-3">Список типоразмеров</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">					
					<div class="table-responsive">						
						<table id="example" class="table table-bordered display" width="100%"></table>                    
					</div>						
				</div>				
			</div>
		</div>
	</div>
</section>
<script>
var dataSet = [
<?php foreach($sizes as $size) {
	$tipos = \R::findOne('tiposize', 'value_id = ?', [$size['id']]);
	if($tipos['title'] !="") { $s1 = "25"; }else{ $s1 = 0; }
	if($tipos['description'] !="") { $s2 = "25"; }else{ $s2 = 0; }
	if($tipos['content'] !="") { $s3 = "50"; }else{ $s3 = 0; }
	$seo = $s1+$s2+$s3; 
	if($seo == 25) { $itog_seo = "SEO $seo% <div class='progress progress-xs'><div class='progress-bar bg-danger progress-bar-striped' role='progressbar' aria-valuenow='".$seo."' aria-valuemin='0' aria-valuemax='100' style='width: ".$seo."%'><span class='sr-only'>".$seo."% Complete (warning)</span></div></div>"; }
	if($seo == 50) { $itog_seo = "SEO $seo% <div class='progress progress-xs'><div class='progress-bar bg-warning progress-bar-striped' role='progressbar' aria-valuenow='".$seo."' aria-valuemin='0' aria-valuemax='100' style='width: ".$seo."%'><span class='sr-only'>".$seo."% Complete (warning)</span></div></div>"; }
	if($seo == 75) { $itog_seo = "SEO $seo% <div class='progress progress-xs'><div class='progress-bar bg-warning progress-bar-striped' role='progressbar' aria-valuenow='".$seo."' aria-valuemin='0' aria-valuemax='100' style='width: ".$seo."%'><span class='sr-only'>".$seo."% Complete (warning)</span></div></div>"; }
	if($seo == 100) { $itog_seo = "SEO $seo% <div class='progress progress-xs'><div class='progress-bar bg-success progress-bar-striped' role='progressbar' aria-valuenow='".$seo."' aria-valuemin='0' aria-valuemax='100' style='width: ".$seo."%'><span class='sr-only'>".$seo."% Complete (warning)</span></div></div>"; }
	
	$option = "<a href='".ADMIN."/plagins/tiposize-edit?id=".$size["id"]."'><i class='fas fa-pencil-alt'></i></a> <a class='delete' href='".ADMIN."/delete-tiposize?id=".$size["id"]."'><i class='fas fa-times-circle text-danger'></i></a> <a target='_blank' href='/size/".$size["value"]."'><i class='fas fa-eye'></i></a></a>";
    $set .= '[ "'.$size["value"].'", "'.$seo.'", "'.$option.'" ],';
 } echo "".$set.""; ?>
 
];
 
$(document).ready(function() {
	
    var table = $('#example').DataTable( {		
		"lengthMenu": [[20, 50, 100, -1], [20, 50, 100, "Все"]],
		"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [ 1, 2 ] }],
        data: dataSet,
        columns: [
            { title: "Размер шины" },
            { title: "SEO", "width": "120px" },
			{ title: "Действия", "width": "60px" }
        ]		
    } );


} );
</script>