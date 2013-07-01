<div class="update-area">
	<table>
		<thead><tr><th colspan="3">Tasks</th></tr></thead>
		<tbody>
			<?php foreach($tasks as $task): ?>
				<tr>
					<td width="5%"><input type="checkbox" class="tasklist-toggle" data-taskid="<?php echo $task->id; ?>" <?php echo $task->is_done ? 'checked' : ''; ?>></td>
					<td width="90%"  <?php echo $task->is_done ? 'style="text-decoration:line-through;"' : ''; ?>><?php echo $task->task; ?></td>
					<td width="5%"><a href="#" class="tasklist-delete" data-taskid="<?php echo $task->id; ?>">Delete</a>&nbsp;</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>	
</div>

<?php echo wgt_form_open('ajax_add_task'); ?>
	<table>
		<tr>
			<td width="85%"><input type="text" name="task" placeholder="Add new task"></td>
			<td><button type="submit">Add</button></td>
		</tr>
	</table>
<?php echo form_close(); ?>