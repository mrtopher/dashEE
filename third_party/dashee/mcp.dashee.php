<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * dashEE Module Control Panel File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Chris Monnat
 * @link		http://chrismonnat.com
 */

class Dashee_mcp
{
	public $return_data;

	private $EE;
	private $_model;
	private $_theme_url;
	private $_css_url;
	private $_js_url;
	private $_member_id;
	private $_config_id;
	private $_super_admin = FALSE;
	private $_settings;
	private $_widgets;

	/**
	 * Constructor
	 *
	 * @access 		public
 	 * @return 		void
	 */
	public function __construct()
	{
		$this->EE =& get_instance();

        $this->EE->load->model('dashee_model');
        $this->EE->load->helper('module');

        $this->_model = $this->EE->dashee_model;

        $this->_theme_url   = $this->_model->get_package_theme_url();
        // $this->_js_url   	= $this->_theme_url . 'js/dashee.js';
        $this->_js_url   	= $this->_theme_url .'js/dashee.min.js';

        $this->_member_id = $this->EE->session->userdata('member_id');
        if($this->EE->session->userdata('group_id') == 1)
        {
        	$this->_super_admin = TRUE;
        }
        $this->_config_id = isset($_GET['config_id']) ? $this->EE->input->get('config_id') : $this->_model->get_member_default_config_id($this->_member_id);

        // get current members dash configuration for use throughout module
        $this->_get_member_settings();
        $this->_get_widgets();
	}

	/**
	 * Index Function
	 * Display selected dashboard.
	 *
	 * @access 		public
	 * @return 		void
	 */
	public function index()
	{
        $this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="' . $this->_theme_url .'css/cp.css" />
        							<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">');

        // is the member_group layout locked?
        if($this->_settings['locked'] == FALSE)
        {
        	$this->EE->cp->add_to_foot('<script type="text/javascript" src="' . $this->_js_url . '"></script>
        								<script type="text/javascript" src="' . $this->_theme_url .'js/dashee_plugin.js"></script>');
        }

		// override default breadcrumb display to make module look like default CP homepage
		$settings = $this->_model->get_module_settings();
		$this->EE->javascript->output("
			$('a[href=\"#collapse\"]').parent('.button').css('float', 'left');
			$('a[href=\"#expand\"]').parent('.button').css('float', 'left');
			$('a[href=\"#member-settings\"]').attr('title', 'Display Settings').css('padding', '3px 4px 2px 4px');
			$('#breadCrumb ol li').slice(2).remove();
			$('#breadCrumb ol li:last-child').attr('class', 'last').html('" . $settings['crumb_term'] . "');
			");

		$msg = $this->EE->session->flashdata('dashee_msg');
		if($msg != '')
		{
			$this->EE->javascript->output("
				$.ee_notice('".$msg."', {type: 'success'});
				");
		}

		$this->EE->javascript->compile();

		// load widgets
		$widgets = $this->_widget_loader($this->_settings['widgets']);

		// get module settings for appropriate page title
		$module_settings = $this->_model->get_module_settings();

		/**
		 * Because add_package_path is called for each module with widgets in widget_loader, EE will incorrectly default the views
		 * drectory to the last module whose widget was called thus causing the index view for that module to be displayed
		 * instead of the dashboard... this line adds dashee path onto the end after widget_loader to ensure this doesn't happen
		 */
		$this->EE->load->add_package_path(PATH_THIRD . 'dashee/');

		$this->EE->view->cp_page_title = $module_settings['crumb_term'];

		$page_data = array(
			'settings' 			=> $this->_settings,
			'dashboards'		=> $this->_model->get_dashboards($this->_member_id),
			'content' 			=> $widgets,
			'theme_url' 		=> $this->_theme_url,
			'state_buttons'		=> $this->_settings['state_buttons'],
			'super_admin'		=> $this->_super_admin,
			'locked'			=> $this->_settings['locked'],
			'config_id'			=> $this->_config_id
			);

		return $this->EE->load->view('index', $page_data, TRUE);
	}

	/**
	 * Settings Function
	 * Display module settings form.
	 *
	 * @access 		public
	 * @return 		void
	 */
	public function settings()
	{
		$this->EE->load->library('table');

        $this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="' . $this->_theme_url . 'css/settings.css" />');
        $this->EE->cp->add_to_foot('<script type="text/javascript" src="' . $this->_js_url . '"></script>');

        $this->EE->cp->set_right_nav(array(
        	'btn_back_to_dashboard'	=> cp_url('cp/addons_modules/show_module_cp', array('module' => 'dashee'))
        	));

		$this->EE->cp->set_breadcrumb(cp_url('cp/addons_modules/show_module_cp', array('module' => 'dashee')), lang('btn_settings'));

		// override default breadcrumb display
		$this->EE->javascript->output("
			$('#breadCrumb ol li').slice(2,4).remove();
			");
		$this->EE->javascript->compile();

		$msg = $this->EE->session->flashdata('dashee_msg');
		if($msg != '')
		{
			$this->EE->javascript->output("
				$.ee_notice('".$msg."', {type: 'success'});
				");
		}

		// get default layout ID
		// run this function up here to ensure the default layout is in the table BEFORE generating layout options
		$default_id = $this->_model->get_default_layout()->id;

		// get layout options for display and use as dropdown options
		$layouts 		= array();
		$layout_options = array();
		if($this->EE->session->userdata('group_id') == 1)
		{
			$layouts = $this->_model->get_all_layouts();
			$layout_options = array();
			foreach($layouts as $layout)
			{
				$layout_options[$layout->id] = $layout->name;
			}
		}

		$this->EE->view->cp_page_title = lang('dashee_settings');

		$page_data = array(
			'settings' 		=> $this->_model->get_module_settings(),
			'is_admin'		=> $this->_super_admin,
			'layouts' 		=> $layouts,
			'opts_layouts' 	=> $layout_options,
			'member_groups'	=> $this->_model->get_member_groups(),
			'default_id' 	=> $default_id,
			'group_layouts' => $this->_model->get_all_group_layouts()
			);

		return $this->EE->load->view('settings', $page_data, TRUE);
	}

	/**
	 * Update Settings Function
	 * Display module settings form.
	 *
	 * @access 		public
	 * @return 		void
	 */
	public function update_settings()
	{
		$settings = array();

		if(isset($_POST['crumb_term']))
		{
			$settings['crumb_term'] = $_POST['crumb_term'];
		}

		if(isset($_POST['redirect_admins']) AND is_numeric($_POST['redirect_admins']))
		{
			$settings['redirect_admins'] = $_POST['redirect_admins'];
		}

		$this->_model->update_module_settings($settings);

		$this->EE->session->set_flashdata('dashee_msg', lang('flash_settings_updated'));

		$this->EE->functions->redirect(cp_url('cp/addons_modules/show_module_cp', array('module' => 'dashee', 'method' => 'settings')));
	}

	/**
	 * Update Member Settings Function
	 * Attempt to save users module settings to DB.
	 *
	 * @access 		public
	 * @return 		void
	 */
	public function update_member_settings()
	{
		$post_columns = $this->EE->input->post('columns');

		if(($post_columns != '' AND is_numeric($post_columns) AND $post_columns <= 3) AND $post_columns != $this->_settings['columns'])
		{
			$current = $this->_settings['columns'];
			$new = $this->EE->input->post('columns');
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
		}

		$this->_settings['state_buttons'] = isset($_POST['state_buttons']) ? $this->EE->input->post('state_buttons') : 1;

		$this->_update_member(FALSE);

		$this->EE->session->set_flashdata('dashee_msg', lang('flash_settings_updated'));

		$this->EE->functions->redirect(cp_url('cp/addons_modules/show_module_cp', array('module' => 'dashee', 'config_id' => $this->EE->input->post('config_id'))));
	}

	/**
	 * AJAX METHOD
	 * Get listing of all available widgets from installed modules.
	 *
	 * @access 		public
	 * @return 		void
	 */
	public function ajax_get_widget_listing()
	{
		$this->EE->load->library('table');

		$map = directory_map(PATH_THIRD, 2);

		// Fetch installed modules.
		$installed_mods = $this->_model->get_installed_modules();

		// Determine which installed modules have widgets associated with them.
		$mods_with_widgets = array();
		foreach($map as $third_party => $widgets)
		{
			if(is_array($widgets))
			{
				if(in_array($third_party, $installed_mods) AND in_array('widgets', $widgets))
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
			$path = PATH_THIRD . $mod . '/widgets/';
			$map = directory_map(PATH_THIRD . $mod . '/widgets', 1);

			if(is_array($map))
			{
				$col = 1;
				asort($map);

				foreach($map as $widget)
				{
					$this->EE->lang->loadfile($mod);

					// check widget permissions before adding to table and skip if user doesn't have permission
					$obj = $this->_get_widget_object($mod, $widget);
					if(method_exists($obj, 'permissions') && !$obj->permissions())
					{
						continue;
					}

					// provide for developers to include widget name and description in widget file and not need to put in dashEE language file
					$widget_name 			= '';
					$widget_description 	= '';

					if(property_exists($obj, 'widget_name'))
					{
						$widget_name 		= $obj->widget_name;
						$widget_description = $obj->widget_description ? $obj->widget_description : '--';
					}
					else
					{
						$widget_name 		= lang($this->_format_filename($widget).'_name');
						$widget_description = lang($this->_format_filename($widget).'_description');
					}

					$table_data[] = array(
						$widget_name,
						$widget_description,
						lang(strtolower($mod).'_module_name'),
						anchor(cp_url('cp/addons_modules/show_module_cp', array('module' => 'dashee')), 'Add', 'class="addWidget" data-module="' . $mod . '" data-widget="' . $widget . '"')
						);
				}
			}
		}

		echo $this->EE->load->view('widgets_listing', array('rows' => $table_data), TRUE);
		exit();
	}

	/**
	 * AJAX METHOD
	 * Add selected widget to users dashboard and update config.
	 *
	 * @access 		public
	 * @return 		void
	 */
	public function ajax_add_widget()
	{
		$mod = $this->EE->input->get('mod');
		$wgt = $this->EE->input->get('wgt');

		if(isset($mod) AND isset($wgt))
		{
			$this->EE->load->helper('string');

			$obj = $this->_get_widget_object($mod, $wgt);
			$wid = 'wgt'.random_string('numeric', 8);

			// run widget add method if exists
			if(method_exists($obj, 'widget_add'))
			{
				$obj->widget_add();
			}

			// run widget install method if exists
			if(method_exists($obj, 'widget_install'))
			{
				// determine if any other users have this widget on their dashboard
				$total = $this->_model->get_widget_count(@$this->_widgets[$wgt]['wgt']);

				// only run install for widget if it is not already on any dashboards currently
				if($total == 0)
				{
					$obj->widget_install();
				}
			}

			// determine which column has the least number of widgets in it so you can add the
			// new one to the one with the least
			$totals = array();
			for($i=1; $i <= $this->_settings['columns']; ++$i)
			{
				$totals[$i] = @count($this->_settings['widgets'][$i]);
			}

			$col = array_keys($totals, min($totals));

			$new_widget = array(
				'mod' 	=> $mod,
				'wgt' 	=> $wgt,
				'state' => 1
				);

			// add widget settings to config if present
			if(isset($obj->settings))
			{
				$new_widget['stng'] = json_encode($obj->settings);
			}

			$this->_settings['widgets'][$col[0]][$wid] = $new_widget;

			// update members dashboard config in DB
			$this->_update_member(FALSE);
		}

		echo json_encode(array('id' => $wid, 'col' => $col, 'html' => $this->_render_widget($wid, $mod, $wgt, 1, @$new_widget['stng'])));
		exit();
	}

	/**
	 * AJAX METHOD
	 * Remove selected widget from users dashboard and update settings.
	 *
	 * @access 		public
	 * @return 		void
	 */
	public function ajax_remove_widget()
	{
		$wgt = $this->EE->input->get('wgt');

		if(array_key_exists($wgt, $this->_widgets))
		{
			$widget = $this->_widgets[$wgt];

			if($widget['wgt'] != 'dummy')
			{
				$obj = $this->_get_widget_object($widget['mod'], $widget['wgt']);

				// run widget remove method if exists
				if(method_exists($obj, 'widget_remove'))
				{
					$obj->widget_remove(@$widget['stng']);
				}

				// run widget uninstaller method if exists
				if(method_exists($obj, 'widget_uninstall'))
				{
					// determine if any other users have this widget on their dashboard
					$total = $this->_model->get_widget_count($widget['wgt']);

					// only run uninstall for widget if this is the only instance of it
					if($total <= 1)
					{
						$obj->widget_uninstall();
					}
				}
			}

			unset($this->_settings['widgets'][$widget['col']][$wgt]);
			$this->_update_member(FALSE);
		}
	}

	/**
	 * AJAX METHOD
	 * Update widget order and column placement in DB.
	 *
	 * @access 		public
	 * @return 		void
	 */
	public function ajax_update_widget_order()
	{
		$order = $this->EE->input->get('order');

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
	 * @access 		public
	 * @return 		void
	 */
	public function ajax_widget_settings()
	{
		$wgt = $this->EE->input->get('wgt');

		if(array_key_exists($wgt, $this->_widgets))
		{
			$widget = $this->_widgets[$wgt];

			$obj = $this->_get_widget_object($widget['mod'],$widget['wgt']);
			echo $obj->settings_form(json_decode($widget['stng']));
			exit();
		}
		else
		{
			echo '<p>Widget could not be found.</p>';
		}
	}

	/**
	 * AJAX METHOD
	 * Attempt to update a widgets state in the DB.
	 *
	 * @access 		public
	 * @return 		void
	 */
	public function ajax_update_widget_state()
	{
		$state = 1;
		$state = $this->EE->input->get('state');

		if(isset($_GET['wgt']))
		{
			$widget = $this->_widgets[$this->EE->input->get('wgt')];
			$this->_settings['widgets'][$widget['col']][$widget['id']]['state'] = $state;
		}
		else
		{
			foreach($this->_settings['widgets'] as $col => $widgets)
			{
				foreach($widgets as $id => $params)
				{
					$this->_settings['widgets'][$col][$id]['state'] = $state;
				}
			}
		}

		$this->_update_member(FALSE);
		exit();
	}

	/**
	 * AJAX METHOD
	 * Attempt to update a widgets settings.
	 *
	 * @access 		public
	 * @return 		void
	 */
	public function ajax_update_widget_settings()
	{
		$data 		= $_POST;
		$settings 	= array();
		$widget 	= $this->_widgets[$data['wgt']];

		foreach($data as $field => $value)
		{
			$settings[$field] = $value;
		}

		$settings_json = json_encode($settings);
		$this->_settings['widgets'][$widget['col']][$widget['id']]['stng'] = $settings_json;
		$this->_update_member(FALSE);

		$obj = $this->_get_widget_object($widget['mod'], $widget['wgt']);
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
	 * @access 		public
	 * @return 		void
	 */
	public function ajax_save_layout()
	{
		$name 			= $this->EE->input->post('layout_name');
		$description 	= $this->EE->input->post('layout_desc');

		if($this->_super_admin AND $name != '')
		{
			$this->_model->add_layout($name, $description, $this->_settings);
		}
	}

	/**
	 * AJAX METHOD
	 * Pass through (proxy) method for hanlding widget POST requests.
	 *
	 * @access 		public
	 * @return 		string
	 */
	public function ajax_widget_post_proxy()
	{
		// $this->_config_id = $this->EE->input->post('config_id');
		$wgtid 	= $this->EE->input->post('wgtid');
		$method = $this->EE->input->post('mthd');

		if(isset($wgtid) AND isset($method))
		{
			$widget = $this->_widgets[$wgtid];
			$obj = $this->_get_widget_object($widget['mod'], $widget['wgt']);
			$message = $obj->$method($_POST);

			$content = $obj->index(@json_decode($this->_widgets[$wgtid]['stng']));
			$result = array(
				'type'		=> 'success',
				'title'		=> $obj->title,
				'content' 	=> $content,
				'message'	=> $message
				);
		}
		else
		{
			$result = array(
				'type'		=> 'failure',
				'message'	=> 'Something went wrong.'
				);
		}

		echo json_encode($result);
		exit();
	}

	/**
	 * AJAX METHOD
	 * Pass through (proxy) method for hanlding widget GET requests.
	 *
	 * @access 		public
	 * @return 		string
	 */
	public function ajax_widget_get_proxy()
	{
		// $this->_config_id = $this->EE->input->post('config_id');
		$wgtid 	= $this->EE->input->get('wgtid');
		$method = $this->EE->input->get('mthd');

		if(isset($wgtid) AND isset($method))
		{
			$widget = $this->_widgets[$wgtid];
			$obj = $this->_get_widget_object($widget['mod'], $widget['wgt']);
			$message = $obj->$method($_GET);

			$content = $obj->index(@json_decode($this->_widgets[$wgtid]['stng']));
			$result = array(
				'type'		=> 'success',
				'title'		=> $obj->title,
				'content' 	=> $content,
				'message'	=> $message
				);
		}
		else
		{
			$result = array(
				'type'		=> 'failure',
				'message'	=> 'Something went wrong.'
				);
		}

		echo json_encode($result);
		exit();
	}

	/**
	 * AJAX METHOD
	 * Return JSON for selected widget for processing by javascript.
	 * Used to return widgets back to load state after NOT submitting settings form.
	 *
	 * @access 		public
	 * @return		string
	 */
	public function ajax_get_widget()
	{
		$widget = $this->_widgets[$this->EE->input->get('wgt')];
		$obj = $this->_get_widget_object($widget['mod'], $widget['wgt']);
		$content = $obj->index(json_decode($widget['stng']));
		$result = array(
			'title'		=> $obj->title,
			'content' 	=> $content
			);
		echo json_encode($result);
		exit();
	}

	/**
	 * Change default layout in DB.
	 *
	 * @access 		public
	 * @return 		void
	 */
	public function set_default_layout()
	{
		$layout_id = $this->EE->input->get('layout_id');

		if($this->_super_admin AND $layout_id != '' AND is_numeric($layout_id))
		{
			$this->_model->set_default_layout($layout_id);

			$this->EE->session->set_flashdata('dashee_msg', lang('flash_layout_updated'));
		}
		else
		{
			$this->EE->session->set_flashdata('dashee_msg', lang('flash_layout_not_updated'));
		}

		$this->EE->functions->redirect(cp_url('cp/addons_modules/show_module_cp', array('module' => 'dashee', 'method' => 'settings')));
	}

	/**
	 * Load selected saved layout for current user.
	 *
	 * @access 		public
	 * @return 		void
	 */
	public function load_layout()
	{
		$layout_id = $this->EE->input->get('layout_id');

		if($this->_super_admin AND $layout_id != '' AND is_numeric($layout_id))
		{
			$layout = $this->_model->get_layout($layout_id);
			$this->_settings = json_decode($layout->config, TRUE);

			$this->_update_member(FALSE);

			$this->EE->session->set_flashdata('dashee_msg', $layout->name . lang('flash_layout_loaded'));
		}
		else
		{
			$this->EE->session->set_flashdata('dashee_msg', lang('flash_layout_not_loaded'));
		}

		$this->EE->functions->redirect(cp_url('cp/addons_modules/show_module_cp', array('module' => 'dashee')));
	}

	/**
	 * Delete selected saved layout from DB.
	 *
	 * @access 		public
	 * @return 		void
	 */
	public function delete_layout()
	{
		$layout_id = $this->EE->input->get('layout_id');

		if($this->_super_admin AND $layout_id != '' AND is_numeric($layout_id))
		{
			$layout = $this->_model->get_layout($layout_id);

			if(!$layout->is_default)
			{
				$this->_model->delete_layout($layout->id);

				$this->EE->session->set_flashdata('dashee_msg', $layout->name . lang('flash_layout_deleted'));
			}
		}
		else
		{
			$this->EE->session->set_flashdata('dashee_msg', lang('flash_layout_not_deleted'));
		}

		$this->EE->functions->redirect(cp_url('cp/addons_modules/show_module_cp', array('module' => 'dashee', 'method' => 'settings')));
	}

	/**
	 * Update Member Group Defaults Function
	 * Attempt to save member group default settings to DB.
	 *
	 * @access 		public
	 * @return 		void
	 */
	public function update_group_defaults()
	{
		if($this->_super_admin == FALSE)
		{
			show_error(lang('unauthorized_access'));
		}

		$group_layouts = $this->EE->input->post('group_layouts');
		$group_locked = $this->EE->input->post('group_locked');

		if($group_layouts != '' AND is_array($group_layouts))
		{
			$this->_model->update_group_layouts($group_layouts, $group_locked);

			$this->EE->session->set_flashdata('dashee_msg', lang('flash_group_default_updated'));
		}
		else
		{
			$this->EE->session->set_flashdata('dashee_msg', lang('flash_group_default_not_updated'));
		}

		$this->EE->functions->redirect(cp_url('cp/addons_modules/show_module_cp', array('module' => 'dashee', 'method' => 'settings')));
	}

	/**
	 * Reset Member Group Defaults Function
	 * Reset layout for a member group.
	 *
	 * @access 		public
	 * @return 		void
	 */
	public function reset_group_defaults()
	{
		$group_id = $this->EE->input->get('group_id');

		if($this->_super_admin == false)
		{
            show_error(lang('unauthorized_access'));
		}

		$group = $this->_model->get_member_group($group_id);
		if($group_id != '' AND is_numeric($group_id))
		{
			$this->_model->reset_member_layouts($group_id);

			$this->EE->session->set_flashdata('dashee_msg', lang('flash_group_layout_reset') . $group->group_title . '.');
		}
		else
		{
			$this->EE->session->set_flashdata('dashee_msg', lang('flash_group_layout_not_reset') . $group->group_title . '.');
		}

		$this->EE->functions->redirect(cp_url('cp/addons_modules/show_module_cp', array('module' => 'dashee', 'method' => 'settings')));

	}

	/**
	 * Reset Dashboard Function
	 * Reset selected dashboard to default layout config.
	 *
	 * @access	public
	 * @return	void
	 */
	public function reset_dashboard()
	{
		$default = $this->_model->get_default_layout();
		$this->_settings = json_decode($default->config, TRUE);

		$this->_update_member(FALSE);

		$this->EE->session->set_flashdata('dashee_msg', lang('flash_dashboard_reset'));
		$this->EE->functions->redirect(cp_url('cp/addons_modules/show_module_cp', array('module' => 'dashee', 'config_id' => $this->_config_id)));
	}

	/**
	 * Rename Dashboard Function
	 * Update name of the selected dashboard in DB.
	 *
	 * @access	public
	 * @return	void
	 */
	public function rename_dashboard()
	{
		$name 		= $this->EE->input->post('dashboard_name');
		$config_id 	= $this->EE->input->post('config_id');

		if($name != '' AND ($config_id != '' AND is_numeric($config_id)))
		{
		 	$dashboard_params = array(
		 		'name' => $name
		 		);

		 	$this->_model->update_dashboard($config_id, $dashboard_params);

			$this->EE->session->set_flashdata('dashee_msg', lang('flash_dashboard_updated'));
		}

		$this->EE->functions->redirect(cp_url('cp/addons_modules/show_module_cp', array('module' => 'dashee', 'config_id' => $config_id)));
	}

	/**
	 * Create New Dashboard Function
	 * Create new dashboard for current user.
	 *
	 * @access	public
	 * @return	void
	 */
	public function new_dashboard()
	{
		$name 	= $this->EE->input->post('dashboard_name');
		$config = $this->EE->input->post('dashboard_config');

		if($name == '')
		{
			$name = 'New Dashboard';
		}

		if($config == 'default')
		{
			$result = $this->_model->get_default_layout();
			$config = $result->config;
		}
		else
		{
			$config = json_encode(
				array(
					'widgets' 		 => array(),
					'columns'        => 3,
		            'state_buttons'  => TRUE,
		            'locked'		 => FALSE
					)
				);
		}

		$dashboard_params = array(
			'dashee_id' 	=> $this->_model->get_dashee_id($this->_member_id),
			'name'			=> $name,
			'config' 		=> $config,
			'is_default' 	=> FALSE
			);

		$config_id = $this->_model->add_dashboard($dashboard_params);

		$this->EE->session->set_flashdata('dashee_msg', lang('flash_dashboard_created'));
		$this->EE->functions->redirect(cp_url('cp/addons_modules/show_module_cp', array('module' => 'dashee', 'config_id' => $config_id)));
	}

	/**
	 * Delete Dashboard Function
	 * Delete current dashboard from DB.
	 *
	 * @access  public
	 * @return	void
	 */
	public function delete_dashboard()
	{
		$result = $this->_model->get_dashboard($this->_config_id);

		if($result->num_rows() === 1)
		{
			$dashboard = $result->row();

			if(!$dashboard->is_default)
			{
				$this->_model->delete_dashboard($dashboard->id);
				$this->EE->session->set_flashdata('dashee_msg', lang('flash_dashboard_deleted'));
			}
			else
			{
				// can't delete a users default dashboard
				$this->EE->session->set_flashdata('dashee_msg', lang('flash_dashboard_deleted_error'));
			}
		}
		else
		{
			// can't find selected dashboard
			$this->EE->session->set_flashdata('dashee_msg', lang('flash_dashboard_not_found'));
		}

		// redirect user to their default dashboard
		$config_id = $this->_model->get_member_default_config_id($this->_member_id);
		$this->EE->functions->redirect(cp_url('cp/addons_modules/show_module_cp', array('module' => 'dashee', 'config_id' => $config_id)));
	}

	/**
	 * Add Widget's Package Path Function
	 * Makes it possible for widgets to use $EE->load->view(), etc.
	 *
	 * Should be called right before calling a widget's index() funciton.
	 *
	 * @access 		private
	 * @param 		string 		$module 	Module widget belongs to.
	 * @param 		string 		$widget 	Widget name.
	 * @return 		void
	 */
	private function _add_widget_package_path($module, $widget)
	{
		if(substr($widget, 0, 4) === 'wgt.')
		{
			$path = PATH_THIRD . $module;
		}
		else
		{
			// when dealing with widget folder you need to add package path for module as well as widget folder to account
			// for widget using some module assets like models or helpers
			$this->EE->load->add_package_path(PATH_THIRD . $module);
			$path = PATH_THIRD . $module . '/widgets/' . $widget;
		}

		$this->EE->load->add_package_path($path);

		// manually add the view path if this is less than EE 2.1.5
		if(version_compare(APP_VER, '2.1.5', '<'))
		{
			$this->EE->load->_ci_view_path = $path . 'views/';
		}
	}

	/**
	 * Get Member Module Settings Function
	 * Get/update users dashEE settings.
	 *
	 * @access 		private
	 * @return 		array
	 */
	private function _get_member_settings()
	{
		$settings = $this->_model->get_member_settings($this->_member_id, $this->_config_id);

		// make sure to set config ID after getting member settings because it will have been set to NULL for new users from the construct
		if(is_null($this->_config_id))
		{
			$this->_config_id = $this->_model->get_member_default_config_id($this->_member_id);
		}

		$this->EE->cp->get_installed_modules();

		// Ensure all widgets in users settings are still installed and files available.
		$update_member = FALSE;
		foreach($settings['widgets'] as $col => $widget)
		{
			if(is_array($widget))
			{
				foreach($widget as $id => $params)
				{
					if($params['mod'] != 'dashee' AND $params['wgt'] != 'dummy')
					{
						if(!isset($this->EE->cp->installed_modules[$params['mod']]) ||
							!file_exists(PATH_THIRD.$params['mod'].'/widgets/'.$params['wgt']))
						{
							unset($settings['widgets'][$col][$id]);

							$update_member = TRUE;
						}
						else
						{
							$this->_widgets[$id] = $params;
							$this->_widgets[$id]['col'] = $col;
							$this->_widgets[$id]['id'] = $id;
						}
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
	 * Get Widgets Function
	 * Get just widget data and put in array for easy access/reference.
	 *
	 * @access 		private
	 * @return 		array
	 */
	private function _get_widgets()
	{
		foreach($this->_settings['widgets'] as $col => $widgets)
		{
			foreach($widgets as $wid => $widget)
			{
				$this->_widgets[$wid] = $widget;
				$this->_widgets[$wid]['col'] = $col;
				$this->_widgets[$wid]['id'] = $wid;
			}
		}
	}

	/**
	 * Update Member Config Function
	 * Attempt to update a members dashboard config in DB.
	 *
	 * @access 		private
	 * @return 		array
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

		$this->_model->update_member($this->_config_id, $this->_settings);

		// ensure widgets array is updated with most recent data
		$this->_get_widgets();
	}

	/**
	 * Load Widget Function
	 * Load selected widgets for display.
	 *
	 * @access 		private
	 * @return 		array
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
					$cols[$col] .= $this->_render_widget($id, $params['mod'], $params['wgt'], @$params['state'], @$params['stng']);
				}
			}
		}

		return $cols;
	}

	/**
	 * Render Widget Function
	 * Render selected widget and return generated HTML.
	 *
	 * @access 		private
	 * @return		string
	 */
	private function _render_widget($id, $module, $widget, $state = 1, $settings = '')
	{
		// determine if this is a dummy widget or one with a corresponding object
		// dummy widget
		if($module == 'dashee' AND $widget == 'dummy')
		{
			$data = $this->_widgets[$id]['data'];

			$title 		= $data['title'];
			$class 		= $data['wclass'];
			$class 		.= (isset($state) AND !$state) ? ' collapsed' : '';
			$dash_code	= '';
			$content 	= $data['content'];
			$js 		= '';
		}
		// the real thing
		else
		{
			$obj = $this->_get_widget_object($module, $widget);

			$class 		= isset($obj->wclass) ? $obj->wclass : '';
			$dash_code 	= method_exists($obj, 'settings_form') ? 'dashee="dynamic"' : '';

			// check widget permissions
			if(method_exists($obj, 'permissions') && !$obj->permissions())
			{
				$content = '<p>'.lang('permission_denied').'</p>';
			}
			else
			{
				$content = $obj->index(@json_decode($settings));
				$title 	 = $obj->title;
			}

			// check widget state (expanded vs. collapsed)
			if(isset($state) AND !$state)
			{
				$class .= ' collapsed';
			}

			// check if widget has associated JS
			$js = '';
			if(isset($obj->js))
			{
				$js = $obj->js;
			}
		}

		return '
			<li id="' . $id . '" class="widget ' . $class . '" ' . $dash_code . '>
				<div class="heading">
					<h2>' . $title . '</h2>
					<div class="buttons"></div>
				</div>
				<div class="widget-content">' . $content . '</div>
				' . $js . '
			</li>
		';
	}

	/**
	 * Get Widget Function
	 * Require necessary widget class and return instance.
	 *
	 * @access 		private
	 * @param		$module		string		Module that requested widget is part of.
	 * @param		$widget		string		Requested widget.
	 * @return 		object
	 */
	private function _get_widget_object($module, $widget)
	{
		if(substr($widget, 0, 4) === 'wgt.')
		{
			include_once(PATH_THIRD . $module . '/widgets/' . $widget);
			$obj = $this->_format_filename($widget, TRUE);
		}
		else
		{
			include_once(PATH_THIRD . $module . '/widgets/' . $widget . '/wgt.' . $widget . '.php');
			$obj = $this->_format_filename('wgt.' . $widget . '.php', TRUE);
		}

		$this->_add_widget_package_path($module, $widget);

		return new $obj();
	}

	/**
	 * Format Filename Function
	 * Format widget names for reference.
	 *
	 * @access 		private
	 * @param 		$name		string		File name.
	 * @param 		$cap		bool		Capitalize filename?
	 * @return 		string
	 */
	private function _format_filename($name, $cap = FALSE)
	{
		if(substr($name, -4) === '.php')
		{
			$str = str_replace('.', '_', substr($name, 0, -4));
		}
		else
		{
			$str = 'wgt_' . $name;
		}

		return $cap ? ucfirst($str) : $str;
	}

}
/* End of file mcp.dashee.php */
/* Location: /system/expressionengine/third_party/dashee/mcp.dashee.php */
