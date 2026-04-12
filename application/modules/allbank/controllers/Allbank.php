<?php
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';

require APPPATH . 'libraries/Format.php';
require APPPATH . 'libraries/Authorization_Token.php';
require_once( APPPATH . 'services/DwdApiService.php' );

class Allbank extends REST_Controller
{

function __construct()
{
    parent::__construct();

    date_default_timezone_set('Asia/Manila');

    $this->load->model('Allbank_model', 'modelrepo');
    $this->load->helper('allbank_helper');

    // Remove X-Powered-By
    header_remove('X-Powered-By');

    // CSP
    $this->output->set_header(
        "Content-Security-Policy: default-src 'none'; frame-ancestors 'none'; base-uri 'none';"
    );

    // Other headers
    $this->output->set_header("X-Content-Type-Options: nosniff");
    $this->output->set_header("X-Frame-Options: DENY");
}

    public function token_get()
    {
        $this->response(get_token($this), Rest_Controller::HTTP_FORBIDDEN);
    }

    public function index_get()
    {   

        $data['status'] = false;
        $data['message'] = 'Forbidden';
        $this->response($data, Rest_Controller::HTTP_FORBIDDEN);
    }

    // public function instapay_post()
    // {   

    //     $pData = file_get_contents('php://input');

    //     $encData = json_encode($pData);
    //     $response['callback_data'] = json_decode($encData, true).$this->uri->uri_string();
    //     $c_log= $this->modelrepo->callback_logs($response);
        
    //     $dateToday = date('Y-m-d\TH:i:s.vP');

    //     $resp['payment_channel'] = 'AllBank';
    //     $resp['payment_datetime'] = $dateToday;
    //     $resp['status'] = 'Successful';
    //     $resp['message'] = 'Payment Received';
      
    //     $this->response($resp, Rest_Controller::HTTP_OK);
    // }


    public function instapay_post()
{
    // Validate exact endpoint
    if ($this->uri->uri_string() !== 'webhook/allbank/instapay') {
        
        $resp['status'] = 'false';
        $resp['message'] = 'Invalid endpoint';
        $this->response($resp, Rest_Controller::HTTP_NOT_FOUND);
    }

    // Validate JSON
    $pData = file_get_contents('php://input');
    $data = json_decode($pData, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
       
        $resp['status'] = 'false';
        $resp['message'] = 'Invalid JSONr';
         $this->response($resp, Rest_Controller::HTTP_FORBIDDEN);
    }

 
       $encData = json_encode($pData);
        $response['callback_data'] = json_decode($encData, true).$this->uri->uri_string();
     
        
    $this->modelrepo->callback_logs($response);

    // Response
    $dateToday = date('Y-m-d\TH:i:s.vP');

    $resp = [
        'payment_channel' => 'AllBank',
        'payment_datetime' => $dateToday,
        'status' => 'Successful',
        'message' => 'Payment Received'
    ];

    $this->response($resp, Rest_Controller::HTTP_OK);
}
        public function pesonet_post()
    {   
        $pData = file_get_contents('php://input');

       $encData = json_encode($pData);
        $response['callback_data'] = json_decode($encData, true).$this->uri->uri_string();
        $c_log= $this->modelrepo->callback_logs($response);
        
        $dateToday = date('Y-m-d\TH:i:s.vP');

        $resp['payment_channel'] = 'AllBank';
        $resp['payment_datetime'] = $dateToday;
        $resp['status'] = 'Successful';
        $resp['message'] = 'Payment Received';
      
        $this->response($resp, Rest_Controller::HTTP_OK);
    }


            public function p2m_post()
    {   
        $pData = file_get_contents('php://input');

        $encData = json_encode($pData);
        $response['callback_data'] = json_decode($encData, true).$this->uri->uri_string();
        $c_log= $this->modelrepo->callback_logs($response);
        
        $dateToday = date('Y-m-d\TH:i:s.vP');

        $resp['payment_channel'] = 'AllBank';
        $resp['payment_datetime'] = $dateToday;
        $resp['status'] = 'Successful';
        $resp['message'] = 'Payment Received';
      
        $this->response($resp, Rest_Controller::HTTP_OK);;
    }



    
    public function payment_get()
    {


    	$response = allbank_cashin();
        $this->response($response, Rest_Controller::HTTP_FORBIDDEN);
    }

}