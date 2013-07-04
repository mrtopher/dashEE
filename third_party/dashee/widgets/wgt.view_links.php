<?php

/**
 * EE View Links Widget
 *
 * Display static view links just like on default EE CP home.
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Widget
 * @author		Chris Monnat
 * @link		http://chrismonnat.com
 */

class Wgt_view_links
{
	public $title;
	public $wclass;
	
	private $_EE;
	
	public function __construct()
	{
		$this->title 	= 'View';
		$this->wclass 	= 'contentMenu view';
	}
	
	/**
	 * Permissions Function
	 * Defines permissions needed for user to be able to add widget.
	 *
	 * @return 	bool
	 */
	public function permissions()
	{
		if(!EE()->cp->allowed_group('can_access_publish'))
		{
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Index Function
	 *
	 * @return 	string
	 */
	public function index()
	{
		return '
			<ul>
				<li class="site">'.anchor(EE()->config->item('site_url').EE()->config->item('index_page').'?URL='.EE()->config->item('site_url').EE()->config->item('index_page'), lang('site')).'</li>
				<li class="item"><a href="'.BASE.AMP.'D=cp'.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=comment">'.lang('recent_comments').'</a></li>
				<li class="item"><a href="'.BASE.AMP.'D=cp'.AMP.'C=content_edit'.AMP.'M=show_recent_entries'.AMP.'count=10">'.lang('recent_entries').'</a></li>
				<li class="resource"><a rel="external" href="'.config_item('doc_url').'">'.lang('user_guide').'</a></li>
				<li class="resource"><a rel="external" href="http://expressionengine.com/wiki/">'.lang('ee_wiki').'</a></li>
			</ul>
		';
	}
}
/* End of file wgt.view_links.php */
/* Location: /system/expressionengine/third_party/dashee/widgets/wgt.view_links.php */