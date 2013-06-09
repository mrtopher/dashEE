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

	public function __construct()
	{
		parent::__construct();

		$this->EE =& get_instance();
	}

	public function get_tasks()
	{
		return $this->EE->db->get('widget_tasklist')->result();
	}

	public function add_task($params)
	{
		$this->EE->db->insert('widget_tasklist', $params);
	}
}
/* End of file task_model.php */
/* Location: /system/expressionengine/third_party/dashee/widgets/tasklist/models/task_model.php */