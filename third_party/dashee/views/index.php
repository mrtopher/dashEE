<div id="dashboards">
	<ul>
		<?php foreach($dashboards as $dash): ?>
			<?php $class = ($config_id == $dash->id) ? 'class="active"' : ''; ?>

			<li><?php echo anchor(cp_url('cp/addons_modules/show_module_cp', array('module' => 'dashee', 'config_id' => $dash->id)), $dash->name, $class); ?></li>
		<?php endforeach; ?>
		<li><a href="#new-dashboard"><i class="fa fa-plus"></i> <?php echo lang('trm_new_dashboard'); ?></a></li>
		<li><a class="opt" href="#rename-dashboard" title="Rename Dashboard"><i class="fa fa-pencil-square-o"></i></a></li>
		<li><a class="opt" href="<?php echo cp_url('cp/addons_modules/show_module_cp', array('module' => 'dashee', 'method' => 'reset_dashboard', 'config_id' => $config_id)); ?>" title="<?php echo lang('trm_reset_dashboard'); ?>"><i class="fa fa-refresh"></i></a></li>
		<li><a class="opt" href="<?php echo cp_url('cp/addons_modules/show_module_cp', array('module' => 'dashee', 'method' => 'delete_dashboard', 'config_id' => $config_id)); ?>" title="<?php echo lang('trm_delete_dashboard'); ?>"><i class="fa fa-times-circle"></i></a></li>
		<?php if(!$locked): ?>
			<?php if($state_buttons): ?>
				<li><a class="opt" href="#collapse" title="<?php echo lang('btn_collapse'); ?>"><i class="fa fa-minus-square"></i></a></li>
				<li><a class="opt" href="#expand" title="<?php echo lang('btn_expand'); ?>"><i class="fa fa-plus-square"></i></a></li>
			<?php endif; ?>
			<li><a class="opt" href="#member-settings" title="<?php echo lang('trm_dashboard_settings'); ?>"><i class="fa fa-cog"></i></a></li>
			<?php if($super_admin): ?>
				<li class="sub"><a class="opt" href="<?php echo cp_url('cp/addons_modules/show_module_cp', array('module' => 'dashee', 'method' => 'settings')); ?>" title="<?php echo lang('btn_settings'); ?>"><?php echo lang('btn_settings'); ?></i></a></li>
			<?php endif; ?>
			<li class="sub"><a class="opt" href="#save-layout" title="<?php echo lang('trm_save_dashboard'); ?>"><?php echo lang('trm_save_dashboard'); ?></i></a></li>
			<li class="sub"><a class="opt" href="#widgets" title="<?php echo lang('trm_widgets'); ?>"><?php echo lang('btn_widgets'); ?></i></a></li>
		<?php endif; ?>
	</ul>
	<div class="clear"></div>
</div>

<div id="dashContainer">
	<div id="dashListing" style="display:none;">
		<div class="widgets">&nbsp;</div>
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
	<?php echo form_open(form_url('dashee','update_member_settings'), array('id' => 'dasheeMemberSettingsForm')); ?>	
		<p><label for="state_buttons">Hide expand/collapse all buttons?</label></p><br />
		<p><input type="radio" name="state_buttons" id="state_buttons" value="0" <?php echo $settings['state_buttons'] ? '' : 'checked'; ?> /> Yes&nbsp;&nbsp;&nbsp;&nbsp; 
		<input type="radio" name="state_buttons" value="1" <?php echo $settings['state_buttons'] ? 'checked' : ''; ?> /> No</p>
		
		<p>&nbsp;</p>

		<p><label for="columns">Number of columns?</label></p><br />
		<p><input type="radio" name="columns" id="columns" value="3" <?php echo $settings['columns'] == 3 ? 'checked' : ''; ?> /> 3&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="radio" name="columns" value="2" <?php echo $settings['columns'] == 2 ? 'checked' : ''; ?> /> 2&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="radio" name="columns" value="1" <?php echo $settings['columns'] == 1 ? 'checked' : ''; ?> /> 1</p>

		<input type="hidden" name="config_id" value="<?php echo $config_id; ?>">
	<?php echo form_close(); ?>
</div>

<div id="dashRenameDashboard" style="display:none;">
	<?php echo form_open(form_url('dashee', 'rename_dashboard'), array('id' => 'dasheeRenameDashboardForm')); ?>
		<p><label for="dashboard_name"><?php echo lang('lbl_dashboard_name'); ?>:</label>
		<input type="text" name="dashboard_name" id="dashboard_name" class="text ui-widget-content ui-corner-all" /></p>
		<input type="hidden" name="config_id" value="<?php echo $config_id; ?>">
	<?php echo form_close(); ?>
</div>

<div id="dashNewDashboard" style="display:none;">
	<?php echo form_open(form_url('dashee', 'new_dashboard'), array('id' => 'dasheeNewDashboardForm')); ?>
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