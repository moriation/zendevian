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
		
		$data = $this->admin_model->getAgentsDB('agents');
		
		var_dump($data);
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
				}	
			}
		}
	}
	
	function deleteTopic($id)
	{
		$data = curlWrap("/topics/" . $id . ".json", null, "DELETE");
	}
		
}
