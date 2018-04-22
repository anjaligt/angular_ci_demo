<?php
require APPPATH . '/libraries/REST_Controller.php';

class Common_API_Controller extends REST_Controller {

    public $post_data;
    public $return = array();

    public function __construct() {
        parent::__construct();
        /* Gather Inputs - starts */
        $this->post_data = $this->post();
        /* Gather Inputs - ENDS */
        
        //Language code for rest services
        if (is_array($this->response->lang)) {
            $language = $this->response->lang[0];
        } else {
            $language = $this->response->lang;
        }
        if($language != $this->config->item('language')) {
            $this->config->set_item('language', $language);
            $loaded = $this->lang->is_loaded;
            $this->lang->is_loaded = array();
            foreach($loaded as $lang) {
                $this->load->language(str_replace("_lang.php","",$lang), $language);
            }
        }//End language code

        /* Define return variables - starts */
        $this->return = array(
            'ResponseCode' => 200,
            'Message' => 'Success',
            'Data' => array(),
            'ServiceName' => $this->router->class . '/' . $this->router->method
        );

        //Set REST API Validation Configuration
        $this->form_validation->set_rest_validation(TRUE, $this->post_data);
    }    
}
?>