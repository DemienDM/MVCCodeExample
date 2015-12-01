<div class="news">
	<h2><?php echo Yii::t('interface', 'Статьи');?></h2>
<?php
	$this->widget('zii.widgets.CListView', array(
        'id' => 'articleList',
		'dataProvider' => $dataProvider,
		'ajaxUpdate' => false,
   		'itemView'     => '_article',
		'template'     => "{items}\n{pager}",
	)); 
?>	
</div>