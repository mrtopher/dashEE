<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * dashEE Module Control Panel File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Chris Monnat
 * @link		http://chrismonnat.com
 */

class Dashee_mcp {
	
	public $return_data;
	
	private $_EE;
	private $_model;
	private $_base_qs;
	private $_base_url;
	private $_theme_url;
	private $_css_url;
	private $_js_url;
	private $_member_id;
	private $_super_admin = FALSE;
	private $_settings;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->_EE =& get_instance();
		
        $this->_EE->load->model('dashee_model');
        $this->_model = $this->_EE->dashee_model;
		
        $this->_base_qs     = 'C=addons_modules' .AMP .'M=show_module_cp' .AMP .'module=dashee';
        $this->_base_url    = BASE .AMP .$this->_base_qs;
        $this->_theme_url   = $this->_model->get_package_theme_url();
        $this->_css_url   	= $this->_theme_url .'css/cp.css';
        //$this->_js_url   	= $this->_theme_url .'js/dashee.js';
        $this->_js_url   	= $this->_theme_url .'js/dashee.min.js';
        
        $this->_member_id = $this->_EE->session->userdata('member_id');
        if($this->_EE->session->userdata('group_id') == 1)
        {
        	$this->_super_admin = TRUE;
        }
        
        // get current members dash configuration for use throughout module
        $this->_get_member_settings($this->_member_id);
	}

	// ----------------------------------------------------------------

	/**
	 * Index Function
	 *
	 * @return 	void
	 */
	public function index()
	{
        $this->_EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$this->_css_url.'" />');

        // is the member_group layout locked?
        if($this->_settings['locked'] == FALSE)
        {
        	$this->_EE->cp->add_to_head('<script type="text/javascript" src="'.$this->_js_url.'"></script>');
        }
	
		$this->_EE->cp->set_variable('cp_page_title', lang('dashee_term'));
		
		// set button data appropriately based on type of user
		$button_data['btn_collapse'] = '#collapse';
		$button_data['btn_expand'] 	 = '#expand';
			
		if($this->_super_admin)
		{
			$button_data['btn_save'] = '#save-layout'; 
		}
		
		$button_data['btn_widgets']  = '#widgets'; 
		$button_data['btn_settings'] = $this->_base_url.AMP.'method=settings';

        // is the member_group layout locked?
        if($this->_settings['locked'] == FALSE) $this->_EE->cp->set_right_nav($button_data);
		
		// override default breadcrumb display to make module look like default CP homepage
		$this->_EE->javascript->output("
			$('#breadCrumb ol li').slice(2).remove();
			$('#breadCrumb ol li:last-child').attr('class', 'last').html('Dashboard');
			");
		
		$msg = $this->_EE->session->flashdata('dashee_msg');
		if($msg != '')
		{
			$this->_EE->javascript->output("
				$.ee_notice('".$msg."', {type: 'success'});
				");
		}
		
		$this->_EE->javascript->compile();
		
		// load widgets
		$widgets = $this->_widget_loader($this->_settings['widgets']);
		
		$page_data = array(
			'settings' 	=> $this->_settings, 
			'content' 	=> $widgets, 
			'theme_url' => $this->_theme_url
			);
		
		return $this->_EE->load->view('index', $page_data, TRUE);
	}
	
	/**
	 * Settings Function
	 * Display module settings form.
	 *
	 * @return 	void
	 */
	public function settings()
	{
		$this->_EE->load->library('table');
	
        $this->_EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$this->_theme_url.'css/settings.css" />');
        $this->_EE->cp->add_to_head('<script type="text/javascript" src="'.$this->_js_url.'"></script>');
	
		$this->_EE->cp->set_variable('cp_page_title', lang('dashee_settings'));
		
		$this->_EE->cp->set_breadcrumb($this->_base_url, lang('btn_settings'));
		
		// override default breadcrumb display
		$this->_EE->javascript->output("
			$('#breadCrumb ol li').slice(2,4).remove();
		");
		$this->_EE->javascript->compile();
		
		$msg = $this->_EE->session->flashdata('dashee_msg');
		if($msg != '')
		{
			$this->_EE->javascript->output("
				$.ee_notice('".$msg."', {type: 'success'});
			");
		}
		
		// get layout options for display and use as dropdown options
		$layouts 		= array();
		$layout_options = array();
		if($this->_EE->session->userdata('group_id') == 1)
		{
			$layouts = $this->_model->get_all_layouts();
			$layout_options = array();
			foreach($layouts as $layout)
			{
				$layout_options[$layout->id] = $layout->name;
			}
		}
		
		$page_data = array(
			'base_qs' 		=> $this->_base_qs,
			'base_url'		=> $this->_base_url,
			'settings' 		=> $this->_settings,
			'is_admin'		=> $this->_super_admin,
			'layouts' 		=> $layouts,
			'opts_layouts' 	=> $layout_options,
			'member_groups'	=> $this->_model->get_member_groups(),
			'default_id' 	=> $this->_model->get_default_layout()->id,
			'group_layouts' => $this->_model->get_all_group_layouts()
			);
		return $this->_EE->load->view('settings', $page_data, TRUE);
	}
	
	/**
	 * Update Settings Function
	 * Attempt to save users module settings to DB.
	 *
	 * @return 	void
	 */
	public function update_settings()
	{
		$post_columns = $this->_EE->input->post('columns');
		if($post_columns != '' AND is_numeric($post_columns) AND $post_columns <= 3)
		{
			$current = $this->_settings['columns'];
			$new = $this->_EE->input->post('columns');
			$widgets = $this->_settings['widgets'];
			
			// modify widget placement based on newly selected # of columns
			if($new < $current)
			{
				if($new > 1)
				{
					foreach($widgets[3] as $id => $settings)
					{
						$widgets[2][$id] = $settings;
					}
					unset($widgets[3]);
				}
				else
				{
					if(array_key_exists(3,$widgets) && array_key_exists(2,$widgets))
					{
						$combined = array_merge($widgets[2],$widgets[3]);	
					}
					else
					{
						$combined = $widgets[2];
					}
					
					foreach($combined as $id => $settings)
					{
						$widgets[1][$id] = $settings;
					}

					unset($widgets[3]);
					unset($widgets[2]);
				}
			}
			elseif($new > $current)
			{
				if($new == 2)
				{
					$widgets[2] = array();
				}
				else
				{
					$widgets[3] = array();
				}
			}
			
			// save new config to DB
			$this->_settings['widgets'] = $widgets;
			$this->_settings['columns'] = $new;
			$this->_update_member(FALSE);
			
			$this->_EE->session->set_flashdata('dashee_msg', 'Your settings have been updated.');
		}
		
		$this->_EE->functions->redirect($this->_base_url);
	}
		
	/**
	 * AJAX METHOD
	 * Get listing of all available widgets from installed modules.
	 *
	 * @return 	NULL
	 */
	public function get_widget_listing()
	{
		$this->_EE->load->library('table');
	
		$map = directory_map(PATH_THIRD, 2);
		
		// Fetch installed modules.
		$installed_mods = $this->_model->get_installed_modules();
		
		// Determine which installed modules have widgets associated with them.
		$mods_with_widgets = array();
		foreach($map as $third_party => $jabber)
		{
			if(is_array($jabber))
			{
				if(in_array($third_party, $installed_mods) AND in_array('widgets', $jabber))
				{
					$mods_with_widgets[] = $third_party;
				}
			}
		}
		
		// Get array of all widgets of installed modules.
		$table_data = array();
		asort($mods_with_widgets);
		foreach($mods_with_widgets as $mod)
		{
			$path = PATH_THIRD.$mod.'/widgets/';
			$map = directory_map(PATH_THIRD.$mod.'/widgets', 1);
		
			if(is_array($map))
			{
				$col = 1;
				asort($map);
				foreach($map as $widget)
				{
					$this->_EE->lang->loadfile($mod);
					
					// check widget permissions before adding to table and skip if user doesn't have permission
					$obj = $this->_get_widget_object($mod, $widget);
					if(method_exists($obj, 'permissions') && !$obj->permissions())
					{
						continue;
					}
					
					$table_data[] = array(
						lang($this->_format_filename($widget).'_name'),
						lang($this->_format_filename($widget).'_description'),
						lang(strtolower($mod).'_module_name'),
						anchor($this->_base_url.AMP.'method=add_widget'.AMP.'mod='.$mod.AMP.'wgt='.$widget, 'Add')
						);			
				}
			}
		}
		
		echo $this->_EE->load->view('widgets_listing', array('rows' => $table_data), TRUE);
		exit();
	}
	
	/**
	 * Add Widget's Package Path
	 *
	 * Makes it possible for widgets to use $EE->load->view(), etc
	 *
	 * Should be called right before calling a widget's index() funciton
	 */
	private function _add_widget_package_path($name)
	{
		$path = PATH_THIRD . $name . '/';
		$this->_EE->load->add_package_path($path);

		// manually add the view path if this is less than EE 2.1.5
		if (version_compare(APP_VER, '2.1.5', '<'))
		{
			$this->_EE->load->_ci_view_path = $path . 'views/';
		}
	}
	
	/**
	 * Add selected widget to users dashboard and update config.
	 *
	 * @return 	void
	 */
	public function add_widget()
	{
		$mod = $this->_EE->input->get('mod');
		$wgt = $this->_EE->input->get('wgt');

		if(isset($mod) AND isset($wgt))
		{
			$obj = $this->_get_widget_object($mod, $wgt);
			
			// determine which column has the least number of widgets in it so you can add the 
			// new one to the one with the least
			$totals = array();
			for($i=1; $i <= $this->_settings['columns']; ++$i)
			{
				$totals[$i] = @count($this->_settings['widgets'][$i]);
			}
			
			$col = array_keys($totals, min($totals));
		
			/*echo '<pre>';
			print_r($totals);
			exit();*/

			$new_widget = array(
				'mod' => $mod,
				'wgt' => $wgt,				
				);
		
			// add widget settings to config if present
			if(isset($obj->settings))
			{
				$new_widget['stng'] = json_encode($obj->settings);
			}
			
			$this->_settings['widgets'][$col[0]][] = $new_widget;
			
			// update members dashboard config in DB
			$this->_update_member();
		}
		
		$this->_EE->session->set_flashdata('message_success', lang('widget_added'));
		$this->_EE->functions->redirect($this->_base_url);
	}
	
	/**
	 * AJAX METHOD
	 * Remove selected widget from users dashboard and update settings.
	 *
	 * @return 	NULL
	 */
	public function remove_widget()
	{
		$col = $this->_EE->input->get('col');
		$wgt = $this->_EE->input->get('wgt');

		if(isset($col) AND isset($wgt))
		{
			unset($this->_settings['widgets'][$col][$wgt]);
			$this->_update_member(FALSE);
		}
	}
	
	/**
	 * AJAX METHOD
	 * Update widget order and column placement in DB.
	 *
	 * @return 	NULL
	 */
	public function update_widget_order()
	{
		$order = $this->_EE->input->get('order');
		
		if($order)
		{
			$widgets		= explode('|', $order);
			$current 		= $this->_settings['widgets'];
			$widgets_only 	= array();
			$new			= array();
			
			// get widget only settings in accessable array (without column number in front)
			foreach($current as $col => $wgts)
			{
				foreach($wgts as $id => $settings)
				{
					$widgets_only[$id] = $settings;
				}
			}
			
			// loop through widgets, separate based on delimiter and create new array based on submitted 
			foreach($widgets as $widget)
			{
				$pieces = explode(':', $widget);
				$new[$pieces[0]][$pieces[1]] = $widgets_only[$pieces[1]];
			}
			
			$this->_settings['widgets'] = $new;
			$this->_update_member(FALSE);
		}
		
		return TRUE;
	}
	
	/**
	 * AJAX METHOD
	 * Display settings options for selected widget.
	 *
	 * @return 	NULL
	 */
	public function widget_settings()
	{
		$col = $this->_EE->input->get('col');
		$wgt = $this->_EE->input->get('wgt');

		if(isset($col) AND isset($wgt))
		{
			$widget = $this->_settings['widgets'][$col][$wgt];
			
			$obj = $this->_get_widget_object($widget['mod'],$widget['wgt']);
			echo $obj->settings_form(json_decode($widget['stng']));
			exit();
		}
	}
	
	/**
	 * AJAX METHOD
	 * Attempt to update a widgets settings.
	 *
	 * @return 	NULL
	 */
	public function update_widget_settings()
	{
		$data 		= $_POST;
		$settings 	= array();
		$widget 	= $this->_settings['widgets'][$data['col']][$data['wgt']];
				
		foreach($data as $field => $value)
		{
			$settings[$field] = $value;
		}
	
		$settings_json = json_encode($settings);
		$this->_settings['widgets'][$data['col']][$data['wgt']]['stng'] = $settings_json;
		$this->_update_member(FALSE);
	
		$obj = $this->_get_widget_object($widget['mod'],$widget['wgt']);
		$this->_add_widget_package_path($widget['mod']);
		$content = $obj->index(json_decode($settings_json));
		$result = array(
			'title'		=> $obj->title,
			'content' 	=> $content
			);
		echo json_encode($result);
		exit();
	}
	
	/**
	 * AJAX METHOD
	 * Attempt to save current dashboard layout in DB.
	 *
	 * @return 	NULL
	 */
	public function save_layout()
	{
		$name 			= $this->_EE->input->post('layout_name');
		$description 	= $this->_EE->input->post('layout_desc');
		
		if($this->_super_admin AND $name != '')
		{
			$this->_model->add_layout($name, $description, $this->_settings);
		}
	}
	
	/**
	 * Change default layout in DB.
	 *
	 * @return 	void
	 */
	public function set_default_layout()
	{
		$layout_id = $this->_EE->input->get('layout_id');
		
		if($this->_super_admin AND $layout_id != '' AND is_numeric($layout_id))
		{
			$this->_model->set_default_layout($layout_id);
			
			$this->_EE->session->set_flashdata('dashee_msg', lang('flashLayoutUpdated'));
		}
		else
		{
			$this->_EE->session->set_flashdata('dashee_msg', lang('flashLayoutNotUpdated'));
		}
		
		$this->_EE->functions->redirect($this->_base_url.AMP.'method=settings');
	}
	
	/**
	 * Load selected saved layout for current user.
	 *
	 * @return 	void
	 */
	public function load_layout()
	{
		$layout_id = $this->_EE->input->get('layout_id');
		
		if($this->_super_admin AND $layout_id != '' AND is_numeric($layout_id))
		{
			$layout = $this->_model->get_layout($layout_id);
			$this->_settings = json_decode($layout->config);
			
			$this->_update_member(FALSE);
			
			$this->_EE->session->set_flashdata('dashee_msg', $layout->name . lang('flashLayoutLoaded'));
		}
		else
		{
			$this->_EE->session->set_flashdata('dashee_msg', lang('flashLayoutNotLoaded'));
		}
		
		$this->_EE->functions->redirect($this->_base_url);
	}
	
	/**
	 * Delete selected saved layout from DB.
	 *
	 * @return 	void
	 */
	public function delete_layout()
	{
		$layout_id = $this->_EE->input->get('layout_id');
		
		if($this->_super_admin AND $layout_id != '' AND is_numeric($layout_id))
		{
			$layout = $this->_model->get_layout($layout_id);
			
			if(!$layout->is_default)
			{
				$this->_model->delete_layout($layout->id);

				$this->_EE->session->set_flashdata('dashee_msg', $layout->name . lang('flashLayoutDeleted'));
			}
		}
		else
		{
			$this->_EE->session->set_flashdata('dashee_msg', lang('flashLayoutNotDeleted'));
		}
		
		$this->_EE->functions->redirect($this->_base_url.AMP.'method=settings');
	}
	
	/**
	 * Update Member Group Defaults Function
	 * Attempt to save member group default settings to DB.
	 *
	 * @return 	void
	 */
	public function update_group_defaults()
	{
		if($this->_super_admin == FALSE)
		{
			show_error(lang('unauthorized_access'));
		}

		$group_layouts = $this->_EE->input->post('group_layouts');
		$group_locked = $this->_EE->input->post('group_locked');
		
		if($group_layouts != '' AND is_array($group_layouts))
		{
			$this->_model->update_group_layouts($group_layouts, $group_locked);
		
			$this->_EE->session->set_flashdata('dashee_msg', lang('flashGroupDefaultUpdated'));
		}
		else
		{
			$this->_EE->session->set_flashdata('dashee_msg', lang('flashGroupDefaultNotUpdated'));
		}
		
		$this->_EE->functions->redirect($this->_base_url.AMP.'method=settings');
	}
	
	/**
	 * Reset layout for a member group
	 *
	 * @return 	void
	 */
	public function reset_group_defaults()
	{
		$group_id = $this->_EE->input->get('group_id');

		if($this->_super_admin == false)
		{
            show_error(lang('unauthorized_access'));
		}
		
		$group = $this->_model->get_member_group($group_id);
		if($group_id != '' AND is_numeric($group_id))
		{
			$this->_model->reset_member_layouts($group_id);
			
			$this->_EE->session->set_flashdata('dashee_msg', lang('flashGroupLayoutReset') . $group->group_title . '.');
		}
		else
		{
			$this->_EE->session->set_flashdata('dashee_msg', lang('flashGroupLayoutNotReset') . $group->group_title . '.');
		}
		
		$this->_EE->functions->redirect($this->_base_url.AMP.'method=settings');

	}
	
	/**
	 * Get/update users dashEE settings.
	 *
	 * @return 	array
	 */
	public function _get_member_settings($member_id)
	{
		$settings = $this->_model->get_member_settings($member_id);

		$this->_EE->cp->get_installed_modules();

		// Ensure all widgets in users settings are still installed and files available.
		$update_member = FALSE;
		foreach($settings['widgets'] as $col => $widget)
		{
			if(is_array($widget))
			{
				foreach($widget as $id => $params)
				{
					if(!isset($this->_EE->cp->installed_modules[$params['mod']]) || 
						!file_exists(PATH_THIRD.$params['mod'].'/widgets/'.$params['wgt']))
					{
						unset($settings['widgets'][$col][$id]);
						
						$update_member = TRUE;
					}
				}
			}
		}
		
		$this->_settings = $settings;
	
		if($update_member)
		{
			$this->_update_member();
		}
	}
	
	/**
	 * Attempt to update a members dashboard config in DB.
	 *
	 * @return 	array
	 */
	private function _update_member($reindex = TRUE)
	{
		if($reindex)
		{
			// reindex widgets array before saving it to the DB
			$widgets = array();
			for($x=1; $x <= $this->_settings['columns']; ++$x)
			{
				$widgets[$x] = array();
			}
			
			$i = 1;
			foreach($this->_settings['widgets'] as $col => $widget)
			{
				if(is_array($widget))
				{
					foreach($widget as $id => $params)
					{
						$widgets[$col]['wgt'.$i] = $params;
						++$i;
					}
				}
			}
			$this->_settings['widgets'] = $widgets;
		}

		$this->_model->update_member($this->_member_id, $this->_settings);	
	}

	/**
	 * Load selected widgets for display.
	 *
	 * @return 	array
	 */
	private function _widget_loader(array $widgets)
	{
		for($i=1; $i <= $this->_settings['columns']; ++$i)
		{
			$cols[$i] = '';
		}

		foreach($widgets as $col => $widget)
		{
			if(is_array($widget))
			{
				foreach($widget as $id => $params)
				{
					$obj = $this->_get_widget_object($params['mod'], $params['wgt']);
									
					$class 		= isset($obj->wclass) ? $obj->wclass : '';
					$dash_code 	= method_exists($obj, 'settings_form') ? 'dashee="dynamic"' : '';

					// check widget permissions
					if(method_exists($obj, 'permissions') && !$obj->permissions())
					{
						$content = '<p>'.lang('permission_denied').'</p>';
					}
					else
					{
						$this->_add_widget_package_path($params['mod']);
						$content = $obj->index(@json_decode($params['stng']));
					}
					
					$cols[$col] .= '
						<li id="'.$id.'" class="widget '.$class.'" '.$dash_code.'>
							<div class="heading">
								<h2>'.$obj->title.'</h2>
								<div class="buttons"></div>
							</div>
							<div class="widget-content">'.$content.'</div>
						</li>
					';
				}
			}

			//$cols[$col] .= '&nbsp;';
		}
		
		return $cols;
	}
	
	/**
	 * Require necessary widget class and return instance.
	 *
	 * @param	$module		string		Module that requested widget is part of.
	 * @param	$widget		string		Requested widget.
	 * @return 	object
	 */
	private function _get_widget_object($module, $widget)
	{
		include_once(PATH_THIRD.$module.'/widgets/'.$widget);
		$obj = $this->_format_filename($widget, TRUE);
		return new $obj();
	}
	
	/**
	 * Format widget names for reference.
	 *
	 * @param 	$name		string		File name.
	 * @param 	$cap		bool		Capitalize filename?
	 * @return 	string
	 */
	private function _format_filename($name, $cap = FALSE)
	{
		$str = str_replace('.', '_', substr($name, 0, -4));
		return $cap ? ucfirst($str) : $str;
	}
	
}
/* End of file mcp.dashee.php */
/* Location: /system/expressionengine/third_party/dashee/mcp.dashee.php */