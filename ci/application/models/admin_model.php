<?
class Admin_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('ftp');
	}
	
	function getAgentsDB($table)
	{
		$query = $this->db->get($table);
		
		return $query;
	}
	
	function getNonLightAgents($table)
	{
		$data = array(
		               'is_lightagent' => FALSE,
		               'rr_status' => FALSE
		            );
		$query = $this->db->get_where($table, $data);
		return $query->result();
	}
	
	function get_data_where($table, $field, $id)
	{
		$query = $this->db->get_where($table, array($field => $id));
		return $query->result();
	}
	
	function markLightAgents($table, $userid)
	{
		$data = array(
		               'is_lightagent' => TRUE
		            );
		
		$this->db->where('userid', $userid);
		$result = $this->db->update('agents', $data); 
	
		return $result;
	}
	
	function populateAgentsTable($table, $name, $userid)
	{
		$query = $this->db->get_where($table, array('userid' => $userid));
		$result = $query->result();
		
		if(empty($result))
		{
			$data = array(
			   'name' => $name ,
			   'userid' => $userid ,
			   'rr_status' => FALSE
			);
			
			$this->db->insert($table, $data); 		
		}
		else
		{
			//do something
		}		
	}
	
	function update_data($table, $field, $id)
	{
		$result = $this->db->update($table, $_POST, array($field => $id));
		return $result->result();
	}
	
	/*
	function addimages($id, $filename)
	{
		$data = array(
           'filename' => $filename, 
		   'model_id' => $id
			);
		
		$result = $this->db->insert('images', $data);
	}
	
	function update_model_for_image($id, $filename)
	{
		$data = array(
		  'filename' => $filename
			);
		$this->db->where('id', $id);
		$this->db->update('model', $data);
	}
		
	function get_data($table)
	{ 
		if($table == "manufacturer")
			$this->db->order_by('name', 'ASC');
			
		$query = $this->db->get($table); 	
		return $query;
	}
	
	function get_data_like($table, $column, $match)
	{ 	
		$this->db->like($column, $match); 		
		$query = $this->db->get($table); 	
		return $query;
	}
	
	function get_data_limit($table, $limit)
	{
		$this->db->order_by('id', 'RANDOM');
		$query = $this->db->get($table, $limit); 	
		return $query;
	}
	
	
	function del_data($table, $id)
	{		
		$result = $this->db->delete($table, array('id' => $id));
		return $result;
	}
	
	function del_img($id, $filename)
	{		
		//$result = $this->db->delete('images', array('id' => $id));
		$result = $this->db->delete('images', array('id' => $id));
		$img_Arr = explode(".", $filename); 
		$img = $img_Arr[0] . "_thumb." . $img_Arr[1];
		unlink('www/images/models/1/' . $filename);
		unlink('www/images/models/1/tn/' . $img);
		unlink('www/images/models/1/med/' . $filename);
	}
	
	
	function set_default_img($id, $filename)
	{
		$data = array('default_img' => $filename );
		$result = $this->db->update('model', $data, array('id' => $id));
		return $result;
	}
	
	function put_default_to_img($id, $filename)
	{
		$data = array('filename' => $filename );
		$result = $this->db->update('images', $data, array('id' => $id));
		return $result;
	}

	
	function set_sold($id, $data)
	{
		$data = array('sold' => $data);
		$result = $this->db->update('model', $data, array('id' => $id));
		return $result;
	}
	
	function set_featured($id, $data)
	{
		$data1 = array(
               'car_of_the_week' => 0
            );
		$this->db->update('model', $data1, "id > 0");
		
		$data = array('car_of_the_week' => $data);
		$result = $this->db->update('model', $data, array('id' => $id));
		return $result;
	}
	
	function reset_def_img($id)
	{
		$data = array(
		  'no_file_selected	' => 1,
		  'default_img' => ''
			);
		$this->db->where('id', $id);
		$this->db->update('model', $data);
	}
	
	function authenticate()
	{
		$this->db->from('admin');
		$this->db->where('username', $_POST['username']);
		$this->db->where('password', $_POST['password']);
		$query = $this->db->get(); 	
		$row = $query->row();
		return $row;
	}	
	*/
}
?>