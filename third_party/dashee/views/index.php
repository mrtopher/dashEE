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

<div id="dashMemberSettings" style="display:none;">
	<?php echo form_open($base_qs . AMP . 'method=update_member_settings', array('id' => 'dasheeMemberSettingsForm')); ?>	
		<p><label for="state_buttons">Hide expand/collapse all buttons?</label></p><br />
		<p><input type="radio" name="state_buttons" id="state_buttons" value="0" <?php echo $settings['state_buttons'] ? '' : 'checked'; ?> /> Yes&nbsp;&nbsp;&nbsp;&nbsp; 
		<input type="radio" name="state_buttons" value="1" <?php echo $settings['state_buttons'] ? 'checked' : ''; ?> /> No</p>
		
		<p>&nbsp;</p>

		<p><label for="columns">Number of columns?</label></p><br />
		<p><input type="radio" name="columns" id="columns" value="3" <?php echo $settings['columns'] == 3 ? 'checked' : ''; ?> /> 3&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="radio" name="columns" value="2" <?php echo $settings['columns'] == 2 ? 'checked' : ''; ?> /> 2&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="radio" name="columns" value="1" <?php echo $settings['columns'] == 1 ? 'checked' : ''; ?> /> 1</p>
	<?php echo form_close(); ?>
</div>

<img src="<?php echo $theme_url; ?>images/ajax-loader.gif" id="dashLoader" />
<img src="<?php echo $theme_url; ?>images/widget-loader.gif" id="widgetLoader" />