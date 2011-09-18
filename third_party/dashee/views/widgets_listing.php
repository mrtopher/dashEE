<?php

$this->table->set_template($cp_table_template);
$this->table->set_heading(
	'Widget',
	'Description',
	'Module',
	'Action'
	);

foreach($rows as $row)
{
	$this->table->add_row($row);			
}

echo $this->table->generate();

?>