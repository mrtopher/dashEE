<?php

/**
 * Tasklist Widget
 *
 * Interactive widget for managing simple task list.
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Widget
 * @author		Chris Monnat
 * @link		http://chrismonnat.com
 */

class Wgt_tasklist
{
	public $EE;
	public $widget_name 		= 'Task List';
	public $widget_description 	= 'Simple widget for managing a task list.';

	public $list_id;
	public $title;
	public $wclass;
	// public $settings;
	public $js;
	
	private $_model;
	private $_member_id;

	public function __construct()
	{
		$this->EE =& get_instance();

		$this->EE->load->model('task_model');
		$this->EE->load->helper(array('string', 'widget'));

		$this->_model = $this->EE->task_model;

		$this->settings = array(
			'list_id' 	=> random_string('alnum', 10),
			'title' 	=> 'Task List',
			);
		$this->wclass = 'contentMenu';
		$this->js = $this->EE->load->view('js/main', 0, TRUE);

		$this->_member_id = $this->EE->session->userdata('member_id');
	}
	
	/**
	 * Index Function
	 *
	 * @param	object
	 * @return 	string
	 */
	public function index($settings = NULL)
	{
		$this->list_id = $settings->list_id;
		$this->title = $settings->title;
	
		$widget_data = array(
			'list_id' 	=> $this->list_id,
			'tasks' 	=> $this->_model->get_tasks($this->_member_id, $this->list_id)
			);

		return $this->EE->load->view('index', $widget_data, TRUE);
	}

	/**
	 * AJAX METHOD
	 * 
	 * Add Task Function
	 * Attempt to add a new task to the DB.
	 *
	 * @return 	string
	 */
	public function ajax_add_task()
	{
		$params = array(
			'member_id'	=> $this->_member_id,
			'list_id'	=> $this->EE->input->post('list_id'),
			'task' 		=> $this->EE->input->post('task')
			);

		$this->_model->add_task($params);

		return 'Task added.';
	}

	/**
	 * AJAX METHOD
	 * 
	 * Update Task Status Function
	 * Attempt to mark the provided task as complete or incomplete in the DB.
	 *
	 * @return 	string
	 */
	public function ajax_update_task($params)
	{
		$task_id = $params['task_id'];

		if($task_id != '' AND is_numeric($task_id))
		{
			$params = array(
				'is_done' => $params['status']
				);

			$this->_model->edit_task($task_id, $params);

			return 'Task updated.';			
		}
		else
		{
			return 'No task specified.';
		}
	}

	/**
	 * AJAX METHOD
	 * 
	 * Delete Task Function
	 * Attempt to delete selected task from the DB.
	 *
	 * @return 	string
	 */
	public function ajax_delete_task($params)
	{
		$task_id = $params['task_id'];

		if($task_id != '' AND is_numeric($task_id))
		{
			$this->_model->delete_task($task_id);

			return 'Task deleted.';			
		}
		else
		{
			return 'No task specified.';
		}
	}
	
	/**
	 * Settings Form Function
	 * Generate settings form for widget.
	 *
	 * @param	object
	 * @return 	string
	 */
	public function settings_form($settings)
	{
		return form_open('', array('class' => 'dashForm')).'
			
			<p><label for="title">Widget Title:</label>
			<input type="text" name="title" value="'.$settings->title.'" /></p>
						
			<p><input type="submit" value="Save" /></p>
			
			'.form_close();
	}

	/**
	 * Widget Installer Function
	 *
	 * @param	object
	 * @return 	void
	 */
	public function widget_install()
	{
		$this->EE->load->dbforge();

		$fields = array(
			'id' => array(
				'type' 				=> 'INT',
				'unsigned' 			=> TRUE,
				'auto_increment' 	=> TRUE
				),
			'member_id' => array(
				'type'				=> 'INT',
				'unsigned' 			=> TRUE,
				'null'				=> FALSE
				),
			'list_id' => array(
				'type'				=> 'VARCHAR',
				'constraint'		=> '10',
				'null'				=> FALSE
				),
			'is_done' => array(
				'type'				=> 'TINYINT',
				'default'			=> '0',
				'null'				=> FALSE
				),
			'task' => array(
				'type'				=> 'VARCHAR',
				'constraint'		=> '255'
				),
			);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);

		$this->EE->dbforge->create_table('widget_tasklist', TRUE);
	}

	/**
	 * Widget Uninstaller Function
	 *
	 * @param	object
	 * @return 	void
	 */
	public function widget_uninstall()
	{
		$this->EE->db->delete('widget_tasklist', array('member_id' => $this->_member_id));

		$this->EE->load->dbforge();

		$this->EE->dbforge->drop_table('widget_tasklist');
	}
}
/* End of file wgt.tasklist.php */
/* Location: /system/expressionengine/third_party/dashee/widgets/tasklist/wgt.tasklist.php */