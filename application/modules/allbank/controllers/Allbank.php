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
        $this->load->model('Postback_model', 'modelrepo');
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

        $data['status'] = false;
        $data['message'] = 'instapay';
        $this->response($data, Rest_Controller::HTTP_FORBIDDEN);
    }
        public function pesonet_post()
    {   

        $data['status'] = false;
        $data['message'] = 'pesonet';
        $this->response($data, Rest_Controller::HTTP_FORBIDDEN);
    }


            public function p2n_post()
    {   

        $data['status'] = false;
        $data['message'] = 'p2m';
        $this->response($data, Rest_Controller::HTTP_FORBIDDEN);
    }
    
    public function payment_get()
    {


    	$response = allbank_cashin();
        $this->response($response, Rest_Controller::HTTP_FORBIDDEN);
    }

}