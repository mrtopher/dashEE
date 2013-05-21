<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$lang = array(	
	'dashee_module_name' 		=> 'dashEE',
	'dashee_module_description' => 'Fully customizable alternate CP dashboard.',

	'module_home' => 'dashEE Home',
	
	'dashee_term' 		=> 'Dashboard',	
	'dashee_settings' 	=> 'Settings',	
	
	// buttons
	'btn_collapse'	=> '- Collapse All',
	'btn_expand'	=> '+ Expand All',
	'btn_widgets'	=> 'Widgets',
	'btn_settings'	=> 'Settings',
	'btn_settings2'	=> 'Settings 2',
	'btn_save'		=> 'Save Layout',
	'btn_back_to_dashboard' => 'Back to Dashboard',
	
	// table captions
	'cap_general'		=> 'General',
	'cap_layouts'		=> 'Saved Layouts',
	'cap_group_layouts'	=> 'Default Member Group Layouts',
	
	// table headers
	'th_preference' 	=> 'Preference',
	'th_setting' 		=> 'Setting',
	'th_name' 			=> 'Name',
	'th_description' 	=> 'Description',
	'th_options'		=> 'Options',
	'th_member_group' 	=> 'Member Group',
	'th_layout'			=> 'Layout',
	'th_locked'			=> 'Lock layout',
	
	// preferences
	'pref_crumb_term'	=> 'Term to use in breadcrumb nav link?',
	'pref_num_columns'	=> 'Number of columns?',

	// extension settings
	'redirect_admins'	=> 'Redirect Super-Admins to dashEE from CP Homepage?',
	
	// labels
	'lbl_layout_name' 	=> 'Layout name',
	'lbl_layout_desc' 	=> 'Description',
	'lbl_lock'			=> 'Lock layout',
	
	// confirmation messages
	'conf_remove_widget'  	=> 'Are you sure you want to remove this widget from your dashboard?',
	'conf_delete_layout'  	=> 'Are you sure you want to delete this layout?',
	'conf_reset_layout'  	=> 'This action will reset the dashboard for all members belonging to this group to the default you have specified.<br /><br />Are you sure you want to reset?',
	'conf_load_layout'		=> '<strong>WARNING:</strong> this will reset your current dashboard and replace it with this saved layout.',
	
	// flash messages
	'flash_layout_updated' 				=> 'Default layout has been updated.',
	'flash_layout_not_updated'			=> 'Unable to load selected layout.',
	'flash_layout_loaded'				=> ' has been loaded to your dashboard.',
	'flash_layout_not_loaded'			=> 'Unable to load selected layout.',
	'flash_layout_deleted'				=> ' has been deleted.',
	'flash_layout_not_deleted'			=> 'Unable to delete selected layout.',
	'flash_group_default_updated'		=> 'Member group defaults have been updated.',
	'flash_group_default_not_updated'	=> 'Member group defaults could not be updated.',
	'flash_group_layout_reset'			=> 'Layout has been reset for ',
	'flash_group_layout_not_reset'		=> 'Layout could not be reset for ',
	'flash_settings_updated'			=> 'Your settings have been updated.',
	
	// terms
	'trm_default_layout' 	=> 'Default layout',
	'trm_whats_this'		=> 'What\'s this?',

	// help
	'help_layouts' => '	<p>Saved layouts give EE Super Admins the ability to save dashboard configurations for later use or assign those configurations as the 
						default for users of a specific member group. Use the options below to <strong>load</strong> a saved layout to your own dashboard, mark a 
						new layout as <strong>default</strong> or <strong>delete</strong> obsolete saved layouts.</p><br />
						<p>The default layout is used for new users accessing the dashboard for the first time with no other layout assigned.</p><br />
						<p>The default member group layouts listed below allow you to assign specific saved layouts as the default for selected membership groups. 
						This allows you to create different dashboard layouts tailored for different member groups. <strong>Please note:</strong> the defaults 
						set here will only affect new users who are visiting the module for the first time. If they have already logged in and viewed the dashEE 
						module their existing configuration will remain the same.</p>',
	'help_lock'		=> '<p>Locking a member groups dashEE layout will prevent those users from making any changes to their dashboard. They will not see the 
						standard Expand All, Collapse All, Widgets or Setting options. They will also not be able to move or modify widget settings.</p>',
	
	// misc.
	'permission_denied' => 'You do not have permission to use this widget.',

	// widgets
	'wgt_create_links_name' 		=> 'EE Create Links',
	'wgt_create_links_description' 	=> 'EE create links from default control panel.',
	
	'wgt_modify_links_name' 		=> 'EE Modify Links',
	'wgt_modify_links_description' 	=> 'EE modify links from default control panel.',
	
	'wgt_view_links_name' 			=> 'EE View Links',
	'wgt_view_links_description' 	=> 'EE view links from default control panel.',

	'wgt_recent_entries_name' 			=> 'Recent Entries',
	'wgt_recent_entries_description' 	=> 'Displays 10 most recent EE entries.',

	'wgt_recent_comments_name' 			=> 'Recent Comments',
	'wgt_recent_comments_description' 	=> 'Displays 10 most recent EE entry comments.',

	'wgt_welcome_name' 				=> 'Welcome to dashEE',
	'wgt_welcome_description' 		=> 'General module info and links.',

	'wgt_new_members_name' 			=> 'New Members',
	'wgt_new_members_description' 	=> 'The last 10 members who have joined your site.',

	'wgt_feed_reader_name' 			=> 'RSS Feed Reader',
	'wgt_feed_reader_description' 	=> 'Display the 5 most recent posts from an RSS feed.',
	
	'wgt_blank_name' 				=> 'Blank Widget',
	'wgt_blank_description' 		=> 'Blank text widget, can be configured with whatever content you wish.',

	'wgt_text_name' 				=> 'Static text widget',
	'wgt_text_description' 			=> 'A basic widget with static text.',
);

/* End of file lang.dashee.php */
/* Location: /system/expressionengine/third_party/dashee/language/english/lang.dashee.php */