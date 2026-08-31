<?php 
//debug($category);
foreach($category['childs'] as $cat){
	$parent = $cat['parent_id'];
	
} 

$pid = \R::findOne('category', 'alias = ?', [$this->alias]);
$catid = \R::findOne('category', 'id = ?', [$pid->parent_id]);
$parent_active = \R::findOne('category', 'parent_id = ?', [$pid->id]);
//debug($cat);
?>
<li id="<?=$pid->id?>" class="<?php if($category['parent_id'] == 0) { ?> first-level<?php } ?> menu-item <?php if($cat) { if($pid->parent_id ==$id or $pid->id ==$parent or $catid->parent_id ==$id) { ?>down-arrow<?php }else{ ?>right-arrow<?php } }else{} ?>">
    <a href="<?=$category['alias'];?>"><?=$category['name'];?></a>
	
		<?php if(isset($category['childs'])): ?>			
			<ul class="sub-menu sub-menu--level_0 accordion-level" <?php if($pid->parent_id ==$id or $pid->id ==$parent or $catid->parent_id ==$id) { ?>style="display:block"<?php }else{ ?>style="display:none"<?php } ?>>
				<?= $this->getMenuHtml($category['childs']);?>
			</ul>			
		<?php endif; ?>

</li>
