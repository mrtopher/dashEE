<?php if(count($dashboards) > 1): ?>
	<div id="dashboards">
		<ul>
			<li><strong>Dashboards:</strong></li>
			<?php foreach($dashboards as $dash): ?>
				<?php $class = ($config_id == $dash->id) ? 'class="active"' : ''; ?>
				<li><?php echo anchor(module_url('dashee', 'index', array('config_id' => $dash->id)), $dash->name, $class); ?></li>
			<?php endforeach; ?>
		</ul>
		<div class="clear"></div>
	</div>
<?php endif; ?>

<?php if(!$locked): ?>
	<div class="rightNav">
		<div style="float: left; width: 100%;">
			<?php if($super_admin): ?>
				<span class="button"><?php echo anchor(module_url('dashee', 'settings'), lang('btn_settings'), 'class="submit"'); ?></span>
			<?php endif; ?>
			<span class="button"><a href="#widgets" class="submit" title="Button"><?php echo lang('btn_widgets'); ?></a></span>
			<span class="button"><a href="#member-settings" class="submit" title="Button"><img src="<?php echo $theme_url; ?>images/icon-cog.png" /></a></span>

			<?php if($state_buttons): ?>
				<span class="button" style="float:left;"><a href="#collapse" class="submit" title="Button"><?php echo lang('btn_collapse'); ?></a></span>
				<span class="button" style="float:left;"><a href="#expand" class="submit" title="Button"><?php echo lang('btn_expand'); ?></a></span>
			<?php endif; ?>
		</div>
		<div class="clear_left"></div>
	</div>
<?php endif; ?>

<div id="dashContainer">
	<div id="dashListing" style="display:none;">
		<div class="widgets">&nbsp;</div>
		<div class="dashboard">
			<h3>Manage Dashboard</h3>
			<ul>
				<li><?php echo anchor(module_url('dashee', 'reset_dashboard', array('config_id' => $config_id)), lang('trm_reset_dashboard')); ?></li>
				<li><a href="#rename-dashboard"><?php echo lang('trm_rename_dashboard'); ?></a></li>
				<li><a href="#save-layout"><?php echo lang('trm_save_dashboard'); ?></a></li>
				<!-- <li><a href="#"><?php echo lang('trm_copy_dashboard'); ?></a></li> -->
				<li><?php echo anchor(module_url('dashee', 'delete_dashboard', array('config_id' => $config_id)), lang('trm_delete_dashboard')); ?></li>
				<li><a href="#new-dashboard"><?php echo lang('trm_new_dashboard'); ?></a></li>
			</ul>
		</div>
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
	<?php echo form_open(module_url('dashee','update_member_settings', array('config_id' => $config_id)), array('id' => 'dasheeMemberSettingsForm')); ?>	
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

<div id="dashRenameDashboard" style="display:none;">
	<?php echo form_open(module_url('dashee', 'rename_dashboard', array('config_id' => $config_id)), array('id' => 'dasheeRenameDashboardForm')); ?>
		<p><label for="dashboard_name"><?php echo lang('lbl_dashboard_name'); ?>:</label>
		<input type="text" name="dashboard_name" id="dashboard_name" class="text ui-widget-content ui-corner-all" /></p>
	<?php echo form_close(); ?>
</div>

<div id="dashNewDashboard" style="display:none;">
	<?php echo form_open(module_url('dashee', 'new_dashboard'), array('id' => 'dasheeNewDashboardForm')); ?>
		<p><label for="dashboard_name"><?php echo lang('lbl_dashboard_name'); ?>:</label>
		<input type="text" name="dashboard_name" id="dashboard_name" class="text ui-widget-content ui-corner-all" /></p>

		<p>&nbsp;</p>
		<p>&nbsp;</p>

		<p><label><input type="radio" name="dashboard_config" value="default" checked> Default dashboard layout</label></p><br />
		<p><label><input type="radio" name="dashboard_config" value="empty"> Empty dashboard</label></p>
	<?php echo form_close(); ?>
</div>

<img src="<?php echo $theme_url; ?>images/ajax-loader.gif" id="dashLoader" />
<img src="<?php echo $theme_url; ?>images/widget-loader.gif" id="widgetLoader" />
<input type="hidden" name="config_id" id="config_id" value="<?php echo $config_id; ?>">