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
		
		//$this->getAgents(); //	//first, get list of ALL agents, including admins and owners; this function will populate agents table as well 
		
		$this->assignToAgent();
		
		$data = $this->admin_model->getAgentsDB('agents');
	}

	public function getAgents()
	{
		echo "inside getAgents<br>";
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
		echo "inside initializeDB<br>";
		$data = $this->admin_model->populateAgentsTable($table, $name, $id);
		
		if($this->getLightAgentID() == $custom_role_id)
		{
			//$this->admin_model->markLightAgents($table, $id);
			$this->admin_model->deleteLightAgents($table, $id);
		}
	}
	
	public function getLightAgentID()
	{
		echo "inside getLightAGentID<br>";
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
		echo "inside assignToAgent<br>";
		$data = $this->admin_model->get_data_where('agents', 'rr_status', TRUE); //get w/c agent was last assigned a ticket
		
		if(empty($data)) //assign to first agent
		{
			$this->assignToFirstAgent();
		}
		else //assign to next agent
		{
			$this->assignToNext();
		}
	}
	
	public function assignToFirstAgent()
	{
		echo "db is clear....assigning to first agent --- ";
		$non_lightagents = $this->admin_model->getNonLightAgents('agents'); //get next non-light agent data to assign ticket	
		$this->assignTicket($non_lightagents[0]->userid);		
		$this->admin_model->update_data_where('agents','rr_status',TRUE, 'userid' , $non_lightagents[0]->userid);
		echo "DSDS";
	}
	
	public function assignToNext()
	{
		error_reporting(0);
		//get first and last record for comparison when to loop back
		$last_record = $this->admin_model->get_last_record('agents');
		$last_record = $last_record[0]->id;
		$first_record = $this->admin_model->get_first_record('agents');
		$first_record = $first_record[0]->id;
		
		//get last user id ticket was assigned to
		$data = $this->admin_model->get_data_where('agents','rr_status',TRUE);			
		$db_id = $data[0]->id;
		echo $db_id . "<br>";
		//once we have the id to determine next assignment reset rr_status
		$clear_db = $this->admin_model->update_data('agents','rr_status',FALSE); // reset rr_status
		
		//now that rr_status is clear assign to next agent
		if($clear_db) 
		{
			//get next agent id
			$result = $this->admin_model->get_next_Agent($db_id);
			
			//check if next agent is the last agent in the table
			if($db_id == $last_record)
			{
				//if true assign to first agent
				$this->admin_model->update_data_where('agents','rr_status',TRUE, 'id' , $first_record);
			}
			else 
			{
				//if false assign to next agent
				$this->admin_model->update_data_where('agents','rr_status',TRUE, 'id' , $result[0]->id); 		
			}
		}
	}
	
	public function assignTicket($userid)
	{
		echo $userid . "<br>";
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
		$pos = strpos($status, "OK");

		//echo $pos; exit;

		if($pos !== false) //if ticket found
		{
			echo "yes";
			
			$this->admin_model->update_data_where('agents','rr_status',TRUE, 'userid', $agent_id);
		}
		else //if not found
		{
			echo "Ticket ERROR: Ticket number not found!";
		}
	}
	
	/*
	function deleteTopic($id)
	{
		$data = curlWrap("/topics/" . $id . ".json", null, "DELETE");
	}
	*/	
}
