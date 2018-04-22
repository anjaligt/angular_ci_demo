<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
/**
 * Example
 * This Class used for REST API
 * (All THE API CAN BE USED THROUGH POST METHODS)
 * @package     CodeIgniter
 * @subpackage  Rest Server
 * @category    Controller
 * @author      Phil Sturgeon
 * @link        http://philsturgeon.co.uk/code/
 */
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
class Products extends REST_Controller {
    //header('Access-Control-Allow-Origin : *');

    /**
     * @Summary: call parent constructor
     * @create_date: Thursday, July 11, 2014
     * @last_update_date:
     * @access: public
     * @param:
     * @return:
     */
    function __construct() {
        parent::__construct();
    }

    
    function getProductList_post() {
        $Return['ResponseCode'] = 200;
        $Return['Message'] = lang('success');
        $Return['Data'] = array();
        $Return['ServiceName'] = 'api/products/getProductList';
        $this->load->model('product_model');
        $Return['records'] = $this->product_model->getProductList();
        $this->response($Return);
    }

    function createProduct_post() {
        $Return['ResponseCode'] = 200;
        $Return['Message'] = lang('success');
        $Return['Data'] = array();
        $Return['ServiceName'] = 'api/products/createProduct';
        $this->load->model('product_model');

        $postdata = file_get_contents("php://input");
        $data = json_decode($postdata);
        

        $Return['records'] = $this->product_model->createProduct($data);
        $this->response($Return);
    }
}