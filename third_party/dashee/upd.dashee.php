<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * dashEE Module Install/Update File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Chris Monnat
 * @link		http://chrismonnat.com
 */

class Dashee_upd {
	
	public $version;
	
	private $_EE;
	private $_model;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->_EE =& get_instance();
		
        $this->_EE->load->add_package_path(PATH_THIRD .'dashee/');

        $this->_EE->load->model('dashee_model');
        $this->_model = $this->_EE->dashee_model;
        
        $this->version = $this->_model->get_package_version();
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Installation Method
	 *
	 * @return 	boolean 	TRUE
	 */
	public function install()
	{
		$this->_model->install_module();
		$this->_model->activate_extension();
		
		return TRUE;
	}

	// ----------------------------------------------------------------
	
	/**
	 * Uninstall
	 *
	 * @return 	boolean 	TRUE
	 */	
	public function uninstall()
	{
		$this->_model->uninstall_module();
		$this->_model->disable_extension();
		
		return TRUE;
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Module Updater
	 *
	 * @return 	boolean 	TRUE
	 */	
	public function update($current = '')
	{
		$this->_model->update_package($current);	
		$this->_model->update_extension($current);	
		
		return TRUE;
	}
	
}
/* End of file upd.dashee.php */
/* Location: /system/expressionengine/third_party/dashee/upd.dashee.php */