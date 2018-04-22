<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class User_model extends Common_Model
{	
	function __construct(){
		parent::__construct();
	}

	function getUserList()
	{
		
		$this->db->select('*');
		$query = $this->db->get('user');
		$result = $query->result_array();
		return $result;
	}

	function registeredUsers($data)
	{
		$InsertData['first_name'] = $data->firstName;
		$InsertData['last_name'] = $data->lastName;
		$InsertData['user_name'] = $data->userName;
		$InsertData['email_id'] = $data->email;
		$InsertData['loginsessionkey'] = random_string('unique');;
		$InsertData['password'] = $data->password;
		$this->db->insert('user_auth', $InsertData);
		$returnData['loginsessionkey'] = $InsertData['loginsessionkey'];
		$returnData['user_id'] =  $this->db->insert_id();
		$returnData['firstName'] = $data->firstName;
		$returnData['lastName'] = $data->lastName;
		$returnData['email'] = $data->email;
		$returnData['userName'] = $data->userName;
		return $returnData;
	}

	function login($data)
	{
		$this->db->select('email_id, user_name, first_name, last_name, loginsessionkey');
		$this->db->where("email_id = '$data->email' 
                   AND password = '$data->password'");
		$query = $this->db->get('user_auth');
		if($query->num_rows()>0)
		{
			$row = $query->row_array();
			$row['Message'] = "Success";
			return $row;
		}else{
			$row['Message'] = "Error";
		}
		return $row;
	}
}