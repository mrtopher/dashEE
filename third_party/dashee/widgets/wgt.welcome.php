<?php

/**
 * dashEE Welcome Widget
 *
 * Display static information about dashEE module.
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Widget
 * @author		Chris Monnat
 * @link		http://chrismonnat.com
 */

class Wgt_welcome
{
	public $title;
	public $wclass;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->title 	= 'Welcome to dashEE';
		$this->wclass	= 'padded welcome';
	}
	
	// ----------------------------------------------------------------

	/**
	 * Index Function
	 *
	 * @return 	string
	 */
	public function index()
	{	
		return '
			<p>dashEE is the ultimate in ExpressionEngine control panel customization. The module comes with several default 
			widgets for making your life easier (located in the \'widgets\' directory). Don\'t see the functionality you\'re 
			looking for? You can develop your own widgets and even integrate dashEE with your custom modules. Learn more 
			by following the links below:</p>
			
			<p><a href="http://chrismonnat.com/code/dashee" target="_blank">Documentation</a> | <a href="https://github.com/mrtopher/dashEE">GitHub Repo</a></p>
		';
	}
}
/* End of file wgt.welcome.php */
/* Location: /system/expressionengine/third_party/dashee/widgets/wgt.welcome.php */