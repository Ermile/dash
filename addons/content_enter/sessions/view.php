<?php
namespace addons\content_enter\sessions;

class view extends \addons\content_enter\main\view
{

	/**
	 * view enter
	 *
	 * @param      <type>  $_args  The arguments
	 */
	public function view_sessions($_args)
	{
		$mySessions    = $this->model()->sessions_list();
		$mySessionData = [];
		foreach ($mySessions as $key => $row)
		{
			@$mySessionData[$key]['id']         = $row['id'];
			@$mySessionData[$key]['ip']         = long2ip($row['ip']);
			@$mySessionData[$key]['last']       = $row['last_seen'];
			@$mySessionData[$key]['browser']    = T_(ucfirst($row['agent_name']));
			@$mySessionData[$key]['browserVer'] = $row['agent_version'];
			@$mySessionData[$key]['os']         = $row['agent_os'];
			@$mySessionData[$key]['osVer']      = T_($row['agent_osnum']);

			if(isset($row['agent_os']))
			{
				switch ($row['agent_os'])
				{
					case 'nt':
						$mySessionData[$key]['os'] = T_('Windows');
						break;

					case 'lin':
						$mySessionData[$key]['os'] = T_('Linux');
						break;

					default:
						break;
				}
			}
		}

		$this->data->sessions_list = $mySessionData;
		// var_dump($this->data->sessions_list);
		// $myAgent = $this->data->sessions_list[0]['agent_agent'];
		// $myBrowser = \lib\utility\browserDetection::browser_detection('full_assoc', '', $myAgent);





		$this->data->page['title']   = T_('Active sessions');
		$this->data->page['desc']    = $this->data->page['title'];

	}
}
?>