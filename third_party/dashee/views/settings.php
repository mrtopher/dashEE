<?php 

// generate user setting options section o
echo form_open($base_qs.AMP.'method=update_settings');

$this->table->set_template($cp_pad_table_template);
$this->table->template['thead_open'] = '<thead class="visualEscapism">';

$this->table->set_caption(lang('capGeneral'));

$this->table->set_heading(
   lang('thPreference'),
   lang('thSetting')
   );

$col_options = '';
for($i=3; $i>=1; --$i)
{
	$checked = '';
	if($settings['columns'] == $i)
	{
		$checked = 'checked';
	}
	$col_options .= '<input type="radio" name="columns" '.$checked.' value="'.$i.'" /> '.$i.NBS.NBS.NBS.NBS;
}
$this->table->add_row(
   lang('prefNumColumns'),
   $col_options
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
 	
	$this->table->set_caption(lang('capLayouts').' <a class="dashHelp" href="#">What\'s this?</a>');
	
	$this->table->set_heading(
	   lang('thName'),
	   lang('thDescription'),
	   lang('thOptions')
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

	echo '<div align="right">* ' . lang('default_layout') . '.</div>';
	echo '<p>&nbsp;</p>';
		
	echo form_open($base_qs.AMP.'method=update_group_defaults');
	
	$this->table->set_caption(lang('capGroupLayouts'));
	
	$this->table->set_heading(
	   lang('thMemberGroup'),
	   lang('thDescription'),
	   lang('thLocked'),
	   lang('thLayout')
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
			$group->title.' '.anchor($base_url.AMP.'method=reset_group_defaults'.AMP.'group_id='.$group->id, 'reset'),
			$group->description ? $group->description : '--',
			form_checkbox('group_locked['.$group->id.']','locked', $locked, ($group->id == 1 ? 'disabled="disabled"' : '')),
			form_dropdown('group_layouts['.$group->id.']', $opts_layouts, $layout_id)
			);
	}
	
	echo $this->table->generate();

?>

<!--<p><input type="checkbox" name="reset" value="yes" /> <?php echo lang('prefReset'); ?></p>-->

<div class="tableFooter">
	<?php echo form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit')); ?> 
</div>

<?php echo form_close(); ?>

<div id="dashConfirmLoad" style="display:none;">
	<p><?php echo lang('confLoadLayout'); ?></p>
</div>

<div id="dashConfirmDelete" style="display:none;">
	<p><?php echo lang('confDeleteLayout'); ?></p>
</div>

<div class="dashLayoutHelp" style="display:none;"><?php echo lang('help_layouts'); ?></div>

<?php endif; ?>