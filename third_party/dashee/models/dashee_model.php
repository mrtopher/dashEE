<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * dashEE Model
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Chris Monnat
 * @link		http://chrismonnat.com
 */

class Dashee_model extends CI_Model 
{
    private $_EE;
    private $_site_id;
    private $_package_name;
    private $_package_version;
    private $_extension_version;

    private $_module_settings = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->_EE =& get_instance();

        $this->_site_id = $this->_EE->session->userdata('site_id');
        
        $this->_package_name    	= 'dashEE';
        $this->_package_version 	= '1.8';
        $this->_extension_version 	= '1.2';

        $this->_module_settings = array(
    		array(
                'site_id' => $this->_site_id,
    			'key' 	  => 'crumb_term',
    			'value'   => 'Dashboard'
    			),
    		array(
                'site_id' => $this->_site_id,
    			'key' 	  => 'redirect_admins',
    			'value'   => TRUE
    			)
    		);
    }
    
    /**
     * Returns the installed package version.
     *
     * @access  public
     * @return  string
     */
    public function get_installed_version()
    {
        $result = $this->_EE->db->select('module_version')
        	->get_where('modules', array('module_name' => $this->get_package_name()), 1);

        return $result->num_rows() === 1 ? $result->row()->module_version : '';
    }
    
    /**
     * Returns the package name.
     *
     * @access  public
     * @return  string
     */
    public function get_package_name()
    {
        return $this->_package_name;
    }
    
    /**
     * Returns the package version.
     *
     * @access  public
     * @return  string
     */
    public function get_package_version()
    {
        return $this->_package_version;
    }

    /**
     * Returns the module URL with session ID if required.
     *
     * @access  public
     * @return  string
     */
    public function get_module_url()
    {
    	$s = 0;
		switch($this->_EE->config->item('admin_session_type'))
		{
			case 's':
				$s = $this->_EE->session->userdata('session_id', 0);
				break;
			case 'cs':
				$s = $this->_EE->session->userdata('fingerprint', 0);
				break;
		}

		return SELF . str_replace('&amp;', '&', '?S=' . $s) . AMP . 'D=cp' . AMP . 'C=addons_modules' . AMP . 'M=show_module_cp' . AMP . 'module=dashee';
    }
    
    /**
     * Installs the module.
     *
     * @access  public
     * @return  bool
     */
    public function install_module()
    {
        $this->install_module_register();
        $this->install_module_members_table();
        $this->install_module_layouts_table();
        $this->install_module_layouts_groups_table();
        $this->install_module_settings_table();

        return TRUE;
    }

    /**
     * Registers the module in the database.
     *
     * @access  public
     * @return  void
     */
    public function install_module_register()
    {
        $this->_EE->db->insert('modules', array(
            'module_name'           => ucfirst($this->get_package_name()),
            'module_version'        => $this->get_package_version(),
            'has_cp_backend'        => 'y',
            'has_publish_fields'    => 'n',
        	));
    }

    /**
     * Creates the dashEE members entries table.
     * Stores each members module configuration data.
     *
     * @access  public
     * @return  void
     */
    public function install_module_members_table()
    {
		$this->_EE->load->dbforge();
				
		$fields = array(
			'id' => array(
				'type' 			 => 'INT',
				'constraint'  	 => 10,
				'unsigned'		 => TRUE,
				'auto_increment' => TRUE
				),
			'site_id' => array(
				'type' 			 => 'INT',
				'constraint'  	 => 10,
				'unsigned'		 => TRUE
				),
			'member_id' => array(
				'type' 			=> 'INT',
				'constraint' 	=> 10,
				'unsigned'		=> TRUE
				),
			'config' => array(
				'type'			=> 'TEXT',
				'null'			=> TRUE
				)
			);
			
		$this->_EE->dbforge->add_field($fields);
		$this->_EE->dbforge->add_key('id', TRUE);
		$this->_EE->dbforge->create_table('dashee_members', TRUE);
    }
    
    /**
     * Creates the dashEE layouts table.
     * Stores saved dashboard layouts for later use.
     *
     * @access  public
     * @return  void
     */
    public function install_module_layouts_table()
    {
		$this->_EE->load->dbforge();
				
		$fields = array(
			'id' => array(
				'type' 			 => 'INT',
				'constraint'  	 => 10,
				'unsigned'		 => TRUE,
				'auto_increment' => TRUE
				),
			'site_id' => array(
				'type' 			 => 'INT',
				'constraint'  	 => 10,
				'unsigned'		 => TRUE
				),
			'name' => array(
				'type' 			=> 'VARCHAR',
				'constraint' 	=> 200
				),
			'description' => array(
				'type' 			=> 'TEXT',
				'null'			=> TRUE
				),
			'config' => array(
				'type'			=> 'TEXT',
				'null'			=> TRUE
				),
			'is_default' => array(
				'type'			=> 'TINYINT',
				'constraint'	=> 1,
				'default'		=> 0
				)
			);
			
		$this->_EE->dbforge->add_field($fields);
		$this->_EE->dbforge->add_key('id', TRUE);
		$this->_EE->dbforge->create_table('dashee_layouts', TRUE);
		
		// add standard default layout to new layouts DB table
		$default_config = $this->_get_standard_default_template();
	
		$params = array(
			'site_id'		=> $this->_site_id,
			'name' 			=> 'Default EE layout',
			'description'	=> 'Default dashEE layout that mimics standard EE CP.',
			'config' 		=> json_encode($default_config),
			'is_default' 	=> TRUE
			);
			
		$this->_EE->db->insert('dashee_layouts', $params);

    }
    
    /**
     * Creates the dashEE member groups layouts table.
     * Stores relationships between saved dashboard layouts and membership groups.
     *
     * @access  public
     * @return  void
     */
    public function install_module_layouts_groups_table()
    {
    	$this->_EE->load->dbforge();
    
		$fields = array(
			'id' => array(
				'type' 			 => 'INT',
				'constraint'  	 => 10,
				'unsigned'		 => TRUE,
				'auto_increment' => TRUE
				),
			'member_group_id' => array(
				'type' 			=> 'INT',
				'constraint' 	=> 10,
				'unsigned'		=> TRUE
				),
			'site_id' => array(
				'type' 			 => 'INT',
				'constraint'  	 => 10,
				'unsigned'		 => TRUE
				),
			'layout_id' => array(
				'type' 			=> 'INT',
				'constraint' 	=> 10,
				'unsigned'		=> TRUE
				),
			'locked' => array(
				'type' 			=> 'INT',
				'constraint' 	=> 1,
				'unsigned'		=> TRUE,
				'null'			=> FALSE
				),
			);
			
		$this->_EE->dbforge->add_field($fields);
		$this->_EE->dbforge->add_key('id', TRUE);
		$this->_EE->dbforge->create_table('dashee_member_groups_layouts', TRUE);
    }

    /**
     * Creates the dashEE settings table.
     * Stores module settings data.
     *
     * @access  public
     * @return  void
     */
    public function install_module_settings_table()
    {
    	$this->_EE->load->dbforge();
    
		$fields = array(
			'id' => array(
				'type' 			 => 'INT',
				'constraint'  	 => 10,
				'unsigned'		 => TRUE,
				'auto_increment' => TRUE
				),
			'site_id' => array(
				'type' 			 => 'INT',
				'constraint'  	 => 10,
				'unsigned'		 => TRUE,
				),
			'key' => array(
				'type' 			=> 'VARCHAR',
				'constraint' 	=> 50,
				'null'			=> FALSE
				),
			'value' => array(
				'type' 			=> 'VARCHAR',
				'constraint'  	=> 255,
				'null'			=> FALSE
				)
			);
			
		$this->_EE->dbforge->add_field($fields);
		$this->_EE->dbforge->add_key('id', TRUE);
		$this->_EE->dbforge->create_table('dashee_settings', TRUE);

        $this->_EE->db->insert_batch('dashee_settings', $this->_module_settings);
    }
    
    /**
     * Activate module extension.
     *
     * @access  public
     * @return  void
     */
    public function activate_extension()
    {
		$hooks = array(
			'cp_css_end'		=> 'crumb_hide',
			'cp_js_end'			=> 'crumb_remap',
			'cp_member_login'	=> 'member_redirect',
			'sessions_end'		=> 'sessions_end',
			);

		foreach ($hooks as $hook => $method)
		{
			$data = array(
				'class'		=> 'Dashee_ext',
				'method'	=> $method,
				'hook'		=> $hook,
				'settings'	=> '',
				'version'	=> $this->_extension_version,
				'enabled'	=> 'y'
				);

			$this->_EE->db->insert('extensions', $data);			
		}
    }
    
    /**
     * Activate module extension.
     *
     * @access  public
     * @return  void
     */
    public function disable_extension()
    {
		$this->_EE->db->where('class', 'Dashee_ext');
		$this->_EE->db->delete('extensions');
    }
    
    /**
     * Uninstalls the module.
     *
     * @access  public
     * @return  bool
     */
    public function uninstall_module()
    {
        $module_name = ucfirst($this->get_package_name());

        // Retrieve the module information.
        $result = $this->_EE->db->select('module_id')
            ->get_where('modules', array('module_name' => $module_name), 1);

        if($result->num_rows() !== 1)
        {
            return FALSE;
        }

        $this->_EE->db->delete('module_member_groups', array('module_id' => $result->row()->module_id));
        $this->_EE->db->delete('modules', array('module_name' => $module_name));

        // Drop the module entries table.
        $this->_EE->load->dbforge();
        $this->_EE->dbforge->drop_table('dashee_members');
        $this->_EE->dbforge->drop_table('dashee_layouts');
        $this->_EE->dbforge->drop_table('dashee_member_groups_layouts');
        $this->_EE->dbforge->drop_table('dashee_settings');

        return TRUE;
    }
    
    /**
     * Updates the module.
     *
     * @access  public
     * @param   string      $installed_version      The installed version.
     * @param   bool        $force                  Forcibly update the module version number?
     * @return  bool
     */
    public function update_package($installed_version = '', $force = FALSE)
    {
        if(version_compare($installed_version, $this->get_package_version(), '>='))
        {
            return FALSE;
        }

        if(version_compare($installed_version, '1.4', '<'))
        {
            $this->_update_package_to_version_14();
        }

        if(version_compare($installed_version, '1.5', '<'))
        {
            $this->_update_package_to_version_15();
        }

        if(version_compare($installed_version, '1.6', '<'))
        {
            $this->_update_package_to_version_16();
        }

        if(version_compare($installed_version, '1.8', '<'))
        {
            $this->_update_package_to_version_18();
        }

        // Forcibly update the module version number?
        if($force === TRUE)
        {
            $this->_ee->db->update(
                'modules',
                array('module_version' => $this->get_package_version()),
                array('module_name' => $this->get_package_name())
            );
        }

        return TRUE;
    }
    
    /**
     * Update dashboard config format to account for storing settings.
     * Add new DB tables to account for saving layouts and assigning them to member groups.
     *
     * @access  private
     * @return  void
     */
    private function _update_package_to_version_14()
    {
    	// update stored configs with new 'columns' variable
    	$qry = $this->_EE->db->get('dashee_members');
    	
    	foreach($qry->result() as $row)
    	{
    		$settings = json_decode($row->config, TRUE);
    		$settings['columns'] = 3;
    		
    		$this->db->update('dashee_members', array('config' => json_encode($settings)), array('id' => $row->id)); 
    	}
    	
    	// add DB tables for storing layouts and assigning them to member groups
    	$this->install_module_layouts_table();
		$this->install_module_layouts_groups_table();		
    }

    /**
     * Add column 'locked' to dashee_member_groups_layouts
     *
     * @access  private
     * @return  void
     */
    private function _update_package_to_version_15()
    {
		$this->_EE->load->dbforge();

		$fields = array(
			'locked' => array(
				'type' 			=> 'int', 
				'constraint' 	=> '1',
				'unsigned'		=> TRUE,
				'null' 			=> FALSE
				)
			);
		
		$this->_EE->dbforge->add_column('dashee_member_groups_layouts', $fields);
    }
        
    /**
     * Add site_id columns to appropriate tables and populate as needed for MSM support.
     *
     * @access  private
     * @return  void
     */
    private function _update_package_to_version_16()
    {
		$this->_EE->load->dbforge();

		$fields = array(
			'site_id' => array(
				'type'			=> 'INT',
				'constraint'  	=> 10,
				'unsigned'		=> TRUE
				)
			);
		
		// add site_id column to both members and layouts table
		// using query() instead of DB forge to take advantage of mysql AFTER operator
		$this->_EE->db->query('ALTER TABLE exp_dashee_members ADD `site_id` INT(10) UNSIGNED NOT NULL AFTER `id`');
		$this->_EE->db->query('ALTER TABLE exp_dashee_layouts ADD `site_id` INT(10) UNSIGNED NOT NULL AFTER `id`');
		$this->_EE->db->query('ALTER TABLE exp_dashee_member_groups_layouts ADD `site_id` INT(10) UNSIGNED NOT NULL AFTER `member_group_id`');
		
		// set new site_id column for all existing members/layouts
		$this->_EE->db->update('dashee_members', array('site_id' => $this->_site_id));
		$this->_EE->db->update('dashee_layouts', array('site_id' => $this->_site_id));
		$this->_EE->db->update('dashee_member_groups_layouts', array('site_id' => $this->_site_id));
		
		// reindex existing member layouts with random IDs
		$this->_EE->load->helper('string');
		
		$members = $this->_EE->db->get('dashee_members');
		foreach($members->result() as $member)
		{
			$config = array();
			$dash = json_decode($member->config, TRUE);
			foreach($dash['widgets'] as $col => $widgets)
			{
				foreach($widgets as $id => $widget)
				{
					$config[$col][random_string('numeric', 8)] = $widget;
				}
			}
			
			$dash['widgets'] = $config;
			$this->_EE->db->update('dashee_members', array('config' => json_encode($dash)), array('id' => $member->id));
		}
    }

	/**
	 * Add module settings DB table and populate with default settings and remove extension settings.
	 *
	 * @access  private
	 * @return  void
	 */
   private function _update_package_to_version_18()
    {
    	// add DB table for storing module settings
    	$this->install_module_settings_table();

  		// remove obsolete extension settings
  		$this->_EE->db->update('extensions', array('settings' => ''), array('class' => 'Dashee_ext'));

      	// update stored configs with new 'state_buttons' variable
    	$qry = $this->_EE->db->get('dashee_members');
    	
    	foreach($qry->result() as $row)
    	{
    		$settings = json_decode($row->config, TRUE);
    		$settings['state_buttons'] = TRUE;
    		
    		$this->db->update('dashee_members', array('config' => json_encode($settings)), array('id' => $row->id)); 
    	}
    }
    
   	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return 	mixed	void on update / false if none
	 */
	public function update_extension($current = '')
	{
		if(version_compare($current, $this->_extension_version, '>='))
		{
			return FALSE;
		}
		
		if(version_compare($current, '1.1', '<'))
		{
			$this->_update_extension_to_version_11();
		}
		
		return TRUE;
	}	
		
	/**
	 * Update Extension to Version 1.1
	 *
	 * Add session_end hook to extensions table.
	 *
	 * @return 	mixed	void on update / false if none
	 */
	private function _update_extension_to_version_11()
	{
		$data = array(
			'class'		=> __CLASS__,
			'method'	=> 'sessions_end',
			'hook'		=> 'sessions_end',
			'settings'	=> serialize(array('redirect_admins' => array('yes'))),
			'version'	=> $this->_extension_version,
			'enabled'	=> 'y'
			);

		$this->_EE->db->insert('extensions', $data);
	}


    /**
     * Returns the package theme folder URL, appending a forward slash if required.
     *
     * @access    public
     * @return    string
     */
    public function get_package_theme_url()
    {
        return URL_THIRD_THEMES.strtolower($this->get_package_name()).'/';
    }
    
    /**
     * Get all installed EE modules.
     *
     * @access    public
     * @return    array
     */
    public function get_installed_modules()
    {
		$result = $this->_EE->db->select('modules.module_name')
			->from('modules')
			->order_by('module_name')
			->get();
		
		$installed = array();
		foreach($result->result_array() as $row)
		{
			$installed[] = strtolower($row['module_name']);
		}
		
		return $installed;
    }
    
	/**
	 * Get members dashboard configuration from DB.
	 *
     * @access  public
     * @param	int			$member_id		ID of currently logged in user.
	 * @return 	obj
	 */
	public function get_member_settings($member_id)
	{
		$site_id = 
		$result = $this->_EE->db->select('*')
			->from('dashee_members')
			->where('site_id', $this->_site_id)
			->where('member_id', $member_id)
			->get();
		
		if($result->num_rows() < 1)
		{
			// This is a new user with no preferences, return default configuration for membership group.
			$params = array(
				'site_id' 			=> $this->_site_id,
				'member_group_id' 	=> $this->_EE->session->userdata('group_id')
				);
			$qry = $this->_EE->db->get_where('dashee_member_groups_layouts', $params);
			
			if($qry->num_rows() > 0)
			{
				$layout_id = $qry->row()->layout_id;
				$layout = $this->get_layout($layout_id);
				$config = $layout->config;
				$locked_group = ($qry->row()->locked == 1) ? TRUE : FALSE;
			}
			else
			{			
				$layout = $this->get_default_layout();
				$config = $layout->config;
				$locked_group = FALSE;
			}
		
			$params = array(
				'site_id'	=> $this->_site_id,
				'member_id' => $member_id,
				'config' 	=> $config
				);
			
			$this->_EE->db->insert('dashee_members', $params);

			$config = json_decode($params['config'], TRUE);
			$config['locked'] = $locked_group;

			return $config;
		}
		else
		{
			// config fetched from DB
			$config = json_decode($result->row()->config, TRUE);

			// check if the member group has a locked layout
			$params = array(
				'locked' 			=> 1,
				'member_group_id' 	=> $this->_EE->session->userdata('group_id')
				);
			$qry = $this->_EE->db->get_where('dashee_member_groups_layouts', $params);

			$config['locked'] = ($qry->num_rows() == 1) ? TRUE : FALSE;

			return $config;
		}
	}
	
	/**
	 * Update members dashboard configuration in DB.
	 *
     * @access  public
     * @param	int			$member_id		ID of currently logged in user.
     * @param	array		$config			Member dashboard config.
	 * @return 	obj
	 */
	public function update_member($member_id, $config)
	{
		return $this->_EE->db->update('exp_dashee_members', array('config' => json_encode($config)), array('site_id' => $this->_site_id, 'member_id' => $member_id));
	}  
	
	/**
	 * Get all saved layouts for display.
	 *
     * @access  public
	 * @return 	obj
	 */
	public function get_all_layouts()
	{
		return $this->_EE->db->where('site_id', $this->_site_id)
			->order_by('name')
			->get('dashee_layouts')
			->result();
	}
	
	/**
	 * Get selected saved layout from DB by ID.
	 *
     * @access  public
     * @param 	int			$layout_id		ID of selected layout.
	 * @return 	obj
	 */
	public function get_layout($layout_id)
	{
		return $this->_EE->db->get_where('dashee_layouts', array('id' => $layout_id, 'site_id' => $this->_site_id))->row();
	}
	
	/**
	 * Store standard default layout for use throughout model.
	 *
     * @access  private
	 * @return 	array
	 */
	private function _get_standard_default_template()
	{
		$this->_EE->load->helper('string');
		
		return array(
			'widgets' => array(
				1 => array(
					'wgt' . random_string('numeric', 8) => array(
						'mod' 	=> 'dashee', 
						'wgt' 	=> 'wgt.welcome.php',
						'state' => 1
						),
					'wgt' . random_string('numeric', 8) => array(
						'mod' 	=> 'dashee',
						'wgt' 	=> 'wgt.create_links.php',
						'state' => 1
						)
					),
				2 => array(
					'wgt' . random_string('numeric', 8) => array(
						'mod' 	=> 'dashee',
						'wgt' 	=> 'wgt.modify_links.php',
						'state' => 1
						)
					),
				3 => array(
					'wgt' . random_string('numeric', 8) => array(
						'mod' 	=> 'dashee',
						'wgt' 	=> 'wgt.view_links.php',
						'state' => 1
						)
					)
				),
			'columns'        => 3,
            'state_buttons'  => TRUE
			);
	}
	
	/**
	 * Get default layout from DB.
	 *
     * @access  public
	 * @return 	obj
	 */
	public function get_default_layout()
	{
		$qry = $this->_EE->db->get_where('dashee_layouts', array('site_id' => $this->_site_id, 'is_default' => TRUE));
		
		if($qry->num_rows() > 0)
		{
			return $qry->row();
		}
		else
		{
			$params = array(
				'site_id'		=> $this->_site_id,
				'name' 			=> 'Default EE layout',
				'description'	=> 'Default dashEE layout that mimics standard EE CP.',
				'config' 		=> json_encode($this->_get_standard_default_template()),
				'is_default' 	=> TRUE
				);
				
			$this->_EE->db->insert('dashee_layouts', $params);
			
			return $this->_EE->db->get_where('dashee_layouts', array('site_id' => $this->_site_id, 'is_default' => TRUE))->row();
		}
	}
	
	/**
	 * Get member group default layouts for display in setting form.
	 *
     * @access  public
	 * @return 	array
	 */
	public function get_all_group_layouts()
	{
		$qry = $this->_EE->db->get('dashee_member_groups_layouts');
		
		$groups = array();
		if($qry->num_rows() > 0)
		{
			foreach($qry->result() as $row)
			{
				$groups[$row->member_group_id] = array(
					'layout_id' => $row->layout_id,
					'site_id'	=> $this->_site_id,
					'locked' 	=> (boolean) $row->locked,
					);
			}
		}
		
		return $groups;
	}
	
	/**
	 * Set new default layout for module.
	 *
     * @access  public
     * @param 	int			$layout_id		ID of selected layout.
	 * @return 	obj
	 */
	public function set_default_layout($layout_id)
	{
		$this->_EE->db->update('dashee_layouts', array('is_default' => FALSE), array('site_id' => $this->_site_id));
		$this->_EE->db->update('dashee_layouts', array('is_default' => TRUE), array('id' => $layout_id, 'site_id' => $this->_site_id));
	}
	
	/**
	 * Add new layout to DB.
	 *
     * @access  public
     * @param	string		$name			Name of layout to save.
     * @param	string		$description	Optional layout description.
     * @param	array		$config			Dashboard config.
	 * @return 	obj
	 */
	public function add_layout($name, $description, $config)
	{
		$params = array(
			'site_id'		=> $this->_site_id,
			'name' 			=> $name,
			'description' 	=> $description,
			'config' 		=> json_encode($config)
			);
		$this->_EE->db->insert('dashee_layouts', $params);
	}  
	
	/**
	 * Update selected saved layout from DB by ID.
	 *
     * @access  public
     * @param 	int			$layout_id		ID of selected layout.
     * @param 	array		$params			Updated layout parameters.
	 * @return 	obj
	 */
	public function update_layout($layout_id, $params)
	{
		$this->_EE->db->update('dashee_layouts', $params, array('id' => $layout_id, 'site_id' => $this->_site_id));
	}
	
	/**
	 * Update default member group layouts.
	 *
     * @access  public
     * @param 	array		$group_layouts		Assoc. array of group_id with assigned layout_id.
	 * @return 	void
	 */
	public function update_group_layouts($group_layouts, $group_locked)
	{
		$this->_EE->db->truncate('dashee_member_groups_layouts');
		
		foreach($group_layouts as $group_id => $layout_id)
		{
			$locked = (isset($group_locked[$group_id])&& $group_locked[$group_id]=='locked') ? 1 : 0;
			
			$params = array(
				'member_group_id' 	=> $group_id,
				'site_id'			=> $this->_site_id,
				'layout_id' 		=> $layout_id,
				'locked' 			=> $locked
				);
			$this->_EE->db->insert('dashee_member_groups_layouts', $params);
		}
	}
	
	/**
	 * Reset member layouts according to the assigned member group layout.
	 *
     * @access  public
     * @param 	array		$group_layouts		Assoc. array of group_id with assigned layout_id.
	 * @return 	void
	 */
	public function reset_member_layouts($group_id = FALSE)
	{
		$this->_EE->db->select('dashee_members.*, members.group_id')
			->from('dashee_members')
			->join('members', 'dashee_members.member_id = members.member_id')
			->where('dashee_members.site_id', $this->_site_id);

		if($group_id) $this->_EE->db->where('members.group_id', $group_id);

		$member_qry = $this->_EE->db->get()->result();
	
		if(count($member_qry) > 0)
		{
			$group_layouts = $this->get_all_group_layouts();
			
			$layout_qry = $this->get_all_layouts();
			$layouts = array();
			foreach($layout_qry as $layout)
			{
				$layouts[$layout->id] = $layout->config;
			}
			
			foreach($member_qry as $member)
			{
				$this->_EE->db->update('dashee_members', array('config' => $layouts[$group_layouts[$member->group_id]['layout_id']]), array('id' => $member->id));
			}
		}
	}
	
	/**
	 * Delete selected saved layout from DB by ID and updates any associated member groups.
	 *
     * @access  public
     * @param 	int			$layout_id		ID of selected layout.
	 * @return 	obj
	 */
	public function delete_layout($layout_id)
	{
		$this->_EE->db->delete('dashee_layouts', array('id' => $layout_id));
		$default = $this->_EE->db->get_where('dashee_layouts', array('is_default' => TRUE))->row();
		$this->_EE->db->update('dashee_member_groups_layouts', array('layout_id' => $default->id), array('layout_id' => $layout_id));
	}
	
	/**
	 * Get all member groups in CMS for layout assignment.
	 *
     * @access  public
	 * @return 	obj
	 */
	public function get_member_groups()
	{
		return $this->_EE->db->select('group_id AS id, group_title AS title, group_description AS description')
			->from('member_groups')
			->where('site_id', $this->_site_id)
			->where('can_access_cp', 'y')
			->order_by('group_title')
			->get()
			->result();
	}

	/**
	 * Get selected member group by ID.
	 *
     * @access  public
	 * @param	int
	 * @return 	obj
	 */
	public function get_member_group($group_id)
	{
		return $this->_EE->db->select('*')
			->from('member_groups')
			->where('group_id', $group_id)
			->where('site_id', $this->_site_id)
			->get()
			->row();
	}

	/**
	 * Get all module settings from DB.
	 *
     * @access  public
	 * @return 	array
	 */
	public function get_module_settings()
	{
		$qry = $this->_EE->db->get_where('dashee_settings', array('site_id' => $this->_site_id));

        // populate module settings if they don't already exist for the site in question (for MSM compatibility)
        if($qry->num_rows() <= 0)
        {
            $this->_EE->db->insert_batch('dashee_settings', $this->_module_settings);
            $qry = $this->_EE->db->get_where('dashee_settings', array('site_id' => $this->_site_id));
        }

		$settings = array();
		foreach($qry->result() as $row)
		{
			$settings[$row->key] = $row->value;
		}

		return $settings;
	}

	/**
	 * Attempt to update module settings in DB.
	 *
     * @access  public
	 * @return 	void
	 */
	public function update_module_settings($params = array())
	{
		foreach($params as $key => $value)
		{
			$this->_EE->db->update('dashee_settings', array('value' => $value), array('site_id' => $this->_site_id, 'key' => $key));
		}
	}
}
/* End of file dashee_model.php */
/* Location: /system/expressionengine/third_party/dashee/models/dashee_model.php */