<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * dashEE Update Model
 *
 * Model used ONLY for modules install and update operations.
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Chris Monnat
 * @link		http://chrismonnat.com
 */

class Dashee_update_model extends CI_Model
{
    private $EE;
    private $_site_id;
    private $_package_name;
    private $_package_version;
    private $_extension_version;

    private $_module_settings = array();

    /**
     * Constructor
     *
     * @access      public
     * @return      void
     */
    public function __construct()
    {
        parent::__construct();

        $this->EE =& get_instance();

        $this->_site_id = $this->EE->session->userdata('site_id');

        $this->_package_name    	= 'dashEE';
        $this->_package_version 	= '2.0';
        $this->_extension_version 	= '1.4';

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
     * Get Installed Version Function
     * Returns the installed package version.
     *
     * @access      public
     * @return      str
     */
    public function get_installed_version()
    {
        $result = $this->EE->db->select('module_version')
        	->get_where('modules', array('module_name' => $this->get_package_name()), 1);

        return $result->num_rows() === 1 ? $result->row()->module_version : '';
    }

    /**
     * Get Package Name Function
     * Returns the package name.
     *
     * @access      public
     * @return      str
     */
    public function get_package_name()
    {
        return $this->_package_name;
    }

    /**
     * Get Package Version Function
     * Returns the package version.
     *
     * @access      public
     * @return      str
     */
    public function get_package_version()
    {
        return $this->_package_version;
    }

    /**
     * Install Module Function
     * Installs the module.
     *
     * @access      public
     * @return      bool
     */
    public function install_module()
    {
        $this->install_module_register();
        $this->install_module_members_table();
        $this->install_module_member_configs_table();
        $this->install_module_layouts_table();
        $this->install_module_layouts_groups_table();
        $this->install_module_settings_table();

        return TRUE;
    }

    /**
     * Register Module Function
     * Registers the module in the database.
     *
     * @access      public
     * @return      void
     */
    public function install_module_register()
    {
        $this->EE->db->insert('modules', array(
            'module_name'           => ucfirst($this->get_package_name()),
            'module_version'        => $this->get_package_version(),
            'has_cp_backend'        => 'y',
            'has_publish_fields'    => 'n',
        	));
    }

    /**
     * Create dashee_members DB Table Function
     *
     * Creates the dashEE members entries table.
     * Stores each members module configuration data.
     *
     * @access      public
     * @return      void
     */
    public function install_module_members_table()
    {
		$this->EE->load->dbforge();

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
				)
			);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table('dashee_members', TRUE);
    }

    /**
     * Create dashee_member_configs DB Table Function
     *
     * Creates the dashEE member config entries table.
     * Stores each members dashboard configuration data allowing
     * members to have multiple dashboards.
     *
     * @access      public
     * @return      void
     */
    public function install_module_member_configs_table()
    {
        $this->EE->load->dbforge();

        $fields = array(
            'id' => array(
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE
                ),
            'dashee_id' => array(
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => TRUE
                ),
            'name' => array(
                'type'           => 'VARCHAR',
                'constraint'     => 200
                ),
            'config' => array(
                'type'           => 'TEXT',
                'null'           => TRUE
                ),
            'is_default' => array(
                'type'          => 'TINYINT',
                'constraint'    => 1,
                'default'       => 0
                )
            );

        $this->EE->dbforge->add_field($fields);
        $this->EE->dbforge->add_key('id', TRUE);
        $this->EE->dbforge->create_table('dashee_member_configs', TRUE);
    }

    /**
     * Create dashee_layouts DB Table Function
     *
     * Creates the dashEE layouts table.
     * Stores saved dashboard layouts for later use.
     *
     * @access      public
     * @return      void
     */
    public function install_module_layouts_table()
    {
		$this->EE->load->dbforge();

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

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table('dashee_layouts', TRUE);

		// add standard default layout to new layouts DB table
        $this->EE->load->model('dashee_model');
		$default_config = $this->EE->dashee_model->get_standard_default_template();

		$params = array(
			'site_id'		=> $this->_site_id,
			'name' 			=> 'Default EE layout',
			'description'	=> 'Default dashEE layout that mimics standard EE CP.',
			'config' 		=> json_encode($default_config),
			'is_default' 	=> TRUE
			);

		$this->EE->db->insert('dashee_layouts', $params);

    }

    /**
     * Create dashee_member_groups_layouts DB Table Function
     *
     * Creates the dashEE member groups layouts table.
     * Stores relationships between saved dashboard layouts and membership groups.
     *
     * @access      public
     * @return      void
     */
    public function install_module_layouts_groups_table()
    {
    	$this->EE->load->dbforge();

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

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table('dashee_member_groups_layouts', TRUE);
    }

    /**
     * Create dashee_settings DB Table Function
     *
     * Creates the dashEE settings table.
     * Stores module settings data.
     *
     * @access      public
     * @return      void
     */
    public function install_module_settings_table()
    {
    	$this->EE->load->dbforge();

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

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table('dashee_settings', TRUE);

        $this->EE->db->insert_batch('dashee_settings', $this->_module_settings);
    }

    /**
     * Activate Extension Function
     *
     * Activate module extension in DB.
     *
     * @access      public
     * @return      void
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

			$this->EE->db->insert('extensions', $data);
		}
    }

    /**
     * Disable Extension Function
     *
     * Activate module extension.
     *
     * @access      public
     * @return      void
     */
    public function disable_extension()
    {
		$this->EE->db->where('class', 'Dashee_ext');
		$this->EE->db->delete('extensions');
    }

    /**
     * Uninstall Module Function
     *
     * Uninstalls the module.
     *
     * @access      public
     * @return      bool
     */
    public function uninstall_module()
    {
        $module_name = ucfirst($this->get_package_name());

        // Retrieve the module information.
        $result = $this->EE->db->select('module_id')
            ->get_where('modules', array('module_name' => $module_name), 1);

        if($result->num_rows() !== 1)
        {
            return FALSE;
        }

        $this->EE->db->delete('module_member_groups', array('module_id' => $result->row()->module_id));
        $this->EE->db->delete('modules', array('module_name' => $module_name));

        // Drop the module entries table.
        $this->EE->load->dbforge();
        $this->EE->dbforge->drop_table('dashee_members');
        $this->EE->dbforge->drop_table('dashee_member_configs');
        $this->EE->dbforge->drop_table('dashee_layouts');
        $this->EE->dbforge->drop_table('dashee_member_groups_layouts');
        $this->EE->dbforge->drop_table('dashee_settings');

        return TRUE;
    }

    /**
     * Update Package Funciton
     *
     * Updates the module to current version.
     *
     * @access      public
     * @param       str         $installed_version      The installed version.
     * @param       bool        $force                  Forcibly update the module version number?
     * @return      bool
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

        if(version_compare($installed_version, '2.0', '<'))
        {
            $this->_update_package_to_version_20();
        }

        // Forcibly update the module version number?
        if($force === TRUE)
        {
            $this->EE->db->update(
                'modules',
                array('module_version' => $this->get_package_version()),
                array('module_name' => $this->get_package_name())
            );
        }

        return TRUE;
    }

    /**
     * Version 1.4 Update Function
     *
     * Update dashboard config format to account for storing settings.
     * Add new DB tables to account for saving layouts and assigning them to member groups.
     *
     * @access      private
     * @return      void
     */
    private function _update_package_to_version_14()
    {
    	// update stored configs with new 'columns' variable
    	$qry = $this->EE->db->get('dashee_members');

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
     * Version 1.5 Update Function
     *
     * Add column 'locked' to dashee_member_groups_layouts
     *
     * @access      private
     * @return      void
     */
    private function _update_package_to_version_15()
    {
		$this->EE->load->dbforge();

		$fields = array(
			'locked' => array(
				'type' 			=> 'int',
				'constraint' 	=> '1',
				'unsigned'		=> TRUE,
				'null' 			=> FALSE
				)
			);

		$this->EE->dbforge->add_column('dashee_member_groups_layouts', $fields);
    }

    /**
     * Version 1.6 Update Function
     *
     * Add site_id columns to appropriate tables and populate as needed for MSM support.
     *
     * @access      private
     * @return      void
     */
    private function _update_package_to_version_16()
    {
		$this->EE->load->dbforge();

		$fields = array(
			'site_id' => array(
				'type'			=> 'INT',
				'constraint'  	=> 10,
				'unsigned'		=> TRUE
				)
			);

		// add site_id column to both members and layouts table
		// using query() instead of DB forge to take advantage of mysql AFTER operator
		$this->EE->db->query('ALTER TABLE exp_dashee_members ADD `site_id` INT(10) UNSIGNED NOT NULL AFTER `id`');
		$this->EE->db->query('ALTER TABLE exp_dashee_layouts ADD `site_id` INT(10) UNSIGNED NOT NULL AFTER `id`');
		$this->EE->db->query('ALTER TABLE exp_dashee_member_groups_layouts ADD `site_id` INT(10) UNSIGNED NOT NULL AFTER `member_group_id`');

		// set new site_id column for all existing members/layouts
		$this->EE->db->update('dashee_members', array('site_id' => $this->_site_id));
		$this->EE->db->update('dashee_layouts', array('site_id' => $this->_site_id));
		$this->EE->db->update('dashee_member_groups_layouts', array('site_id' => $this->_site_id));

		// reindex existing member layouts with random IDs
		$this->EE->load->helper('string');

		$members = $this->EE->db->get('dashee_members');
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
			$this->EE->db->update('dashee_members', array('config' => json_encode($dash)), array('id' => $member->id));
		}
    }

	/**
     * Version 1.8 Update Function
     *
	 * Add module settings DB table and populate with default settings and remove extension settings.
	 *
	 * @access     private
	 * @return     void
	 */
    private function _update_package_to_version_18()
    {
    	// add DB table for storing module settings
    	$this->install_module_settings_table();

  		// remove obsolete extension settings
  		$this->EE->db->update('extensions', array('settings' => ''), array('class' => 'Dashee_ext'));

      	// update stored configs with new 'state_buttons' variable
    	$qry = $this->EE->db->get('dashee_members');

    	foreach($qry->result() as $row)
    	{
    		$settings = json_decode($row->config, TRUE);
    		$settings['state_buttons'] = TRUE;

    		$this->db->update('dashee_members', array('config' => json_encode($settings)), array('id' => $row->id));
    	}
    }

    /**
     * Version 2.0 Update Function
     *
     * Add new DB table for multiple dashboard functionality and update any
     * references to Feed Reader widget to new name.
     *
     * @access      private
     * @return      void
     */
    private function _update_package_to_version_20()
    {
        // create new dashee_member_configs table
        $this->install_module_member_configs_table();

        // update all occurances of feed reader widget to new widget name (because it lives in a subfolder now) and move
        // configs to new dashee_member_configs table
        $members = $this->EE->db->get('dashee_members');
        foreach($members->result() as $member)
        {
            $config = array();
            $dash = json_decode($member->config, TRUE);
            foreach($dash['widgets'] as $col => $widgets)
            {
                foreach($widgets as $id => $widget)
                {
                    if($widget['wgt'] == 'wgt.welcome.php')
                    {
                        $dash['widgets'][$col][$id]['wgt']     = 'dummy';
                        $dash['widgets'][$col][$id]['data']    = array(
                            'title'     => 'Welcome to dashEE',
                            'wclass'    => 'padded welcome',
                            'content'   => '<p>dashEE is the ultimate in ExpressionEngine control panel customization. The module comes with several default widgets for making your life easier (located in the \'widgets\' directory). Don\'t see the functionality you\'re looking for? You can develop your own widgets and even integrate dashEE with your custom modules. Check out the video below learn more or visit the project on Github to contribute.</p><iframe src="//fast.wistia.net/embed/iframe/4186nqfmh2?videoFoam=true" allowtransparency="true" frameborder="0" scrolling="no" class="wistia_embed" name="wistia_embed" allowfullscreen mozallowfullscreen webkitallowfullscreen oallowfullscreen msallowfullscreen width="640" height="360"></iframe><script src="//fast.wistia.net/assets/external/iframe-api-v1.js"></script><br /><p><a target="_blank" href="https://github.com/mrtopher/dashEE">GitHub Repo</a></p>'
                            );
                    }

                    if($widget['wgt'] == 'wgt.feedreader.php')
                    {
                        $dash['widgets'][$col][$id]['wgt'] = 'feedreader';
                    }
                }
            }

            $params = array(
                'dashee_id'     => $member->id,
                'name'          => 'Default',
                'config'        => json_encode($dash),
                'is_default'    => TRUE
                );

            $this->EE->db->insert('dashee_member_configs', $params);
        }

        // drop old config column from dashee_members table
        $this->EE->load->dbforge();
        $this->EE->dbforge->drop_column('dashee_members', 'config');

        // add dummy widget welcoming current user only (no other members) to dashEE 2.0
        $widget = array(
            'title'     => 'dashEE 2.0 Is Here!',
            'wclass'    => 'padded',
            'content'   => '<iframe src="//fast.wistia.net/embed/iframe/4186nqfmh2?videoFoam=true" allowtransparency="true" frameborder="0" scrolling="no" class="wistia_embed" name="wistia_embed" allowfullscreen mozallowfullscreen webkitallowfullscreen oallowfullscreen msallowfullscreen width="640" height="360"></iframe><script src="//fast.wistia.net/assets/external/iframe-api-v1.js"></script>'
            );

        $member_id = $this->EE->session->userdata('member_id');
        $member = $this->EE->db->get_where('dashee_member_configs', array('dashee_id' => $member_id, 'is_default' => TRUE))->row();
        $config = $this->_add_dummy_widget($widget, $member->config);
        $this->EE->db->update('dashee_member_configs', array('config' => $config), array('dashee_id' => $member_id));
    }

   	/**
	 * Update Extension Function
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
     *
     * @access      public
     * @param       str         $current        void on update / false if none
	 * @return      bool
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
	 * Version 1.1 Extension Update Function
	 *
	 * Add session_end hook to extensions table.
	 *
     * @access     private
	 * @return 	   void
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

		$this->EE->db->insert('extensions', $data);
	}

    /**
     * Add Dummy Widget Function
     *
     * Add provided dummy widget to provided dash config.
     *
     * Was added to provide for creation of widgets that don't require widget files
     * in the widgets directory (like update notes and the Welcome widget).
     *
     * @access      private
     * @param       array         $widget         Array representation of widget.
     * @param       array         $config         Array for current dashboard configuration.
     * @return      obj
     */
    private function _add_dummy_widget($widget, $config)
    {
        $this->EE->load->helper('string');

        $config = json_decode($config, TRUE);

        $wid = 'wgt'.random_string('numeric', 8);

        $new_widget = array(
            'mod'   => 'dashee',
            'wgt'   => 'dummy',
            'state' => 1,
            'data'  => $widget
            );

        // determine which column has the least number of widgets in it so you can add the
        // new one to the one with the least
        $totals = array();
        for($i=1; $i <= $config['columns']; ++$i)
        {
            $totals[$i] = @count($config['widgets'][$i]);
        }

        $col = array_keys($totals, min($totals));
        $config['widgets'][$col[0]][$wid] = $new_widget;

        return json_encode($config);
    }
}
/* End of file dashee_update_model.php */
/* Location: /system/expressionengine/third_party/dashee/models/dashee_update_model.php */
