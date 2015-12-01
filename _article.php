<div class="item">
	<div class="pic"><?php echo CHtml::image($data->image);?></div>
	<?php echo CHtml::link($data->title, Yii::app()->createUrl('article/view', array('slug' => $data->slug)), array('class' => 'name'));?>
	<p><?php echo $data->short_text;?></p>
	<div class="cleaner"></div>
</div>