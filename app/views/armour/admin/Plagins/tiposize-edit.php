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
			  <li class="breadcrumb-item"><a href="<?=ADMIN;?>/plagins/tiposize">Типоразмеры</a></li>
              <li class="breadcrumb-item active">Редактирование типоразмера</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

<!-- Main content -->
<section class="content">
	<div class="row">
          <div class="col-12">
                <form action="<?=ADMIN;?>/plagins/tiposize-edit" method="post" data-toggle="validator">
                    <!-- Custom Tabs -->
            <div class="card">
              <div class="card-header d-flex p-0">
                <h3 class="card-title p-3">Редактировать типоразмер <?=h($values->value);?></h3>
              </div><!-- /.card-header -->
              <div class="card-body">
                    <div class="box-body">
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label" for="value">Размер шины</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" name="value" id="value" value="<?=h($values->value);?>" disabled>
								<input type="hidden" name="value_id" value="<?=$values->id;?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label" for="content">Описание</label>
							<div class="col-sm-9">
								<textarea name="content" id="editor1" cols="80" rows="10"><?=h($size->content);?></textarea>
							</div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label" for="title">Заголовок (Title)</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" name="title" id="title" value="<?=h($size->title);?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label" for="description">Ключевое описание (Description)</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" name="description" id="description" value="<?=h($size->description);?>">
                            </div>
                        </div>				
						<div class="form-group row">
                            <label class="col-sm-3 col-form-label" for="keywords">Ключевые слова (Keywords)</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" name="keywords" id="keywords" value="<?=h($size->keywords);?>">
                            </div>
                        </div>
						<div class="form-group row">
                            <label class="col-sm-3 col-form-label" for="hide">Статус активности <span class="text-danger">*</span></label>
							<div class="col-sm-9">
								<select class="form-control" name="hide">
									<option value="" />Выберите статус</option>
									<option value="show"<?php if($size->hide == 'show') echo ' selected'; ?> />Да</option>
									<option value="hide"<?php if($size->hide == 'hide') echo ' selected'; ?> />Нет</option>					
								</select>
							</div>
                        </div>
                    </div>				
				</div><!-- /.card-body -->			  
            </div>
            <div class="box-footer">
                <input type="hidden" name="id" value="<?=$values->id;?>">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
                </form>        
	</div>
	</div>
</section>
<!-- /.content -->