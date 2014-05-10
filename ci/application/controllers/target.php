<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Target extends CI_Controller 
{
	function  __construct()
	{
		parent::__construct();
		$this->load->helper('url');
		$this->load->helper('form');
		$this->load->helper('file');
		$this->load->library('session');
		$this->load->model('admin_model');
	}
	
	public function index()
	{
		//error_reporting(1);
		require("curl.php");
		
		$this->getAgents(); //	//first, get list of ALL agents, including admins and owners 
		
		$this->assignToAgent();
		
		$data = $this->admin_model->getAgentsDB('agents');
	}

	public function getAgents()
	{
		$data = curlWrap("/users.json?role[]=admin&role[]=agent", null, "GET");
		
		// separate headers from data
		list($header, $body) = explode("\r\n\r\n", $data, 2);
		$f_header = http_parse_headers($header);
		$f_body = json_decode($body);
		
		//var_dump($f_body); exit;
		
		foreach ($f_body as $val) { 					
			if(is_array($val))
			{
				for($x=0; $x<count($val); $x++)
				{
					echo "Agent: " . $val[$x]->name . " (" . $val[$x]->id . ") -- " . $val[$x]->role . "<br>";
					$this->initializeDB('agents', $val[$x]->name, $val[$x]->id, $val[$x]->custom_role_id); //uncomment to intitialize user db				
				}	
			}
		}
	}
	
	public function initializeDB($table, $name, $id, $custom_role_id)
	{
		$data = $this->admin_model->populateAgentsTable($table, $name, $id);
		
		if($this->getLightAgentID() == $custom_role_id)
		{
			$this->admin_model->markLightAgents($table, $id);
		}
	}
	
	public function getLightAgentID()
	{
		$data = curlWrap("/custom_roles.json", null, "GET");
		
		// separate headers from data
		list($header, $body) = explode("\r\n\r\n", $data, 2);
		$f_header = http_parse_headers($header);
		$f_body = json_decode($body);
		
		//var_dump($f_body); exit;
		
		$lightagent_ID = 0;
		foreach ($f_body as $val) { 					
			if(is_array($val))
			{
				for($x=0; $x<count($val); $x++)
				{
					//echo "Role: " . $val[$x]->name . " (" . $val[$x]->id . ") <br>";					
					if($val[$x]->name == "Light Agent")
					{
						$lightagent_ID = $val[$x]->id;
					}
				}	
			}
		}
		
		return $lightagent_ID;
	}
	
	public function assignToAgent()
	{
		$data = $this->admin_model->get_data_where('agents', 'rr_status', TRUE); //get w/c agent was last assigned a ticket
		
		if(empty($data)) //assign to first agent
		{
			echo "DSDS";
			$non_lightagents = $this->admin_model->getNonLightAgents('agents');
		
			var_dump($non_lightagents); 
			
			$this->db->last_query();
			
			exit;
		
			/*
			$agent_id = 652646516;
			$payload = array('ticket' => array('assignee_id' => $agent_id));
			$json = json_encode($payload);
			
			$data = curlWrap("/tickets/" . $_GET['id'] . ".json", $json, "PUT");
			
			// separate headers from data
			list($header, $body) = explode("\r\n\r\n", $data, 2);
			$f_header = http_parse_headers($header);
			$f_body = json_decode($body);
			
			$status = $f_header['Status'];
			echo $status; //if prints 200, ticket was found assigned successfully
			$pos = strpos($status, "404");

			if($pos !== false) //if ticket found
			{
				echo "yes";
			}
			else //if not found
			{
				echo "no";
			}
			
			//var_dump($f_body);
			$x = 1;
			foreach ($f_body as $val) { 
				echo $x++; 
			}
			*/
		}
		else //assign to next agent
		{
			$non_lightagents = $this->admin_model->getNonLightAgents('agents');
			//var_dump($non_lightagents);  
			$this->assignTicket($non_lightagents[0]->userid);
			
		}
	}
	
	public function assignTicket($userid)
	{
		$agent_id = $userid;
		$payload = array('ticket' => array('assignee_id' => $agent_id));
		$json = json_encode($payload);
		
		$data = curlWrap("/tickets/" . $_GET['id'] . ".json", $json, "PUT");
		
		// separate headers from data
		list($header, $body) = explode("\r\n\r\n", $data, 2);
		$f_header = http_parse_headers($header);
		$f_body = json_decode($body);
		
		$status = $f_header['Status'];
		echo $status; //if prints 200, ticket was found assigned successfully
		$pos = strpos($status, "404");

		if($pos !== false) //if ticket found
		{
			echo "yes";
			
			//CODE UPDATE DATABASE HERE REMOVE CURRENT ASSIGN, UPDATE RECENT ASSIGNED
		}
		else //if not found
		{
			echo "no";
		}
	}
	
	/*
	function deleteTopic($id)
	{
		$data = curlWrap("/topics/" . $id . ".json", null, "DELETE");
	}
	*/	
}
