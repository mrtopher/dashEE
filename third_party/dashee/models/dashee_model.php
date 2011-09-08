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
 * dashEE Model
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Chris Monnat
 * @link		http://chrismonnat.com
 */

class Dashee_model extends CI_Model {

    private $_EE;
    private $_package_name;
    private $_package_version;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->_EE =& get_instance();
        
        $this->_package_name    = 'dashEE';
        $this->_package_version = '1.0';
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
     * Creates the OmniLog entries table.
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
			'member_id' => array(
				'type' 			=> 'INT',
				'constraint' 	=> 10,
				'unsigned'		=> TRUE,
				),
			'config' => array(
				'type'			=> 'TEXT',
				'null'			=> TRUE,
				)
			);
			
		$this->_EE->dbforge->add_field($fields);
		$this->_EE->dbforge->add_key('id', TRUE);
		$this->_EE->dbforge->create_table('dashee_members', TRUE);
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
        $result = $this->_EE->db
            ->select('module_id')
            ->get_where('modules', array('module_name' => $module_name), 1);

        if($result->num_rows() !== 1)
        {
            return FALSE;
        }

        $this->_EE->db->delete('module_member_groups', array('module_id' => $result->row()->module_id));
        $this->_EE->db->delete('modules', array('module_name' => $module_name));

        // Drop the log entries table.
        $this->_EE->load->dbforge();
        $this->_EE->dbforge->drop_table('dashee_members');

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

        /*if(version_compare($installed_version, '1.1.0', '<'))
        {
            $this->_update_package_to_version_110();
        }*/

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
     * Returns the package theme folder URL, appending a forward slash if required.
     *
     * @access    public
     * @return    string
     */
    public function get_package_theme_url()
    {
        $theme_url = $this->_EE->config->item('theme_folder_url');
        $theme_url .= substr($theme_url, -1) == '/' ? 'third_party/' : '/third_party/';

        return $theme_url.strtolower($this->get_package_name()).'/';
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
		$result = $this->_EE->db->select('*')
			->from('dashee_members')
			->where('member_id', $member_id)
			->get();
		
		if($result->num_rows() < 1)
		{
			// This is a new user with no preferences, return default configuration.
			$config = array(
				'widgets' => array(
					1 => array(
						'wgt1' => array(
							'mod' => 'dashee', 
							'wgt' => 'wgt.welcome.php'
							),
						'wgt2' => array(
							'mod' => 'dashee',
							'wgt' => 'wgt.create_links.php'
							)
						),
					2 => array(
						'wgt3' => array(
							'mod' => 'dashee',
							'wgt' => 'wgt.modify_links.php'
							)
						),
					3 => array(
						'wgt4' => array(
							'mod' => 'dashee',
							'wgt' => 'wgt.view_links.php'
							)
						)
					)
				);
		
			$params = array(
				'member_id' => $member_id,
				'config' 	=> json_encode($config)
				);
				
			$this->_EE->db->insert('dashee_members', $params);
			
			return json_decode($params['config'], TRUE);
		}
		else
		{
			return json_decode($result->row()->config, TRUE);
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
		return $this->_EE->db->update('exp_dashee_members', array('config' => json_encode($config)), array('member_id' => $member_id));
	}    
}
/* End of file dashee_model.php */
/* Location: /system/expressionengine/third_party/dashee/models/dashee_model.php */