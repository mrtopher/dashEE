<?php 

echo form_open($action_url);

$col3_checked = '';
$col2_checked = '';
$col1_checked = '';

if($settings['columns'] == 3)
{
	$col3_checked = 'checked';
}
elseif($settings['columns'] == 2)
{
	$col2_checked = 'checked';
}
else
{
	$col1_checked = 'checked';
}

$this->table->set_template($cp_pad_table_template);
$this->table->template['thead_open'] = '<thead class="visualEscapism">';

$this->table->set_caption('General');

$this->table->set_heading(
   'Preference',
   'Setting');

$this->table->add_row(
   'Number of columns?',
   '<input type="radio" name="columns" '.$col3_checked.' value="3" /> 3'.NBS.NBS.NBS.NBS.
   '<input type="radio" name="columns" '.$col2_checked.' value="2" /> 2'.NBS.NBS.NBS.NBS.
   '<input type="radio" name="columns" '.$col1_checked.' value="1" /> 1'
   );

echo $this->table->generate();

?>

<div class="tableFooter">
	<?php echo form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit')); ?>
</div>


<?php 

echo form_close(); 

$this->table->set_template($cp_pad_table_template);
$this->table->template['thead_open'] = '<thead class="visualEscapism">';

$this->table->set_caption('Saved Layouts');

$this->table->set_heading(
   'Name',
   'Description',
   'Options');

if(count($layouts) > 0)
{
	foreach($layouts as $layout)
	{
		$this->table->add_row($layout->name,
								$layout->description ? $layout->description : '--',
								anchor('#', 'Use layout').' | '.anchor('#', 'Delete'));
	}
	
	echo $this->table->generate();
}
else
{

}
?>
<p>&nbsp;</p>
<?php
$this->table->set_template($cp_pad_table_template);
$this->table->template['thead_open'] = '<thead class="visualEscapism">';

$this->table->set_caption('Member Group Layouts');

$this->table->set_heading(
   'Member Group',
   'Description',
   'Layout');

if(count($member_groups) > 0)
{
	foreach($member_groups as $group)
	{
		$this->table->add_row($group->title,
								$group->description ? $group->description : '--',
								form_dropdown('', $opts_layouts));
	}
	
	echo $this->table->generate();
}
else
{

}


?>