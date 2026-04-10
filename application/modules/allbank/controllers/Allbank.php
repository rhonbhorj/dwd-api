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

    public function instapay_post()
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