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

$this->table->set_template($cp_table_template);
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


<?php echo form_close(); ?>