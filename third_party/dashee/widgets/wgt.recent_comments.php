<?php

/**
 * Recent Comments Widget
 *
 * Display static listing of 10 most recent entry comments.
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Widget
 * @author		Chris Monnat
 * @link		http://chrismonnat.com
 */

class Wgt_recent_comments
{
	public 	$title;
	
	private $_EE;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->title = 'Recent Comments';
	
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
		$this->_EE->db->select('entry_id, channel_id, name, comment_date');
		$this->_EE->db->from('comments');
		$this->_EE->db->order_by('comment_date DESC');
		$this->_EE->db->limit(10);
		$comments = $this->_EE->db->get();
	
		// generate table HTML
		$display = '';
		if($comments->num_rows() > 0)
		{
			foreach($comments->result() as $comment)
			{
				$display .= '
					<tr class="'.alternator('odd','even').'">
						<td><a href="'.BASE.AMP.'">'.$comment->name.'</a></td>
						<td>'.date('m/d/Y',$comment->comment_date).'</td>
					</tr>';
			}
		}
		else
		{
			$display = '<tr><td colspan="2"><center>No comments have been posted.</center></td></tr>';
		}
		
		return '
			<table>
				<thead><tr><th>Name</th><th>Comment Date</th></tr></thead>
				<tbody>'.$display.'</tbody>
			</table>
		';
	}
}
/* End of file wgt.recent_comments.php */
/* Location: /system/expressionengine/third_party/dashee/widgets/wgt.recent_comments.php */