<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * dashEE Extension
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Extension
 * @author		Chris Monnat
 * @link		http://chrismonnat.com
 */

class Dashee_ext {
	
	public $settings 		= array();
	public $description		= 'Handle redirection and link remapping to alternate dashEE dashboard instead of defaule CP Home.';
	public $docs_url		= 'http://dash-ee.com';
	public $name			= 'dashEE';
	public $settings_exist	= 'n';
	public $version			= '1.0';
	
	private $_EE;
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		$this->_EE =& get_instance();
		$this->settings = $settings;
		
        $this->_base_qs     = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=dashee';
        $this->_base_url    = BASE .AMP .$this->_base_qs;
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @see http://codeigniter.com/user_guide/database/index.html for
	 * more information on the db class.
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		// Setup custom settings in this array.
		$this->settings = array();
		
		$hooks = array(
			'cp_css_end'		=> 'crumb_hide',
			'cp_js_end'			=> 'crumb_remap',
			'cp_member_login'	=> 'member_redirect',
			);

		foreach ($hooks as $hook => $method)
		{
			$data = array(
				'class'		=> __CLASS__,
				'method'	=> $method,
				'hook'		=> $hook,
				'settings'	=> serialize($this->settings),
				'version'	=> $this->version,
				'enabled'	=> 'y'
			);

			$this->_EE->db->insert('extensions', $data);			
		}
	}	

	// ----------------------------------------------------------------------
	
	/**
	 * Hide Breadcrumb Nav
	 *
	 * Adds CSS to CP to hide breadcrumb nav. Module will display with JS once 
	 * the page is fully loaded. This is to prevent the breadcrumb nav from being 
	 * displayed before it has been updated by module JS.
	 *
	 * @return string
	 */
	public function crumb_hide()
	{
		return '#breadCrumb ol { display:none; }';
	}
	
	// ----------------------------------------------------------------------

	/**
	 * Remap Breadcrumb Nav
	 *
	 * Returns JS to modify default main nav home button, breadcrumb href 
	 * and html attributes site wide so users are directed to module instead 
	 * of default EE CP Home.
	 *
	 * @return string
	 */
	public function crumb_remap()
	{
		return 'jQuery().ready(function() {'
			. '$("ul#navigationTabs li.home a").attr("href", "'.htmlspecialchars_decode($this->_base_url).'");'
			. '});';
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Redirect Members on Login
	 *
	 * Automatically redirects members to module instead of default EE CP Home 
	 * when logging into the CP.
	 *
	 * @return NULL 
	 */
	public function member_redirect()
	{
		$this->_EE->functions->redirect($this->_base_url);  
	}

	// ----------------------------------------------------------------------

	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
		$this->_EE->db->where('class', __CLASS__);
		$this->_EE->db->delete('extensions');
	}

	// ----------------------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return 	mixed	void on update / false if none
	 */
	function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
	}	
	
	// ----------------------------------------------------------------------
}

/* End of file ext.dashee.php */
/* Location: /system/expressionengine/third_party/dashee/ext.dashee.php */