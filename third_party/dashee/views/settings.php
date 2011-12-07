<?php 

// generate user setting options section o
echo form_open($base_qs.AMP.'method=update_settings');

$this->table->set_template($cp_pad_table_template);
$this->table->template['thead_open'] = '<thead class="visualEscapism">';

$this->table->set_caption('General');

$this->table->set_heading(
   'Preference',
   'Setting'
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
   'Number of columns?',
   $col_options
   );

echo $this->table->generate();

?>

<div class="tableFooter">
	<?php echo form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit')); ?>
</div>

<?php 

echo form_close();

if($is_admin)
{ 		
	$this->table->set_caption('Saved Layouts');
	
	$this->table->set_heading(
	   'Name',
	   'Description',
	   'Options'
	   );
	
	foreach($layouts as $layout)
	{
		$name = $layout->name;
		$options = anchor($base_url.AMP.'method=set_default_layout'.AMP.'layout_id='.$layout->id, 'Make default').' | '.
					anchor($base_url.AMP.'method=load_layout'.AMP.'layout_id='.$layout->id, 'Load', 'dashLoad').' | '.
					anchor($base_url.AMP.'method=delete_layout'.AMP.'layout_id='.$layout->id, 'Delete');
		if($layout->is_default)
		{
			$name = '<strong>' . $layout->name . '*</strong>';
			$options = anchor($base_url.AMP.'method=load_layout'.AMP.'layout_id='.$layout->id, 'Load');
		}
		$this->table->add_row(
			$name,
			$layout->description ? $layout->description : '--',
			$options
			);
	}
	
	echo $this->table->generate();
	echo '<div align="right">* Default layout.</div>';
	echo '<p>&nbsp;</p>';
		
	echo form_open($base_qs.AMP.'method=update_group_defaults');
	
	$this->table->set_caption('Default Member Group Layouts');
	
	$this->table->set_heading(
	   'Member Group',
	   'Description',
	   'Layout'
	   );
	
	foreach($member_groups as $group)
	{
		$this->table->add_row(
			$group->title,
			$group->description ? $group->description : '--',
			form_dropdown('group_layouts['.$group->id.']', $opts_layouts, $group_layouts[$group->id])
			);
	}
	
	echo $this->table->generate();
} 

?>

<div class="tableFooter">
	<?php echo form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit')); ?>
</div>

<?php echo form_close(); ?>

<div id="dashConfirmLoad" style="display:none;">
	<p>WARNING: this will reset your current dashboard and replace it with this saved layout.</p>
</div>

<div id="dashConfirmDelete" style="display:none;">
	<p>Are you sure you want to delete this layout?</p>
</div>
