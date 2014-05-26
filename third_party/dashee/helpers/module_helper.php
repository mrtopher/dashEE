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