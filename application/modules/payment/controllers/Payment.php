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
        return preg_replace("/[^a-zA-Z0-9\s_,.@-]/", "", trim($input));
    }

 


 

    public function create_qr_post()
    {
        $today    = date('Y-m-d H:i:s');
        $AVR      = true;
        $chkAcess = $this->vallidate_access();

        if ($chkAcess['status'] == false) {
        //    $AVR      = false;
        
            $this->response($chkAcess['data'], Rest_Controller::HTTP_UNAUTHORIZED);
        } else {
            $this->form_validation->set_rules('amount', 'amount', 'trim|required');
            $this->form_validation->set_rules('reference_number', 'reference_number', 'trim|required');
            $this->form_validation->set_rules('acount_no', 'acount_no', 'trim|required');
            $this->form_validation->set_rules('phone_number', 'phone_number', 'trim|required');
            $this->form_validation->set_rules('email', 'email', 'trim|required|valid_email');

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
                        'acount_no'        => $this->input->post('acount_no', true),
                        'phone_number'     => $this->input->post('phone_number', true),
                        'email'            => $this->input->post('email', true)

                    );
            }

             $ins_data['params'] = json_encode($datapost);

            $ins_data['request_at'] = $today;

            $ins_data['method'] = $_SERVER['REQUEST_METHOD'];

            $ins_data['uri'] = $this->uri->uri_string();

            $apiLogId = $this->modelrepo->do_apilogs($ins_data);

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
                        'acount_no'        => $this->sanitizeInput($datapost['acount_no']),
                        'phone_number'     => $this->sanitizeInput($datapost['phone_number']),
                        'email'            => $this->sanitizeInput($datapost['email'])
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
                 
                $fee               = '20';

                if( $get_billing_query['status_code'] == 200 ){

                    if(date('Y-m-d') <=$get_billing_query['response']['Due_Date']){
                        $amount_to_transact = $get_billing_query['response']['Total_Amount_Due'];
                        
                    }else{

                        $amount_to_transact = $get_billing_query['response']['Amount_After_Due'];

                    }

                    if($pdata['amount'] != $amount_to_transact){

                        $this->response([

                        'status'        => false,
                        'status_code'   => 401,
                        'message'       => 'Invalid Amount to transact',
                        'amount_to_paid' => $amount_to_transact
                    
                        ], Rest_Controller::HTTP_UNAUTHORIZED);
                    }

                    
                    $request_data['reference_number']   = $pdata['reference_number'];
                      
                    $request_data['txn_reference']      = date('Y').$pdata['reference_number'];
                  
                    $request_data['dwd_reference_num']  = $get_billing_query['response']['ReferenceNum'];

                    $request_data['amount']             = $pdata['amount'];

                    $request_data['fee']                = $fee;

                    $request_data['total_amount']       = $pdata['amount'] + $fee;

                    $request_data['account_no']         = $get_billing_query['response']['Account_No'];

                    $request_data['account_name']       = $get_billing_query['response']['Account_Name'];
                  
                    $request_data['total_amount_due']   = $get_billing_query['response']['Total_Amount_Due'];

                    $request_data['amount_after_due']   = $get_billing_query['response']['Amount_After_Due'];

                    $request_data['due_date']           = $get_billing_query['response']['Due_Date'];

                    $request_data['billing_month']      = $get_billing_query['response']['Billing_Month'];

                    $request_data['status']             = 'CREATED';

                    $request_data['email']              = $pdata['email'];

                    $request_data['mobile_number']      = $pdata['phone_number'];



                    $postBackData       = base_url() . 'postback/?txref=' . $request_data['txn_reference'];

                    $jayParsedAry = [
                        'endpoint'               => 'p2m-generateQR',
                        'reference_number'       => $request_data['txn_reference'],
                        'return_url'             => 'https://example.com/success',
                        'callback_url'           => $postBackData,

                        'merchant_details'       => [
                            'txn_amount'    => $request_data['total_amount'],
                            'method'        => 'dynamic',
                            'txn_type'      => 1,
                            'name'          => $get_billing_query['response']['Account_Name'],
                            'mobile_number' => $pdata["phone_number"]

                        ],
                        'email_confirmation'    =>  [
                            'email'=> $pdata['email'],
                            "auto"=>"off"
                        ],
                        "other_details" => [
                            [
                                "item"  => "billing_month",
                                "amount"=> $get_billing_query['response']['Billing_Month']
                            ],
                            [
                                "item"  => "Amount",
                                "amount"=> $request_data['amount']
                            ],
                            [
                                "item"  => "Fee",
                                "amount"=> $request_data['fee']
                            ]
                        ]
                    ];
                    $ngsi_resp = generate_qr_api( $jayParsedAry );

                    $update['status']       = $ngsi_resp['status_code'];
                    $update['response_at']  = date('Y-m-d H:i:s');
                    
                    if( $ngsi_resp['status_code'] == "201" ){
                        $request_data['ngsi_ref_no']      = $ngsi_resp['response']['data']['txn_ref'];

                        $insert_txn                 = $this->modelrepo->insert_request_txn($request_data);

                        $resp['status']            = true;
                        $resp['message']           = "Created Successfully!";
                        $resp['amount']            = $pdata['amount'];
                        $resp['fee']               = $request_data['fee'];
                        $resp['total_txn_amount']  = $pdata['amount'] +$request_data['fee'];
                        
                        $resp['reference_number']  = $request_data['reference_number'];
                        $resp['txn_reference']     = $request_data['txn_reference'];
                        $resp['create_at']         = $ngsi_resp["response"]['data']['create_at'];
                        $resp['raw_string']        = $ngsi_resp["response"]['data']['raw_string'];

                        

         
                        
                        

                    }elseif($ngsi_resp['status_code'] >= 500){
                             $update['api_response'] = json_encode($ngsi_resp['response']).json_encode($get_billing_query);
                            $updateapi= $this->modelrepo->doUpdateApilogs($update, $apiLogId);

                        $err_respponse['status'] = false;
                        $err_respponse['message'] = 'internal error';
                        $this->response($err_respponse, Rest_Controller::HTTP_INTERNAL_SERVER_ERROR);

                    }else{
                        $update['api_response'] = json_encode($ngsi_resp['response']);
                        $updateapi              = $this->modelrepo->doUpdateApilogs($update, $apiLogId);

                        $AVR = false;
                        $err_respponse['status'] = false;
                        $err_respponse['message'] = $ngsi_resp['response']['message'];
                         $this->response($err_respponse, Rest_Controller::HTTP_UNAUTHORIZED);

                    }
                   
                        $insert_txn = $this->modelrepo->insert_request_txn($request_data);
                 $update['api_response'] = json_encode($ngsi_resp['response']).json_encode($get_billing_query);
                  $updateapi= $this->modelrepo->doUpdateApilogs($update, $apiLogId);
                }else{
                    $update['api_response'] = json_encode($get_billing_query);
                  $updateapi= $this->modelrepo->doUpdateApilogs($update, $apiLogId);
                        $AVR=false;
                        $resp['status'] = false;
                        $resp['message'] = "No Balance or Bill Not Yet Uploaded.";
                       

                       

                }    
               
              

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
	