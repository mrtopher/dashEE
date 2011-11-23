<?php

/**
 * Blank Widget
 *
 * Display blank boilerplace widget that can be customized with settings.
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Widget
 * @author		Chris Monnat
 * @link		http://chrismonnat.com
 */

class Wgt_blank
{
	public $title;
	public $wclass;
	public $settings;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->settings = array(
			'title' => 'Blank Widget',
			'body'  => '<p>This is a blank module. You can fill it with whatever content you wish.</p>'
			);
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
		$this->title = $settings->title;
	
		return $settings->body;
	}
	
	/**
	 * Settings Form Function
	 * Generate settings form for widget.
	 *
	 * @param	object
	 * @return 	string
	 */
	public function settings_form($settings)
	{
		return form_open('', array('class' => 'dashForm')).'
			
			<p><label for="title">Widget Title:</label>
			<input type="text" name="title" value="'.$settings->title.'" /></p>
			
			<p><label for="body">Widget Body:</label>
			<textarea name="body">'.$settings->body.'</textarea></p>
			
			<p><input type="submit" value="Save" /></p>
			
			'.form_close();
	}

}
/* End of file wgt.blank.php */
/* Location: /system/expressionengine/third_party/dashee/widgets/wgt.blank.php */