<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Task Model
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Widget
 * @author		Chris Monnat
 * @link		http://chrismonnat.com
 */

class Task_model extends CI_Model 
{
	public $EE;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->EE =& get_instance();
	}

	// ----------------------------------------------------------------

	/**
	 * Get all tasks from DB.
	 *
	 * @param 	int 		$member_id
	 * @return 	object
	 */
	public function get_tasks($member_id)
	{
		return $this->EE->db->order_by('is_done ASC, task ASC')
			->get_where('widget_tasklist', array('member_id' => $member_id))
			->result();
	}

	/**
	 * Attempt to add new task to DB.
	 *
	 * @return 	void
	 */
	public function add_task($params)
	{
		$this->EE->db->insert('widget_tasklist', $params);
	}

	/**
	 * Attempt to update a task in the DB.
	 *
	 * @return 	void
	 */
	public function edit_task($task_id, $params)
	{
		$this->EE->db->update('widget_tasklist', $params, array('id' => $task_id));
	}

	/**
	 * Attempt to delete a task from the DB.
	 *
	 * @return 	void
	 */
	public function delete_task($task_id)
	{
		$this->EE->db->delete('widget_tasklist', array('id' => $task_id));
	}
}
/* End of file task_model.php */
/* Location: /system/expressionengine/third_party/dashee/widgets/tasklist/models/task_model.php */