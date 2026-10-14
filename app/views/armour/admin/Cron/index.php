<?php if($_SESSION['user']['groups'] == 1) { ?>
<!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">CRON задания</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?=ADMIN;?>">Главная</a></li>
              <li class="breadcrumb-item active">Список CRON заданий</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
			<div class="menu_btn">
                <a href="<?=ADMIN;?>/cron/add" class="btn btn-primary"><i class="fa fa-fw fa-plus"></i> Добавить задание</a>
            </div>
            <div class="card">
				<div class="card-header">
                    <h3 class="card-title">Список CRON заданий</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
					<div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Название</th>
								<th>Системное имя</th>
								<th>URL-путь</th>                               
								<th>Статус</th>
								<th>Дата выполнения</th>
								<th>Действия</th>                                
                            </tr>
					    </thead>
                        <tbody>
                            <?php if (!$crons): ?>
                                <tr><td colspan="6" class="text-center text-muted">CRON-задания пока не настроены.</td></tr>
                            <?php endif; ?>
                            <?php foreach($crons as $cron): ?>
                                <?php $canRun = $cron['hide'] === 'show' && ($cron['url_params'] !== 'refresh-tovars' || trim((string)$cron['alias']) !== ''); ?>
                                <tr class="cont_td_znach">
                                    <td><?=h($cron['name']);?></td>
									<td><?=h($cron['url_params']);?></td>
									<td><?=h($cron['alias']);?></td>
									<td><?=$cron['hide'] === 'show' ? '<span class="badge badge-success">Активный</span>' : '<span class="badge badge-secondary">Неактивный</span>';?></td>
									<td><?=h($cron['date_update']);?></td>
									<td><a href="<?=ADMIN;?>/cron/edit?id=<?=$cron['id'];?>" title="Редактировать"><i class="fas fa-pencil-alt"></i></a> <a class="delete" href="<?=ADMIN;?>/cron/delete?id=<?=$cron['id'];?>" title="Удалить"><i class="fas fa-times-circle text-danger"></i></a> <button type="button" class="btn btn-link p-0 ml-1 js-cron-run" data-url="<?=PATH?>/cron/<?=h($cron['url_params']);?>?id=<?=$cron['id'];?>" title="<?=$canRun ? 'Запустить' : 'Сначала заполните URL источника и включите задание';?>" <?=$canRun ? '' : 'disabled';?>><i class="far fa-rocket-launch"></i></button> <span class="cron-run-status ml-2"><?=$canRun ? '' : '<span class="text-warning">Требуется настройка</span>';?></span></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                    </table>
					</div>                                    
				</div>
			</div>
		</div>
	</div>
</section>
<script>
$(document).ready(function(){
    $('.js-cron-run').on('click', function(event){
        event.preventDefault();
        var button = $(this);
        var status = button.siblings('.cron-run-status');
        button.prop('disabled', true);
        status.removeClass('text-success text-danger').addClass('text-muted').text('Выполняется…');
        $.ajax({url: button.data('url'), type: 'GET', dataType: 'json'})
            .done(function(response){
                status.removeClass('text-muted').addClass('text-success').text(response.message || 'Выполнено');
            })
            .fail(function(xhr){
                var message = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Ошибка ' + xhr.status;
                status.removeClass('text-muted').addClass('text-danger').text(message);
            })
            .always(function(){ button.prop('disabled', false); });
    });
});
</script>
<?php }else{ ?>
<div class="alert alert-warning alert-dismissible">
	<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
	<h5><i class="icon fas fa-exclamation-triangle"></i> Доступ закрыт!</h5>
		На этой странице есть ограничения доступа. Обратитесь к администратору.
</div>
<?php } ?>
