<?php

/**
 * EE Modify Links Widget
 *
 * Display static modify (or delete) links just like on default EE CP home.
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Widget
 * @author		Chris Monnat
 * @link		http://chrismonnat.com
 */

class Wgt_modify_links
{
	public $EE;
	public $title;
	public $wclass;
	
	private $_EE;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();

		$this->title 	= lang('modify').' <span class="subtext">'.lang('or_delete').'</span>';
		$this->wclass 	= 'contentMenu modify';
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
		if(!$this->EE->cp->allowed_group('can_access_publish') && 
			(!$this->EE->cp->allowed_group('can_access_edit') && !$this->EE->cp->allowed_group('can_admin_templates')))
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
		$content = '<ul>
					<li class="item"><a href="'.BASE.AMP.'D=cp'.AMP.'C=content_edit'.'">'.lang('entry').'</a></li>';
		
		// templates
		
		// template groups
		if($this->EE->session->userdata['can_admin_templates'] == 'y')
		{
			$content .= '<li class="group"><a href="'.BASE.AMP.'D=cp'.AMP.'C=design'.AMP.'M=edit_template_group">'.lang('template_group').'</a></li>';
		}
		
		// pages
		$this->EE->cp->get_installed_modules();
		$this->EE->load->model('member_model');
		if(isset($this->EE->cp->installed_modules['pages']))
		{
			if($this->EE->session->userdata('group_id') == 1 || $this->EE->member_model->can_access_module('pages'))
			{
				$content .= '<li class="item"><a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=pages">'.lang('page').'</a></li>';
			}
		}

		// recent entry
		$entry = $this->EE->db->select('entry_id, channel_id')
			->from('channel_titles')
			->order_by('entry_date DESC')
			->limit(1)
			->get()->row();

		if($entry)
		{
			$content .= '<li class="group"><a href="'.BASE.AMP.'C=content_publish'.AMP.'M=entry_form'.AMP.'channel_id='.$entry->channel_id.AMP.'entry_id='.$entry->entry_id.'">'.lang('most_recent_entry').'</a></li>';
		}
		
		// comments - doesn't work for some reason... need to revisit
		/*if($this->_EE->db->table_exists('comments') && $this->_EE->cp->allowed_group('can_moderate_comments'))
		{
			$content .= '<li class="item"><a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=comment'.AMP.'status=p">'.lang('total_validating_comments').'</a></li>';
		}*/
	
		return $content;
	}
}
/* End of file wgt.modify_links.php */
/* Location: /system/expressionengine/third_party/dashee/widgets/wgt.modify_links.php */