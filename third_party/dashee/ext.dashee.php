<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * dashEE Extension
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Extension
 * @author		Chris Monnat
 * @link		http://chrismonnat.com
 */

class Dashee_ext 
{	
	public $settings 		= array();
	public $description		= 'Handle redirection and link remapping to alternate dashEE dashboard instead of defaule CP Home.';
	public $docs_url		= 'http://dash-ee.com';
	public $name			= 'dashEE';
	public $settings_exist	= 'n';
	public $version			= '1.2';
	public $required_by 	= array('module');
	
	private $_EE;
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		$this->_EE 		=& get_instance();
		$this->settings = $settings;

		if(version_compare(APP_VER, 2.6, '>=')) 
		{
			$this->_EE->load->library(array('localize', 'remember', 'session'));
		}
		
        $this->_base_qs     = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=dashee';
        $this->_base_url    = (defined('BASE') ? BASE : SELF).AMP.$this->_base_qs;
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
        $this->_EE->load->model('dashee_model');
        $settings = $this->_EE->dashee_model->get_module_settings();

        $url = $this->_EE->dashee_model->get_module_url();

        $js = '';

        // If another extension shares the same hook
        if ($this->_EE->extensions->last_call !== FALSE)
        {
            $js = $this->_EE->extensions->last_call;
        }

        $js .= "
            $().ready(function() {          
                $('ul#navigationTabs li.home a').attr('href', '" . htmlspecialchars_decode($url) . "');
                $('#breadCrumb ol li:nth-child(2) a').attr('href', '" . htmlspecialchars_decode($url) . "').html('" . $settings['crumb_term'] . "');
                $('#breadCrumb ol').show();
            });
        ";

        return $js;
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
		$this->_EE->load->model('dashee_model');
		$this->_EE->functions->redirect($this->_EE->dashee_model->get_module_url());
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Redirect CP home to DashEE
	 *
	 * @return NULL 
	 */
	public function sessions_end(&$data)
	{	
		$c = $this->_EE->input->get('C');

		if(REQ == 'CP' AND ($c == 'homepage' OR $c == ''))
		{
			$u = $data->userdata;

			$setting = $this->_EE->db->get_where('dashee_settings', array('site_id' => $u['site_id'], 'key' => 'redirect_admins'))->row();

			// redirect super admins?
			if($u['group_id'] == 1 && !$setting->value) return;

			// can user access modules at all?
			if($u['can_access_cp']=='y' && $u['can_access_addons']=='y' && $u['can_access_modules']=='y')
			{
				// is dashEE installed? fetch module_id and check user can access it
				$dashee_id = $this->_EE->db->where('module_name','DashEE')->get('modules')->row('module_id');

				if(empty($dashee_id)) return;

				if(@$u['assigned_modules'][$dashee_id] != TRUE && $u['group_id'] != 1) return;

				// all ok, build the url
				$s = 0;
				switch($this->_EE->config->item('admin_session_type'))
				{
					case 's'	:
						$s = $u['session_id'];
						break;
					case 'cs'	:
						$s = $u['fingerprint'];
						break;
				}

				header('Location: ' . SELF . str_replace('&amp;', '&', '?S=' . $s . AMP . 'D=cp' . AMP . $this->_base_qs));
				exit;
			}
		}
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
		return TRUE;
	}	

	// ----------------------------------------------------------------------

	/**
	 * Disable Extension
	 *
	 * @return void
	 */
	function disable_extension()
	{
		return TRUE;
	}

	// ----------------------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * @return  void
	 */
	function update_extension($current = '')
	{
		return TRUE;
	}		
}

/* End of file ext.dashee.php */
/* Location: /system/expressionengine/third_party/dashee/ext.dashee.php */