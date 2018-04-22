<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

class MY_Controller extends CI_Controller {

    public $layout = "<br><br>Please don't forget to set a layout for this page. <br>Layout file must be kept in views/layout folder ";
    public $data = array("content_view" => "<br><br>Please select a content view for this page");
    public $base_controller = "";
    public $title = "";
    public $page_js = array();
    public $DeviceType;
    public $post_data;

    function __construct() {
        parent::__construct();
       /*header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
        header('Access-Control-Max-Age: 1000');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        $data = trim(file_get_contents('php://input'));*/
        /*if(isset($data) && !empty($data))
        {
            $this->post() = json_decode($data, TRUE);
        }*/
        

        
    }

}

/* End of file MY_Controller.php */
/* Location: application/core/MY_Controller.php */
