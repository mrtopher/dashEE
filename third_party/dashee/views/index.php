<div id="dashContainer">
	<div id="dashListing" style="display:none;">
		<div class="content">&nbsp;</div>
	</div>
	
	<div class="columns<?php echo $settings['columns']; ?>">
		<?php $i = 1; ?>
		<?php foreach($content as $col): ?>
			<ul id="column<?php echo $i; ?>" class="column"><?php echo $col; ?></ul>
			<?php ++$i; ?>
		<?php endforeach; ?>
	</div>
</div>

<div id="dashConfirm" style="display:none;">
	<p>Are you sure you want to remove this widget from your dashboard?</p>
</div>

<img src="<?php echo $theme_url; ?>images/ajax-loader.gif" id="dashLoader" />