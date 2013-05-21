<?php 

// generate user setting options section o
echo form_open($base_qs.AMP.'method=update_settings');

$this->table->set_template($cp_pad_table_template);
$this->table->template['thead_open'] = '<thead class="visualEscapism">';

$this->table->set_caption(lang('cap_general'));

$this->table->set_heading(
   lang('th_preference'),
   lang('th_setting')
   );

$this->table->add_row(
   lang('pref_crumb_term'),
   '<input type="text" name="crumb_term" value="' . $settings['crumb_term'] . '" />'
   );

$this->table->add_row(
   lang('redirect_admins'),
   '<input type="radio" name="redirect_admins" value="1" ' . ($settings['redirect_admins'] ? 'checked' : '') . '/> Yes 
   <input type="radio" name="redirect_admins" value="0" ' . ($settings['redirect_admins'] ? '' : 'checked') . ' /> No'
   );

echo $this->table->generate();
$this->table->clear();
?>

<div class="tableFooter">
	<?php echo form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit')); ?>
</div>

<?php 

echo form_close();

if($is_admin):

	$this->table->set_caption(lang('cap_layouts').' <a class="dashLayoutHelp" href="#">' . lang('trm_whats_this') . '</a>');
	
	$this->table->set_heading(
	   lang('th_name'),
	   lang('th_description'),
	   lang('th_options')
	   );
	
	foreach($layouts as $layout)
	{
		$name = $layout->name;
		$options = anchor($base_url.AMP.'method=set_default_layout'.AMP.'layout_id='.$layout->id, 'Make default').' | '.
					anchor($base_url.AMP.'method=load_layout'.AMP.'layout_id='.$layout->id, 'Load', 'class="dashLoad"').' | '.
					anchor($base_url.AMP.'method=delete_layout'.AMP.'layout_id='.$layout->id, 'Delete', 'class="dashDelete"');
		if($layout->is_default)
		{
			$name = '<strong>' . $layout->name . '*</strong>';
			$options = anchor($base_url.AMP.'method=load_layout'.AMP.'layout_id='.$layout->id, 'Load', 'class="dashLoad"');
		}
		$this->table->add_row(
			$name,
			$layout->description ? $layout->description : '--',
			$options
			);
	}
	
	echo $this->table->generate();

	$this->table->clear();
	
	$this->table->template['thead_open'] = '<thead class="visualEscapism">';

	echo '<div align="right">* ' . lang('trm_default_layout') . '.</div>';
	echo '<p>&nbsp;</p>';
		
	echo form_open($base_qs.AMP.'method=update_group_defaults');
	
	$this->table->set_caption(lang('cap_group_layouts'));
	
	$this->table->set_heading(
	   lang('th_member_group'),
	   lang('th_description'),
	   lang('th_locked'),
	   lang('th_layout')
	   );
	
	foreach($member_groups as $group)
	{
		if(array_key_exists($group->id, $group_layouts))
		{
			$layout_id = $group_layouts[$group->id]['layout_id'];
			$locked = $group_layouts[$group->id]['locked'];
		}
		else
		{
			$layout_id = $default_id;
			$locked = FALSE;
		}

		$this->table->add_row(
			$group->title.' '.anchor($base_url.AMP.'method=reset_group_defaults'.AMP.'group_id='.$group->id, 'Reset', 'class="dashReset"'),
			$group->description ? $group->description : '--',
			form_checkbox('group_locked['.$group->id.']','locked', $locked, ($group->id == 1 ? 'disabled="disabled"' : '')) . ' ' . lang('lbl_lock') . ' (<a href="#" class="dashLockHelp">?</a>)',
			form_dropdown('group_layouts['.$group->id.']', $opts_layouts, $layout_id)
			);
	}
	
	echo $this->table->generate();

?>

<div class="tableFooter">
	<?php echo form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit')); ?> 
</div>

<?php echo form_close(); ?>

<div id="dashConfirmLoad" style="display:none;"><p><?php echo lang('conf_load_layout'); ?></p></div>
<div id="dashConfirmDelete" style="display:none;"><p><?php echo lang('conf_delete_layout'); ?></p></div>
<div id="dashConfirmReset" style="display:none;"><p><?php echo lang('conf_reset_layout'); ?></p></div>

<div id="dashLayoutHelp" style="display:none;"><?php echo lang('help_layouts'); ?></div>
<div id="dashLockHelp" style="display:none;"><?php echo lang('help_lock'); ?></div>

<?php endif; ?>