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
    private $EE;
    private $_site_id;

    private $_module_settings = array();

    public function __construct()
    {
        parent::__construct();

        $this->EE =& get_instance();

        $this->_site_id = $this->EE->session->userdata('site_id');
        
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
     * Returns the package theme folder URL, appending a forward slash if required.
     *
     * @access    public
     * @return    string
     */
    public function get_package_theme_url()
    {
        return URL_THIRD_THEMES.'dashee/';
    }
    
    /**
     * Get all installed EE modules.
     *
     * @access    public
     * @return    array
     */
    public function get_installed_modules()
    {
		$result = $this->EE->db->select('modules.module_name')
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
		$result = $this->EE->db->select('*')
			->from('dashee_members')
			->where('site_id', $this->_site_id)
			->where('member_id', $member_id)
			->get();
		
		if($result->num_rows() < 1)
		{
			// This is a new user with no preferences, return default configuration for membership group.
			$params = array(
				'site_id' 			=> $this->_site_id,
				'member_group_id' 	=> $this->EE->session->userdata('group_id')
				);
			$qry = $this->EE->db->get_where('dashee_member_groups_layouts', $params);
			
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
			
			$this->EE->db->insert('dashee_members', $params);

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
				'member_group_id' 	=> $this->EE->session->userdata('group_id')
				);
			$qry = $this->EE->db->get_where('dashee_member_groups_layouts', $params);

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
		return $this->EE->db->update('exp_dashee_members', array('config' => json_encode($config)), array('site_id' => $this->_site_id, 'member_id' => $member_id));
	}  
	
	/**
	 * Get all saved layouts for display.
	 *
     * @access  public
	 * @return 	obj
	 */
	public function get_all_layouts()
	{
		return $this->EE->db->where('site_id', $this->_site_id)
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
		return $this->EE->db->get_where('dashee_layouts', array('id' => $layout_id, 'site_id' => $this->_site_id))->row();
	}
	
	/**
	 * Store standard default layout for use throughout model.
	 *
     * @access  public
	 * @return 	array
	 */
	public function get_standard_default_template()
	{
		$this->EE->load->helper('string');
		
		return array(
			'widgets' => array(
				1 => array(
					'wgt' . random_string('numeric', 8) => array(
						'mod' 	=> 'dashee', 
						'wgt' 	=> 'dummy',
						'state' => 1,
						'data'	=> array(
							'title' 	=> 'Welcome to dashEE',
							'wclass' 	=> 'padded welcome',
							'content' 	=> "<p>dashEE is the ultimate in ExpressionEngine control panel customization. The module comes with several default widgets for making your life easier (located in the 'widgets' directory). Don't see the functionality you're looking for? You can develop your own widgets and even integrate dashEE with your custom modules. Learn more by following the links below:<\/p><p><a href=\"http:\/\/chrismonnat.com\/code\/dashee\" target=\"_blank\">Documentation<\/a> | <a href=\"https:\/\/github.com\/mrtopher\/dashEE\">GitHub Repo<\/a><\/p>"
							)
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
		$qry = $this->EE->db->get_where('dashee_layouts', array('site_id' => $this->_site_id, 'is_default' => TRUE));
		
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
				'config' 		=> json_encode($this->get_standard_default_template()),
				'is_default' 	=> TRUE
				);
				
			$this->EE->db->insert('dashee_layouts', $params);
			
			return $this->EE->db->get_where('dashee_layouts', array('site_id' => $this->_site_id, 'is_default' => TRUE))->row();
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
		$qry = $this->EE->db->get('dashee_member_groups_layouts');
		
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
		$this->EE->db->update('dashee_layouts', array('is_default' => FALSE), array('site_id' => $this->_site_id));
		$this->EE->db->update('dashee_layouts', array('is_default' => TRUE), array('id' => $layout_id, 'site_id' => $this->_site_id));
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
		$this->EE->db->insert('dashee_layouts', $params);
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
		$this->EE->db->update('dashee_layouts', $params, array('id' => $layout_id, 'site_id' => $this->_site_id));
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
		$this->EE->db->truncate('dashee_member_groups_layouts');
		
		foreach($group_layouts as $group_id => $layout_id)
		{
			$locked = (isset($group_locked[$group_id])&& $group_locked[$group_id]=='locked') ? 1 : 0;
			
			$params = array(
				'member_group_id' 	=> $group_id,
				'site_id'			=> $this->_site_id,
				'layout_id' 		=> $layout_id,
				'locked' 			=> $locked
				);
			$this->EE->db->insert('dashee_member_groups_layouts', $params);
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
		$this->EE->db->select('dashee_members.*, members.group_id')
			->from('dashee_members')
			->join('members', 'dashee_members.member_id = members.member_id')
			->where('dashee_members.site_id', $this->_site_id);

		if($group_id) $this->EE->db->where('members.group_id', $group_id);

		$member_qry = $this->EE->db->get()->result();
	
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
				$this->EE->db->update('dashee_members', array('config' => $layouts[$group_layouts[$member->group_id]['layout_id']]), array('id' => $member->id));
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
		$this->EE->db->delete('dashee_layouts', array('id' => $layout_id));
		$default = $this->EE->db->get_where('dashee_layouts', array('is_default' => TRUE))->row();
		$this->EE->db->update('dashee_member_groups_layouts', array('layout_id' => $default->id), array('layout_id' => $layout_id));
	}
	
	/**
	 * Get all member groups in CMS for layout assignment.
	 *
     * @access  public
	 * @return 	obj
	 */
	public function get_member_groups()
	{
		return $this->EE->db->select('group_id AS id, group_title AS title, group_description AS description')
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
		return $this->EE->db->select('*')
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
		$qry = $this->EE->db->get_where('dashee_settings', array('site_id' => $this->_site_id));

        // populate module settings if they don't already exist for the site in question (for MSM compatibility)
        if($qry->num_rows() <= 0)
        {
            $this->EE->db->insert_batch('dashee_settings', $this->_module_settings);
            $qry = $this->EE->db->get_where('dashee_settings', array('site_id' => $this->_site_id));
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
			$this->EE->db->update('dashee_settings', array('value' => $value), array('site_id' => $this->_site_id, 'key' => $key));
		}
	}
}
/* End of file dashee_model.php */
/* Location: /system/expressionengine/third_party/dashee/models/dashee_model.php */