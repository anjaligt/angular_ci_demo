<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Product_model extends Common_Model
{	
	function __construct(){
		parent::__construct();
	}

	function getProductList()
	{
		$this->db->select('*');
		$query = $this->db->get('product');
		$result = $query->result_array();
		return $result;
	}

	function createProduct($data)
	{
		$InsertData['product_name'] = $data->name;
		$InsertData['product_description'] = $data->description;
		$InsertData['product_price'] = $data->price;
		$InsertData['status'] = 1;
		
		$this->db->insert('product', $InsertData);
	}
}