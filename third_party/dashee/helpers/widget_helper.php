<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * dashEE Widget Helper File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Chris Monnat
 * @link		http://chrismonnat.com
 */

function wgt_action_url()
{

}

/**
 * Widget Form Declaration
 *
 * Creates the opening portion of the form, EE CP Style
 *
 * @access	public
 * @param	string	the method of the widget file to be called
 * @param	array	a key/value pair of attributes
 * @param	array	a key/value pair hidden data
 * @return	string
 */	
function wgt_form_open($method, $attributes = array(), $hidden = array())
{
	$CI =& get_instance();
	
	$form = '<form action="#"';
	$class = 'wgt_form ';

	if(is_array($attributes))
	{
		if(isset($attributes['class']))
		{
			$class .= $attributes['class'];
			unset($attributes['class']);
		}

		if (!isset($attributes['method']))
		{
			$form .= ' method="post"';
		}
		
		foreach($attributes as $key => $val)
		{
			$form .= ' ' . $key . '="' . $val . '"';
		}
	}
	else
	{
		$form .= ' method="post" ' . $attributes;
	}

	$form .= " class='" . $class . "'>\n";
	
	if($CI->config->item('secure_forms') == 'y')
	{
		if (!is_array($hidden))
		{
			$hidden = array();
		}
		
		$hidden['XID'] = XID_SECURE_HASH;
	}

	$hidden['mthd'] = $method;

	if(is_array($hidden) AND count($hidden > 0))
	{
		$form .= form_hidden($hidden)."\n";
	}

	return $form;
}

/* End of file widget_helper.php */
/* Location: /system/expressionengine/third_party/dashee/helpers/widget_helper.php */