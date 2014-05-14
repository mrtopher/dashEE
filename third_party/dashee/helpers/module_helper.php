<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * dashEE Module Helper File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Chris Monnat
 * @link		http://chrismonnat.com
 */

/**
 * Generate CP url for provided module.
 *
 * @param	string		$c				controller		
 * @param	string		$m				method		
 * @param	array		$variables		additional URL GET variables being passed		
 * @return 	string		$url			
 */
if ( ! function_exists('cp_url'))
{
	function cp_url($c, $m = NULL, $variables = array()) 
	{
		$EE =& get_instance();

		$s = 0;
		switch($EE->config->item('admin_session_type'))
		{
			case 's'	:
				$s = $EE->session->userdata('session_id', 0);
				break;
			case 'cs'	:
				$s = $EE->session->userdata('fingerprint', 0);
				break;
		}

		$url = $EE->config->item('cp_url') . '?S=' . $s . AMP . 'D=cp'. AMP . 'C=' . $c;

		if(!is_null($m))
		{
			$url .= AMP . 'M=' . $m;
		}
		
		foreach ($variables as $variable => $value) 
		{
			$url .= AMP . $variable . '=' . $value;
		}
		
		return $url;
	}
}

/**
 * Generate CP url for provided module.
 *
 * @param	string		$module			module your creating a CP url for		
 * @param	string		$action			method name you are linking too		
 * @param	array		$variables		additional URL GET variables being passed		
 * @return 	string		$url			
 */
if ( ! function_exists('module_url'))
{
	function module_url($module, $action = 'index', $variables = array()) 
	{
		$EE =& get_instance();

        if(version_compare(APP_VER, 2.8, '>=')) 
        {
        	$EE->load->helper('url');
        	$url = cp_url('cp/addons_modules/show_module_cp', array('module' => 'dashee', 'method' => $action) + $variables);
        }
        else
        {
			$s = 0;
			switch($EE->config->item('admin_session_type'))
			{
				case 's'	:
					$s = $EE->session->userdata('session_id', 0);
					break;
				case 'cs'	:
					$s = $EE->session->userdata('fingerprint', 0);
					break;
			}

			$url = $EE->config->item('cp_url') . '?S=' . $s . AMP . 'D=cp'. AMP . 'C=addons_modules' . AMP .'M=show_module_cp' . AMP .'module=' . $module . AMP . 'method=' . $action;
			
			foreach ($variables as $variable => $value) 
			{
				$url .= AMP . $variable . '=' . $value;
			}
		}

		return $url;
	}
}

/**
 * Generate URL trailor specifically form form_open() function.
 *
 * @param	string		$module			module your creating a CP url for		
 * @param	string		$action			method name you are linking too		
 */
function form_url($module, $action)
{
	return 'C=addons_modules' . AMP . 'M=show_module_cp' . AMP . 'module=' . $module . AMP . 'method=' . $action;
}

/* End of file module_helper.php */
/* Location: /system/expressionengine/third_party/dashee/helpers/module_helper.php */