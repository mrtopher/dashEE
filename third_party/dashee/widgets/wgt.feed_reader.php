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
	public $title;
	public $wclass;
	public $settings;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->settings = array(
			'url' => 'http://expressionengine.com/feeds/rss/eeblog/',
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
		$EE = get_instance();
		$EE->load->helper('text');
	
		$rss = simplexml_load_file($settings->url);		
		
		$display = '';
		$i = 0;
		foreach($rss->channel->item as $key => $item)
		{
			if($i >= $settings->num) { break; }
		
			$link		= trim($item->link);
			$title 		= trim($item->title);
			
			$display .= '<li class="item" title="'.str_replace('"', '&quot;"', $title).'">'.anchor($link, $title, 'target="_blank"').'</li>';
		
			++$i;
		}
		
		$this->title = ellipsize($rss->channel->title, 19, 1);
	
		return '
			<ul>'.$display.'</ul>
		';
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