<?php

/**
 * RSS Feed Reader Widget
 *
 * Display RSS feed post links for provided feed URLs.
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Widget
 * @author		Chris Monnat
 * @link		http://chrismonnat.com
 */

class Wgt_feed_reader
{
	public $EE;
	public $title;
	public $wclass;
	public $settings;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();

		$this->settings = array(
			'url' => 'http://ellislab.com/blog/rss-feed',
			'num' => 5
			);
		$this->wclass = 'contentMenu';
	}
	
	// ----------------------------------------------------------------

	/**
	 * Index Function
	 *
	 * @param 	object
	 * @return 	string
	 */
	public function index($settings = NULL)
	{
		$this->EE->load->helper('text');
	
		libxml_use_internal_errors(true);
		
		if(!$rss = simplexml_load_file($settings->url))
		{
			$this->title 	= "Error";
			$vars['error'] 	= TRUE;	
		}
		else
		{
			$this->title = (string) $rss->channel->title;
			
			$vars['error'] 	= FALSE;
			$vars['rss'] 	= $rss;
			$vars['num'] 	= $settings->num;
		}

		return $this->EE->load->view('widgets/feed_reader', $vars, TRUE);
	}
	
	/**
	 * Settings Form Function
	 * Generate settings form for widget.
	 *
	 * @param 	object
	 * @return 	string
	 */
	public function settings_form($settings)
	{
		return form_open('', array('class' => 'dashForm')).'
			
			<p><label for="url">Feed URL:</label>
			<input type="text" name="url" value="'.$settings->url.'" /></p>
			
			<p><label for="num">Number of Posts:</label>
			<input type="text" name="num" value="'.$settings->num.'" /></p>
			
			<p><input type="submit" value="Save" /></p>
			
			'.form_close();
	}
}
/* End of file wgt.feed_reader.php */
/* Location: /system/expressionengine/third_party/dashee/widgets/wgt.feed_reader.php */