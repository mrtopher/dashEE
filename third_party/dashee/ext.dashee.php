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
	public $settings_exist	= 'y';
	public $version			= '1.1';
	
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
        $this->_base_url    = (defined('BASE') ? BASE : SELF).AMP.$this->_base_qs;
	}
	
	// ----------------------------------------------------------------------

	/**
	 * Extension Settings
	 *
	 * Display extension settings form
	 *
	 * @return void
	 */
	function settings()
	{
	    $settings = array();
	
	    $settings['redirect_admins'] = array('c', array('yes' => "Yes"), 'yes');
	
	    return $settings;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		// Setup custom settings in this array.
		$this->settings = array(
			'redirect_admins' => 'yes',
			);
		
		$hooks = array(
			'cp_css_end'		=> 'crumb_hide',
			'cp_js_end'			=> 'crumb_remap',
			'cp_member_login'	=> 'member_redirect',
			'sessions_end'		=> 'sessions_end',
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
		return $this->_EE->extensions->last_call . "
			$().ready(function() {			
				$('ul#navigationTabs li.home a').attr('href', '".htmlspecialchars_decode($this->_base_url)."');
				$('#breadCrumb ol li:nth-child(2) a').attr('href', '".htmlspecialchars_decode($this->_base_url)."').html('Dashboard');
				$('#breadCrumb ol').show();
			});
		";
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
	 * Redirect CP home to DashEE
	 *
	 * @return NULL 
	 */
	public function sessions_end( &$data )
	{
		if(REQ == 'CP' && $this->_EE->input->get('C') == 'homepage')
		{
			$u = $data->userdata;

			// redirect super admins?
			if($u['group_id'] == 1 && $this->settings['redirect_admins'] != 'yes') return;

			// can user access modules at all?
			if($u['can_access_cp']=='y' && $u['can_access_addons']=='y' && $u['can_access_modules']=='y')
			{
				// is dashEE installed? fetch module_id and check user can access it
				$dashee_id = $this->_EE->db->where('module_name','DashEE')->get('modules')->row('module_id');

				if(empty($dashee_id)) return;

				if( @$u['assigned_modules'][$dashee_id] != TRUE && $u['group_id'] != 1) return;

				// all ok, build the url
				$s = 0;
				if ($this->_EE->config->item('admin_session_type') != 'c')
				{
					$s = $u['session_id'];
				}
				header('Location: '.SELF. str_replace('&amp;', '&', '?S=' . $s . AMP . 'D=cp' . AMP . $this->_base_qs) );
				exit;
			}
		}
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
		if(version_compare($current, $this->version, '>='))
		{
			return FALSE;
		}
		
		if(version_compare($current, '1.1', '<'))
		{
			$this->update_extension_to_version_11();
		}
	}	
	
	// ----------------------------------------------------------------------
	
	/**
	 * Update Extension to Version 1.1
	 *
	 * Add session_end hook to extensions table.
	 *
	 * @return 	mixed	void on update / false if none
	 */
	function update_extension_to_version_11()
	{
		$data = array(
			'class'		=> __CLASS__,
			'method'	=> 'sessions_end',
			'hook'		=> 'sessions_end',
			'settings'	=> serialize($this->settings),
			'version'	=> $this->version,
			'enabled'	=> 'y'
			);

		$this->_EE->db->insert('extensions', $data);
	}
}

/* End of file ext.dashee.php */
/* Location: /system/expressionengine/third_party/dashee/ext.dashee.php */