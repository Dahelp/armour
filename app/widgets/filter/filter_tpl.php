<?php foreach($this->groups as $group_id => $group_item):?>
	<?php if(!empty($this->attrs[$group_id])): ?>
	
    <section class="sky-form col-md-3">
        <div class="row1">
            <div class="col">
                <?php if(isset($this->attrs[$group_id])): ?>
				<select data-placeholder="<?=$group_item['title'];?>" multiple="multiple" class="form-control select js-select2">
                <?php foreach($this->attrs[$group_id] as $attr_id => $value): ?>

						<option value="<?=$attr_id;?>" data-badge=""<?=in_array((string)$attr_id, $filter ?? [], true) ? ' selected' : '';?>><?=$value;?></option>

                <?php endforeach; ?>
				</select>
                <?php endif; ?>				
            </div>
        </div>
    </section>
	<?php endif; ?>
<?php endforeach; ?>
