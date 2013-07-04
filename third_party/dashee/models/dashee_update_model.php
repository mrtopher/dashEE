<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * dashEE Update Model
 *
 * Model used only for modules install and update operations.
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Chris Monnat
 * @link		http://chrismonnat.com
 */

class Dashee_update_model extends CI_Model 
{
    private $_EE;
    private $_site_id;
    private $_package_name;
    private $_package_version;
    private $_extension_version;

    private $_module_settings = array();

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
     * Update any references to Feed Reader widget to new name.
     *
     * @access  private
     * @return  void
     */
    private function _update_package_to_version_20()
    {

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
}
/* End of file dashee_update_model.php */
/* Location: /system/expressionengine/third_party/dashee/models/dashee_update_model.php */