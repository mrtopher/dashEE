<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$lang = array(	
	'dashee_module_name' => 
	'dashEE',

	'dashee_module_description' => 
	'Fully customizable alternate CP dashboard.',

	'module_home' => 'dashEE Home',
	
	'dashee_term' 	=> 'Dashboard',	
	'dashee_settings' 	=> 'Settings',	
	
	'btn_collapse'	=> '- Collapse All',
	'btn_expand'	=> '+ Expand All',
	'btn_widgets'	=> 'Widgets',
	'btn_settings'	=> 'Settings',
	'btn_save'		=> 'Save Layout',
	
	'capGeneral'		=> 'General',
	'capLayouts'		=> 'Saved Layouts',
	'capGroupLayouts'	=> 'Default Member Group Layouts',
	
	'thPreference' 	=> 'Preference',
	'thSetting' 	=> 'Setting',
	'thName' 		=> 'Name',
	'thDescription' => 'Description',
	'thOptions'		=> 'Options',
	'thMemberGroup' => 'Member Group',
	'thLayout'		=> 'Layout',
	'thLocked'		=> 'Lock layout',
	
	'prefCrumbTerm'	=> 'Term to use in breadcrumb nav link?',
	'prefNumColumns'=> 'Number of columns?',
	'prefReset'		=> 'Reset all user dashboards to the new defaults set above.',

	// extension settings
	'redirect_admins'	=> 'Redirect Super-Admins to dashEE from CP Homepage?',
	
	'lblLayoutName' => 'Layout name',
	'lblLayoutDesc' => 'Description',
	'lblLock'		=> 'Lock layout',
	
	'confRemoveWidget'  => 'Are you sure you want to remove this widget from your dashboard?',
	'confDeleteLayout'  => 'Are you sure you want to delete this layout?',
	'confResetLayout'  => 'This action will reset the dashboard for all members belonging to this group to the default you have specified.<br /><br />Are you sure you want to reset?',
	'confLoadLayout'	=> '<strong>WARNING:</strong> this will reset your current dashboard and replace it with this saved layout.',
	
	'flashLayoutUpdated' 			=> 'Default layout has been updated.',
	'flashLayoutNotUpdated'			=> 'Unable to load selected layout.',
	'flashLayoutLoaded'				=> ' has been loaded to your dashboard.',
	'flashLayoutNotLoaded'			=> 'Unable to load selected layout.',
	'flashLayoutDeleted'			=> ' has been deleted.',
	'flashLayoutNotDeleted'			=> 'Unable to delete selected layout.',
	'flashGroupDefaultUpdated'		=> 'Member group defaults have been updated.',
	'flashGroupDefaultNotUpdated'	=> 'Member group defaults could not be updated.',
	'flashGroupLayoutReset'			=> 'Layout has been reset for ',
	'flashGroupLayoutNotReset'		=> 'Layout could not be reset for ',
	
	'default_layout' => 'Default layout',
	
	'widget_added'	=> 'Widget added.',
	
	'permission_denied' => 'You do not have permission to use this widget.',
	
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
	
	// Widgets
	'wgt_create_links_name' => 'EE Create Links',
	'wgt_create_links_description' => 'EE create links from default control panel.',
	
	'wgt_modify_links_name' => 'EE Modify Links',
	'wgt_modify_links_description' => 'EE modify links from default control panel.',
	
	'wgt_view_links_name' => 'EE View Links',
	'wgt_view_links_description' => 'EE view links from default control panel.',

	'wgt_recent_entries_name' => 'Recent Entries',
	'wgt_recent_entries_description' => 'Displays 10 most recent EE entries.',

	'wgt_recent_comments_name' => 'Recent Comments',
	'wgt_recent_comments_description' => 'Displays 10 most recent EE entry comments.',

	'wgt_welcome_name' => 'Welcome to dashEE',
	'wgt_welcome_description' => 'General module info and links.',

	'wgt_new_members_name' => 'New Members',
	'wgt_new_members_description' => 'The last 10 members who have joined your site.',

	'wgt_feed_reader_name' => 'RSS Feed Reader',
	'wgt_feed_reader_description' => 'Display the 5 most recent posts from an RSS feed.',
	
	'wgt_blank_name' => 'Blank Widget',
	'wgt_blank_description' => 'Blank text widget, can be configured with whatever content you wish.',

	'wgt_text_name' => 'Static text widget',
	'wgt_text_description' => 'A basic widget with static text.',
	
);

/* End of file lang.dashee.php */
/* Location: /system/expressionengine/third_party/dashee/language/english/lang.dashee.php */