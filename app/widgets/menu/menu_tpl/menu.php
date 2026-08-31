<?php 
//debug($category);
$parent = 0;
$cat = null;
foreach((array)($category['childs'] ?? []) as $cat){
	$parent = $cat['parent_id'];
	
} 

$pid = \R::findOne('category', 'alias = ?', [$this->alias]);
$catid = $pid ? \R::findOne('category', 'id = ?', [(int)$pid->parent_id]) : null;
$parent_active = $pid ? \R::findOne('category', 'parent_id = ?', [(int)$pid->id]) : null;
//debug($cat);
?>
<li id="<?=$pid->id ?? 0?>" class="<?php if($category['parent_id'] == 0) { ?> first-level<?php } ?> menu-item <?php if($cat && $pid) { if($pid->parent_id ==$id or $pid->id ==$parent or ($catid->parent_id ?? 0) ==$id) { ?>down-arrow<?php }else{ ?>right-arrow<?php } }else{} ?>">
    <a href="<?=$category['alias'];?>"><?=$category['name'];?></a>
	
		<?php if(isset($category['childs'])): ?>			
			<ul class="sub-menu sub-menu--level_0 accordion-level" <?php if($pid && ($pid->parent_id ==$id or $pid->id ==$parent or ($catid->parent_id ?? 0) ==$id)) { ?>style="display:block"<?php }else{ ?>style="display:none"<?php } ?>>
				<?= $this->getMenuHtml($category['childs']);?>
			</ul>			
		<?php endif; ?>

</li>
