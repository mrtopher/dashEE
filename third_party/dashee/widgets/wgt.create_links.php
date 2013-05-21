<?php

/**
 * EE Create Links Widget
 *
 * Display static create links just like on default EE CP home.
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Widget
 * @author		Chris Monnat
 * @link		http://chrismonnat.com
 */

class Wgt_create_links
{
	public $title;
	public $wclass;
		
	/**
	 * Constructor
	 */
	public function __construct()
	{	
		$this->title  	= lang('create');
		$this->wclass 	= 'contentMenu create';	
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Permissions Function
	 * Defines permissions needed for user to be able to add widget.
	 *
	 * @return 	bool
	 */
	public function permissions()
	{
		if(!EE()->cp->allowed_group('can_access_publish') && 
			(!EE()->cp->allowed_group('can_access_edit') && !EE()->cp->allowed_group('can_admin_templates')) && 
			 (!EE()->cp->allowed_group('can_admin_channels')  && ! EE()->cp->allowed_group('can_admin_sites')))
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
		$content = '<ul>';
		
		if(EE()->session->userdata['can_access_content'] == 'y')
		{
			$content .= '<li class="item"><a href="'.BASE.AMP.'D=cp'.AMP.'C=content_publish'.'">'.lang('entry').'</a></li>';
		}
		if(EE()->session->userdata['can_admin_templates'] == 'y')
		{
			$content .= '<li class="item"><a href="'.BASE.AMP.'D=cp'.AMP.'C=design'.AMP.'M=new_template">'.lang('template').'</a></li>
						<li class="group"><a href="'.BASE.AMP.'D=cp'.AMP.'C=design'.AMP.'M=new_template_group">'.lang('template_group').'</a></li>';
		}
		EE()->cp->get_installed_modules();
		EE()->load->model('member_model');
		if(isset(EE()->cp->installed_modules['pages']))
		{
			if(EE()->session->userdata('group_id') == 1 || EE()->member_model->can_access_module('pages'))
			{
				$content .= '<li class="item"><a href="'.BASE.AMP.'C=content_publish">'.lang('page').'</a></li>';
			}
		}
		if(EE()->session->userdata['can_admin_channels'] == 'y')
		{
			$content .= '<li class="group"><a href="'.BASE.AMP.'D=cp'.AMP.'C=admin_content'.AMP.'M=channel_add">'.lang('channel').'</a></li>';
		}
		
		$content .= '</ul>';
	
		return $content;
	}
}
/* End of file wgt.create_links.php */
/* Location: /system/expressionengine/third_party/dashee/widgets/wgt.create_links.php */