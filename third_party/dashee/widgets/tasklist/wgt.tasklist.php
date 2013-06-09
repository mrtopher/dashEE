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

	public $title;
	public $wclass;
	// public $settings;
	
	private $_model;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();

		$this->EE->load->model('task_model');
		$this->EE->load->helper('widget');

		$this->_model = $this->EE->task_model;

		$this->settings = array(
			'title' => 'Task List',
			);
		$this->wclass = 'contentMenu';
	}
	
	// ----------------------------------------------------------------

	/**
	 * Index Function
	 *
	 * @param	object
	 * @return 	string
	 */
	public function index($settings = NULL)
	{
		$this->title = $settings->title;
	
		$widget_data = array(
			'tasks' => $this->_model->get_tasks()
			);

		return $this->EE->load->view('index', $widget_data, TRUE);
	}

	/**
	 * Add Task Function
	 * Attempt to add a new task to the DB.
	 *
	 * @param	object
	 * @return 	string
	 */
	public function add_task()
	{
		$params = array(
			'task' => $this->EE->input->post('task')
			);

		$this->_model->add_task($params);

		echo 'Task added.';
		exit();
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

	// ----------------------------------------------------------------

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
			'task' => array(
				'type'				=> 'VARCHAR',
				'constraint'		=> '255'
				),
			);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);

		$this->EE->dbforge->create_table('widget_tasklist');
	}

	/**
	 * Widget Uninstaller Function
	 *
	 * @param	object
	 * @return 	void
	 */
	public function widget_uninstall()
	{
		$this->EE->load->dbforge();

		$this->EE->dbforge->drop_table('widget_tasklist');
	}
}
/* End of file wgt.tasklist.php */
/* Location: /system/expressionengine/third_party/dashee/widgets/tasklist/wgt.tasklist.php */