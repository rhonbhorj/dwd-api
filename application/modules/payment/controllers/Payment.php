<?php
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';

require APPPATH . 'libraries/Format.php';
require APPPATH . 'libraries/Authorization_Token.php';
require_once( APPPATH . 'services/DwdApiService.php' );

class Payment extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Manila');
        $this->load->model('api_model', 'modelrepo');
        
         $this->DwdApiService = new DwdApiService();
        $this->authorization_token = new Authorization_Token();
    }


    public function index_get()
    {   
        $data['status'] = false;
        $data['status_code'] = 403;
        $data['message'] = 'Forbidden';
        $this->response($data, Rest_Controller::HTTP_FORBIDDEN);
    }

    public function index_post()
    {
        $data['status'] = false;
        $data['status_code'] = 403;
        $data['message'] = 'Forbidden';
        $this->response($data, Rest_Controller::HTTP_FORBIDDEN);
    }


    function vallidate_access()
    {
        $headers = $this->input->request_headers();
        $today = date('Y-m-d H:i:s');

        $head = checkHeader($this);
        $validateToken = $this->authorization_token->validateToken($headers);
        if ($validateToken['status'] == false) {

            $result['status'] = false;
            $result['status_code'] = 403;
            $result['message'] = $validateToken['message'];
            // $result['data'] = $validateToken;
            $this->response($result, Rest_Controller::HTTP_FORBIDDEN);
        } elseif ($head['status'] == false) {

            $result['status'] = false;
            $result['status_code'] = 403;
            $result['data'] = $head;
            $this->response($result, Rest_Controller::HTTP_FORBIDDEN);
        } else {

            $result['status'] = true;
            $result['status_code'] = 201;
            $result['data'] = $head;
           
        }

        return $result;
    }

    private function sanitizeInput($input)
    {
        return preg_replace("/[^a-zA-Z0-9\s_,.-]/", "", trim($input));
    }

 

    public function get_api_user_post()
    {
        $today = date('Y-m-d H:i:s');
        $AVR = true;
        $chkAcess = $this->vallidate_access();

        if ($chkAcess['status'] == false) {

            $AVR = $chkAcess['status'];
            $resp = $chkAcess['data'];
        } else {

            $this->form_validation->set_rules('sess_id', 'sess_id', 'trim|required');

            $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

            switch ($contentType) {
                case 'application/json':
                    $json = file_get_contents('php://input');
                    $_POST = json_decode($json, true);
                    $datapost = $_POST;
                    break;
                default:
                    $datapost = array(
                        'sess_id' => $this->input->post('sess_id', true)
                    );
            }

            if ($this->form_validation->run() == FALSE) {
                $FVE = $this->form_validation->error_array();
                $this->response([
                    'status' => false,
                     'status_code' => 401,

                    'message' => 'Error validation',
                    'data' => $FVE
                ], Rest_Controller::HTTP_UNAUTHORIZED);
            } else {

                $pdata = array(
                    'sess_id' => $this->sanitizeInput($datapost['sess_id'])
                );
                $validateSession = $this->modelrepo->validate_session($pdata);
                if ($validateSession == false) {

                    $AVR = false;
                    $resp['status'] = false;
                    $resp['status_code'] = 1001;
                    $resp['message'] = "Session denied";
                } else {

                    $apiUserList = $this->modelrepo->api_user_list();

                    $resp['status'] = true;
                    $resp['status_code'] = 200;
                    $resp['message'] = "Api user list";
                    $resp['data'] = $apiUserList;
                }
            }
        }
        if ($AVR) {

            $this->response($resp, Rest_Controller::HTTP_OK);
        } else {

            $this->response($resp, Rest_Controller::HTTP_UNAUTHORIZED);
        }
    }

 

    public function create_qr_post()
    {
        $today    = date('Y-m-d H:i:s');
        $AVR      = true;
        $chkAcess = $this->vallidate_access();

        if ($chkAcess['status'] == false) {
        //    $AVR      = false;
        
            // $this->response($chkAcess['data'], Rest_Controller::HTTP_UNAUTHORIZED);/
        } else {
            $this->form_validation->set_rules('amount', 'amount', 'trim|required');
            $this->form_validation->set_rules('reference_number', 'reference_number', 'trim|required');
            $this->form_validation->set_rules('acount_no', 'acount_no', 'trim|required');

            $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

            switch ($contentType) {
                case 'application/json':
                    $json     = file_get_contents('php://input');
                    $_POST    = json_decode($json, true);
                    $datapost = $_POST;
                    break;
                default:
                    $datapost = array(
                        'amount'           => $this->input->post('amount', true),
                        'reference_number' => $this->input->post('reference_number', true),
                        'acount_no'        => $this->input->post('acount_no', true)

                    );
            }

            if ($this->form_validation->run() == FALSE) {
                $FVE = $this->form_validation->error_array();
                $this->response([
                    'status'        => false,
                    'status_code'   => 401,
                    'message'       => 'Error validation',
                    'data'           => $FVE
                ], Rest_Controller::HTTP_UNAUTHORIZED);
            } else {

                $pdata = array(
                        'amount'           => $this->sanitizeInput($datapost['amount']),
                        'reference_number' => $this->sanitizeInput($datapost['reference_number']),
                        'acount_no'        => $this->sanitizeInput($datapost['acount_no']),
 
                );

            //    $chk_merchant_token = $this->modelrepo->chk_token();

            //    if($chk_merchant_token == false){
            $token =    $this->DwdApiService->generate_token();

                // }else{

                // }

              $resp=  $token;
          
            }
        }
        if ($AVR) {

            $this->response($resp, Rest_Controller::HTTP_CREATED);
        } else {

            $this->response($resp, Rest_Controller::HTTP_UNAUTHORIZED);
        }
    }


    
///////////////////////////





}
	