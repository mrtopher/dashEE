<?php

/**
 * Text Widget
 *
 * Display simple text.
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	DashEE Widget
 */

class Wgt_text
{
	public $title;
	public $wclass;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->wclass = 'padded';
	}
	
	// ----------------------------------------------------------------

	/**
	 * Index Function
	 *
	 * @param	object
	 * @return 	string
	 */
	public function index($settings = NULL)
	{
		$this->title = 'Instructions';
		
		return '

			<p>This is a basic, static text widget</p>
			<p>Only contains static text</p>

		';
	}

}
/* End of file wgt.text.php */
/* Location: /system/expressionengine/third_party/dashee/widgets/wgt.text.php */