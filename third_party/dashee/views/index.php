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
	<p><?php echo lang('conf_remove_widget'); ?></p>
</div>

<div id="dashSaveLayout" style="display:none;">
	<?php echo form_open('', array('id' => 'dasheeLayoutForm')); ?>	
		<p><label for="layout_name"><?php echo lang('lbl_layout_name'); ?>:</label>
		<input type="text" name="layout_name" id="layout_name" class="text ui-widget-content ui-corner-all" /></p>
		
		<p><label for="layout_desc"><?php echo lang('lbl_layout_desc'); ?>:</label>
		<input type="text" name="layout_desc" id="layout_desc" class="text ui-widget-content ui-corner-all" /></p>
	<?php echo form_close(); ?>
</div>

<img src="<?php echo $theme_url; ?>images/ajax-loader.gif" id="dashLoader" />
<img src="<?php echo $theme_url; ?>images/widget-loader.gif" id="widgetLoader" />