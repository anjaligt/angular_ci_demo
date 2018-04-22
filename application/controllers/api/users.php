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
class Users extends REST_Controller {
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

    function login_post() {
        $Return['ResponseCode'] = 200;
        $Return['Message'] = lang('success');
        $Return['Data'] = array();
        $Return['ServiceName'] = 'api/user/login';
        $postdata = file_get_contents("php://input");
        $data = json_decode($postdata);
        $this->load->model('user_model');
        $Return['Data'] = $this->user_model->login($data);
        if($Return['Data']['Message']=='Success')
        {
            $Return['ResponseCode'] = 200;
        }else{
            $Return['ResponseCode'] = 401;
        }
        $this->response($Return);
    }

    function getUserList_post() {
        $Return['ResponseCode'] = 200;
        $Return['Message'] = lang('success');
        $Return['Data'] = array();
        $Return['ServiceName'] = 'api/user/getUserList';
        $this->load->model('user_model');
        $Return['Data'] = $this->user_model->getUserList();
        $this->response($Return);
    }

    function registeredUser_post() {
        $Return['ResponseCode'] = 200;
        $Return['Message'] = lang('success');
        $Return['Data'] = array();
        $postdata = file_get_contents("php://input");
        $data = json_decode($postdata);
        $Return['ServiceName'] = 'api/user/registeredUser';
        $this->load->model('user_model');
        $Return['Data'] = $this->user_model->registeredUsers($data);
        $this->response($Return);
    }

    function editUsers_post() {
        $Return['ResponseCode'] = 200;
        $Return['Message'] = lang('success');
        $Return['Data'] = array();
    }

}