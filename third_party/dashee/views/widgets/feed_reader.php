<ul>
<?php
if ($error == FALSE){
	$i = 0;

	foreach ($rss->channel->item as $key => $item):
		if ($i++ >= $num) break;
		$title = trim($item->title);
		$link = trim($item->link);
?>
	<li class="item" title="<?=str_replace('"', '&quot;"', $title)?>">
		<a href="<?=$link?>" target="_blank"><?=$title?></a>
	</li>
<?php
	endforeach;
	}else{
	?>
	<li>
		<p align="center"><br>Feed doesn't exist. Please check URL</p>
	</li>
<?php		
	}
?>
</ul>