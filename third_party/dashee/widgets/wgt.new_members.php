<?php

/**
 * New EE Members Widget
 *
 * Display static listing of 10 most recent EE members.
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Widget
 * @author		Chris Monnat
 * @link		http://chrismonnat.com
 */

class Wgt_new_members
{
	public $EE;
	public $title;
	public $wclass;
		
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();

		$this->title = 'New Members';
		$this->wclass = 'contentMenu';	
	}
	
	/**
	 * Index Function
	 *
	 * @return 	string
	 */
	public function index()
	{
		// get most recent 10 members from DB
		$results = $this->EE->db->select('member_id, username, join_date')
			->from('members')
			->order_by('join_date DESC')
			->limit(10)
			->get();
	
		// generate table HTML
		$display = '';
		if($results->num_rows() > 0)
		{
			foreach($results->result() as $row)
			{
				$display .= '
					<tr class="'.alternator('odd','even').'">
						<td><a href="'.BASE.AMP.'D=cp'.AMP.'C=myaccount'.AMP.'id='.$row->member_id.'">'.$row->username.'</a></td>
						<td>'.date('n/j/Y',$row->join_date).'</td>
					</tr>';			
			}
		}
		else
		{
			$display = '<tr><td colspan="2"><center>No members have joined yet.</center></td></tr>';
		}
	
		return '
			<table>
				<thead><tr><th>Username</th><th>Join Date</th></tr></thead>
				<tbody>'.$display.'</tbody>
			</table>
		';
	}
}
/* End of file wgt.new_members.php */
/* Location: /system/expressionengine/third_party/dashee/widgets/wgt.new_members.php */