<script type="text/javascript" charset="utf-8">
$(function() {

	// Event to handle when widget task checkbox is checked.
	$(document).on('click', 'input.tasklist-toggle', function() {
		var $widget = $(this).parents('li');
		var $row = $(this).closest('tr');

		if($(this).is(':checked'))
		{
			var $status = 1;
		}
		else
		{
			var $status = 0;
		}

		$.ajax({
			type: 'GET',
			url: EE.BASE + '&C=addons_modules&M=show_module_cp&module=dashee&method=ajax_widget_get_proxy',
			data: {
				'wgtid': $widget.attr('id'),
				'mthd': 'ajax_update_task',
				'task_id': $(this).data('taskid'),
				'status': $status
			},
			success: function(html) {
				var $result = $.parseJSON(html);

				if($result.type == 'success') {
					if($status)
					{
						$row.children('td:nth-child(2)').css('text-decoration', 'line-through');
					}
					else
					{
						$row.children('td:nth-child(2)').css('text-decoration', 'none');
					}
					$.ee_notice($result.message, {type: 'success', open: false});
				}
				else {
					$.ee_notice($result.message, {type: 'error', open: true});
				}
			},
			error: function(html) {
				$.ee_notice("Nope, there was a problem.", {type: 'error', open: true});
			}
		});
	});

	// Event to handle when widget task delete link is clicked.
	$(document).on('click', '.tasklist-delete', function() {
		var $widget = $(this).parents('li');
		var $row = $(this).closest('tr');

		$.ajax({
			type: 'GET',
			url: EE.BASE + '&C=addons_modules&M=show_module_cp&module=dashee&method=ajax_widget_get_proxy',
			data: {
				'wgtid': $widget.attr('id'),
				'mthd': 'ajax_delete_task',
				'task_id': $(this).data('taskid')
			},
			success: function(html) {
				var $result = $.parseJSON(html);

				if($result.type == 'success') {
					$row.hide();
					$.ee_notice($result.message, {type: 'success', open: false});
				}
				else {
					$.ee_notice($result.message, {type: 'error', open: true});
				}
			},
			error: function(html) {
				$.ee_notice("Nope, there was a problem.", {type: 'error', open: true});
			}
		});
	});

});
</script>