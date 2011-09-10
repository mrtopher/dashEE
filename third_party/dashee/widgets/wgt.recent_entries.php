<?php

/**
 * Recent Entries Widget
 *
 * Display static listing of 10 most recent channel entries.
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Widget
 * @author		Chris Monnat
 * @link		http://chrismonnat.com
 */

class Wgt_recent_entries
{
	public 	$title;
	public $wclass;
	
	private $_EE;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->title = 'Recent Entries';
		$this->wclass = 'contentMenu';	
	
		$this->_EE =& get_instance();
	}
	
	// ----------------------------------------------------------------

	/**
	 * Index Function
	 *
	 * @return 	string
	 */
	public function index()
	{
		// get most recent 10 entries from DB
		$this->_EE->db->select('entry_id, channel_id, title, entry_date');
		$this->_EE->db->from('channel_titles');
		$this->_EE->db->order_by('entry_date DESC');
		$this->_EE->db->limit(10);
		$entries = $this->_EE->db->get();
	
		// generate table HTML
		$display = '';
		if($entries->num_rows() > 0)
		{
			foreach($entries->result() as $entry)
			{
				$display .= '
					<tr class="'.alternator('odd','even').'">
						<td><a href="'.BASE.AMP.'D=cp'.AMP.'C=content_publish'.AMP.'M=entry_form'.AMP.'channel_id='.$entry->channel_id.AMP.'entry_id='.$entry->entry_id.'">'.$entry->title.'</a></td>
						<td>'.date('m/d/Y',$entry->entry_date).'</td>
					</tr>';
			}
		}
		else
		{
			$display = '<tr><td colspan="2"><center>No entries have been created.</center></td></tr>';
		}
		
		return '
			<table>
				<thead><tr><th>Title</th><th>Entry Date</th></tr></thead>
				<tbody>'.$display.'</tbody>
			</table>
		';
	}
}
/* End of file wgt.recent_entries.php */
/* Location: /system/expressionengine/third_party/dashee/widgets/wgt.recent_entries.php */