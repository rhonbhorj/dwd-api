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
        $this->load->model('Payment_model', 'modelrepo');
        
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

            $this->response($head, Rest_Controller::HTTP_FORBIDDEN);

        } else {
            $company_id = $head['company_id'];
            $token      = substr($headers['Authorization'],7);
            $chk_merchant_token = $this->modelrepo->chk_token($company_id,$token);
            if($chk_merchant_token){
                $result['status'] = true;
                $result['status_code'] = 200;
                $result['data'] = $head;
                $result['token'] = $headers;

            }else{
                $result['status'] = false;
                $result['status_code'] = 403;
                $result['message'] = "Invalid token";
                $this->response($result, Rest_Controller::HTTP_FORBIDDEN);

            }

        }

        return $result;
    }

    private function sanitizeInput($input)
    {
        return preg_replace("/[^a-zA-Z0-9\s_,.-]/", "", trim($input));
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
                    'data'          => $FVE
                ], Rest_Controller::HTTP_UNAUTHORIZED);
            } else {

                $pdata = array(
                        'amount'           => $this->sanitizeInput($datapost['amount']),
                        'reference_number' => $this->sanitizeInput($datapost['reference_number']),
                        'acount_no'        => $this->sanitizeInput($datapost['acount_no'])
                );
           
                $chk_reference_number = $this->modelrepo->chk_reference($pdata['reference_number']);

                if($chk_reference_number)
                {
                    $this->response([
                        'status'        => false,
                        'status_code'   => 401,
                        'message'       => 'reference_number already exist',
                    
                        ], Rest_Controller::HTTP_UNAUTHORIZED);

                }

                $mattrix_token = $this->get_token();
                    $data_to_send["Account_No"] = $pdata['acount_no'];
                $get_billing_query = $this->DwdApiService->billing_query_external_ap1($mattrix_token,$data_to_send);
                 
             

                $resp =  $get_billing_query;

              
          
            }
        }
        if ($AVR) {

            $this->response($resp, Rest_Controller::HTTP_CREATED);
        } else {

            $this->response($resp, Rest_Controller::HTTP_UNAUTHORIZED);
        }
    }

   function get_token()
   {
        $today = date("Y-m-d H:i:s");
        $get_last_token=$this->modelrepo->latest_token();

        if($get_last_token){

            $tobe_exp = $get_last_token['exp_date'];

            if( $today >= $tobe_exp){
                        
                return $this->new_token();
                    
            }else{
                
                return $get_last_token['access_token'];
            } 


        }else{
        
            return $this->new_token();
        }

   }
    
    function new_token()
    {   $today    = date('Y-m-d H:i:s');
        $dwd_token =    $this->DwdApiService->generate_token();

        if($dwd_token['status_code']==200){
            $matrix_data['access_token'] = $dwd_token['response']['access_token'];
            $matrix_data['token_type'] = $dwd_token['response']['token_type'];
            $matrix_data['created_date'] =$today;
            $matrix_data['exp_date'] =date("Y-m-d H:i:s", strtotime($today) + 3000);
            $matrix_data['expires_in'] =$dwd_token['response']['expires_in'];

            $this->modelrepo->insert_metrix_token($matrix_data);

            return $dwd_token['response']['access_token'];

        }elseif($dwd_token['status_code']>=500){
        
            $this->response([
            'status'        => false,
            'status_code'   => 500,
            'message'       => 'MatrixPay server issue.'
            ], Rest_Controller::HTTP_UNAUTHORIZED);

        }else{
            $this->response([
            'status'        => false,
            'status_code'   => 401,
            'message'       => 'error generate token'
            ], Rest_Controller::HTTP_UNAUTHORIZED);
        }


    }


}
	