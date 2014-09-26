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
	 *
	 * @access 		public
 	 * @return 		void
	 */
	public function __construct()
	{
		$this->wclass = 'padded';
	}
	
	/**
	 * Index Function
	 *
	 * @access 		public
	 * @param		obj 		$settings 		Object containing member widget settings.
	 * @return 		str
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