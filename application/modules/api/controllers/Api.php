<?php
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';

require APPPATH . 'libraries/Format.php';
require APPPATH . 'libraries/Authorization_Token.php';

class Api extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Manila');
        $this->load->model('api_model', 'modelrepo');
        

        $this->authorization_token = new Authorization_Token();
    }


        public function error()
    {
                $this->response([
                    'status' => false,
                    "status_code"=> 404,
                    'message' => '404 Page Not Found',
                    
                ], Rest_Controller::HTTP_NOT_FOUND);

    }

    public function index_get()
    {   
        $bytes = random_bytes(16);

        $data['status'] = false;
        $data['status_code'] = 403;
        // $data['message'] =bin2hex($bytes);
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




    public function generate_token_get()
    {
        $AVR = true;

        $today = date('Y-m-d H:i:s');

        $head = checkHeader($this);

        if ($head['status'] == false) {
                 $this->response($head, Rest_Controller::HTTP_FORBIDDEN);
        } else {

            $token_data['Access'] = "true";
            $token_data['account_id'] = $head['company_id'];
            $token_data['access'] = $head['api_name'];
             $this->modelrepo->exp_all($head['company_id']);
            $tokenData = $this->authorization_token->generateToken($token_data);

            $insert_token['token']=$tokenData['token'];
            $insert_token['conpany_id']= $head['company_id'];
            $insert_token['company_name']= $head['api_name'];
            $insert_token['date_create']=date("Y-m-d H:i:s",$tokenData['iat']);
            $insert_token['date_exp']=date("Y-m-d H:i:s",$tokenData['exp']);
            $insert_token['status']='ACTIVE';


            $this->modelrepo->inser_token($insert_token);

            $resp = array();

            $resp['status'] = true;
            $resp['status_code'] = 201;
            $resp['message'] = "create";
            
            $resp['data']['token'] = $tokenData['token'];
            $resp['data']['exp']=date("Y-m-d H:i:s",$tokenData['exp']);
            
        }
        if ($AVR) {

            $this->response($resp, Rest_Controller::HTTP_CREATED);
        } else {

            $this->response($resp, Rest_Controller::HTTP_UNAUTHORIZED);
        }
    }

    public function verify_post()
    {
        $headers = $this->input->request_headers();
        if (isset($headers['Authorization'])) {

            $decodedToken = $this->authorization_token->validateToken($headers);

            echo json_encode($decodedToken);
        } else {

            $resp['status'] = false;
            $resp['message'] = "Authentication failed";

            $this->response($resp, Rest_Controller::HTTP_UNAUTHORIZED);
        }
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
            $result['data'] = $validateToken;
            $this->response($result, Rest_Controller::HTTP_FORBIDDEN);
        } elseif ($head['status'] == false) {

            $result['status'] = false;
            $result['status_code'] = 403;
            $result['data'] = $head;
        } else {

            $result['status'] = true;
            $result['status_code'] = 201;
            $result['data'] = $head;
            $this->response($result, Rest_Controller::HTTP_FORBIDDEN);
        }

        return $result;
    }

    private function sanitizeInput($input)
    {
        return preg_replace("/[^a-zA-Z0-9\s_,.-]/", "", trim($input));
    }

    public function create_api_access_post()
    {
        $today = date('Y-m-d H:i:s');
        $AVR = true;
        $chkAcess = $this->vallidate_access();

        if ($chkAcess['status'] == false) {

           $this->response($chkAcess, Rest_Controller::HTTP_FORBIDDEN);
        } else {

            $this->form_validation->set_rules('sess_id', 'sess_id', 'trim|required');

            $this->form_validation->set_rules('client_name', 'client_name', 'trim|required|min_length[5]|max_length[40]');

            $this->form_validation->set_rules('company_name', 'company_name', 'trim|required|min_length[3]');

            $this->form_validation->set_rules('image_name', 'image_name', 'trim|required|min_length[3]');
			
            $this->form_validation->set_rules('merch_id', 'merch_id', 'trim|required');

            $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

            switch ($contentType) {
                case 'application/json':
                    $json = file_get_contents('php://input');
                    $_POST = json_decode($json, true);
                    $datapost = $_POST;
                    break;
                default:
                    $datapost = array(
                        'sess_id' => $this->input->post('sess_id', true),
                        'client_name' => $this->input->post('client_name', true),
                        'company_name' => $this->input->post('company_name', true),
                        'image_name' => $this->input->post('image_name', true),
						 'merch_id' => $this->input->post('merch_id', true)
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
                    'sess_id' => $this->sanitizeInput($datapost['sess_id']),
                    'client_name' => $this->sanitizeInput($datapost['client_name']),
                    'company_name' => $this->sanitizeInput($datapost['company_name']),
                    'image_name' => $this->sanitizeInput($datapost['image_name']),
					 'merch_id' => $this->sanitizeInput($datapost['merch_id'])
                );
                $validateSession = $this->modelrepo->validate_session($pdata);
                if ($validateSession == false) {

                    $AVR = false;
                    $resp['status'] = false;
                    $resp['status_code'] = 1001;
                    $resp['message'] = "Session denied";
                } else {

                    // /chk company name if exist
                    $chkCompanyName = $this->modelrepo->chk_company_name($pdata);

                    if ($chkCompanyName) {
                        $AVR = false;
                        $resp['status'] = false;
                        $resp['status_code'] = 401;
                        $resp['message'] = "Company name already exist";
                    } else {

                        // /chk client name
                        $chkClientName = $this->modelrepo->chk_api_name($pdata['client_name']);
                        if ($chkClientName) {
                            $AVR = false;
                            $resp['status'] = false;
                            $resp['status_code'] = 401;
                            $resp['message'] = "Client name already exist";
                        } else {
                            $compantData["company_name"] = $pdata['company_name'];
                            $compantData["ci_min_amount"] = "100";
                            $compantData["ci_max_amount"] = "50000";
                            $compantData["ci_current_balance"] = "0";
                            $compantData["ci_previous_amount"] = "0";
                            $compantData["ci_rate"] = 1.8;
                            $compantData["co_current_balance"] = "0";
                            $compantData["co_rate"] = 1.8;
                            $compantData["co_min_amount"] = "10";
                            $compantData["co_max_amount"] = "100";
                            $compantData["co_previous_amount"] = "0";
                            $compantData["status"] = "1";
                            $compantData["created_date"] = $today;
                            $compantData["image"] = $pdata['image_name'];
							    $compantData["mID"] = $pdata['merch_id'];
                            $doInsertCompanyTableId = $this->modelrepo->insert_tble_campany($compantData);
                            if ($doInsertCompanyTableId) {

                                $createApiUser = $this->modelrepo->insert_api_user($pdata, $doInsertCompanyTableId);
                                if ($createApiUser) {
                                    $resp['status'] = true;
                                    $resp['message'] = "Success";
                                    $resp['data'] = $createApiUser;
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($AVR) {

            $this->response($resp, Rest_Controller::HTTP_CREATED);
        } else {

            $this->response($resp, Rest_Controller::HTTP_UNAUTHORIZED);
        }
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

 

    public function client_change_data_post()
    {
        $today = date('Y-m-d H:i:s');
        $AVR = true;
        $chkAcess = $this->vallidate_access();

        if ($chkAcess['status'] == false) {

            $AVR = $chkAcess['status'];
            $resp = $chkAcess['data'];
        } else {
            $this->form_validation->set_rules('client_id', 'client_id', 'trim|required');
            $this->form_validation->set_rules('user_id', 'user_id', 'trim|required');
            $this->form_validation->set_rules('sess_id', 'sess_id', 'trim|required');
            $this->form_validation->set_rules('password', 'password', 'trim|required|min_length[3]');
            $this->form_validation->set_rules('company_name', 'company_name', 'trim|required|min_length[3]');
            $this->form_validation->set_rules('image_name', 'image_name', 'trim|required|min_length[3]');
            
            $this->form_validation->set_rules('cashin_min_amount', 'cashin_min_amount', 'trim|required');
            $this->form_validation->set_rules('cashin_max_amount', 'cashin_max_amount', 'trim|required');
           
            $this->form_validation->set_rules('cashin_rate', 'cashin_rate', 'trim|required');
            $this->form_validation->set_rules('cashout_rate', 'cashout_rate', 'trim|required');
            $this->form_validation->set_rules('cashout_min_amount', 'cashout_min_amount', 'trim|required');
            $this->form_validation->set_rules('cashout_max_amount', 'cashout_max_amount', 'trim|required');
        

           
      

            $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

            switch ($contentType) {
                case 'application/json':
                    $json = file_get_contents('php://input');
                    $_POST = json_decode($json, true);
                    $datapost = $_POST;
                    break;
                default:
                    $datapost = array(
                        'client_id' => $this->input->post('client_id', true),
                        'user_id' => $this->input->post('user_id', true),
                        'sess_id' => $this->input->post('sess_id', true),
                        'password' => $this->input->post('password', true),
                        'company_name' => $this->input->post('company_name', true),
                        'image_name' => $this->input->post('image_name', true),
                        'cashin_min_amount' => $this->input->post('cashin_min_amount', true),
                        'cashin_max_amount' => $this->input->post('cashin_max_amount', true),
                        'cashin_rate' => $this->input->post('cashin_rate', true),
                        'cashout_rate' => $this->input->post('cashout_rate', true),
                        'cashout_min_amount' => $this->input->post('cashout_min_amount', true),
                        'cashout_max_amount' => $this->input->post('cashout_max_amount', true)
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
                    'company_id' => $this->sanitizeInput($datapost['client_id']),
                    'user_id' => $this->sanitizeInput($datapost['user_id']),
                    'sess_id' => $this->sanitizeInput($datapost['sess_id']),
                    'password' => $this->sanitizeInput($datapost['password']),
                    'company_name' => $this->sanitizeInput($datapost['company_name']),
                    'image' => $this->sanitizeInput($datapost['image_name']),
                    'ci_min_amount' => $this->sanitizeInput($datapost['cashin_min_amount']),
                    'ci_max_amount' => $this->sanitizeInput($datapost['cashin_max_amount']),
                    'ci_rate' => $this->sanitizeInput($datapost['cashin_rate']),
                    'co_rate' => $this->sanitizeInput($datapost['cashout_rate']),
                    'co_min_amount' => $this->sanitizeInput($datapost['cashout_min_amount']),
                    'co_max_amount' => $this->sanitizeInput($datapost['cashout_max_amount'])
                );
                $validateSession = $this->modelrepo->validate_session($pdata);
                if ($validateSession == false) {

                    $AVR = false;
                    $resp['status'] = false;
                    $resp['status_code'] = 1001;
                    $resp['message'] = "Session denied";
                } else {
                  $getUserAccess  =   $this->modelrepo->validate_user_access($pdata);

                  if($getUserAccess ==false ){
                    $AVR = false;
                    $resp['status'] = false;
                    $resp['status_code'] = 401;
                    $resp['message'] = "user_id not exist";
                  }else{
                     if($getUserAccess['password'] !=md5( $pdata['password'])){
                        
                        $AVR = false;
                        $resp['status'] = false;
                        $resp['status_code'] = 401;
                        $resp['message'] = "Incorrect password";
                   
                    
                    }else{

                        $clientUpdate['company_name']= $pdata ['company_name'];
                        $clientUpdate['image']= $pdata ['image'];
                        $clientUpdate['ci_min_amount']= $pdata['ci_min_amount'] ;
                        $clientUpdate['ci_max_amount']= $pdata['ci_max_amount'] ;
                        $clientUpdate['ci_rate']= $pdata['ci_rate'] ;
                        $clientUpdate['co_rate']= $pdata['co_rate'] ;
                        $clientUpdate['co_min_amount']= $pdata['co_min_amount'] ;
                        $clientUpdate['co_max_amount']= $pdata['co_max_amount'] ;

                      $doupdateClientData=   $this->modelrepo->do_client_update_data($clientUpdate, $pdata ['company_id']);

                      if(  $doupdateClientData){
                        $resp['status'] = true;
                        $resp['status_code'] = 200;
                        $resp['message'] = "Account updated successfully!";
                        // $resp['data'] = $getUserAccess;
                      }else{
                        $AVR = false;
                        $resp['status'] = false;
                        $resp['status_code'] = 401;
                        $resp['message'] = "failed to update";
                      
                      }
                       
                        
                    }
                  

                    


                    
                    
                  }
                    

                    // /chk company name if exist
                    // $chkCompanyName = $this->modelrepo->chk_company_name($pdata);

                    // if ($chkCompanyName) {
                    //     $AVR = false;
                    //     $resp['status'] = false;
                    //     $resp['message'] = "Company name already exist";
                    // } else {

                   
                    // }
                }
            }
        }
        if ($AVR) {

            $this->response($resp, Rest_Controller::HTTP_CREATED);
        } else {

            $this->response($resp, Rest_Controller::HTTP_UNAUTHORIZED);
        }
    }

    public function user_change_data_post()
    {
        $today = date('Y-m-d H:i:s');
        $AVR = true;
        $chkAcess = $this->vallidate_access();

        if ($chkAcess['status'] == false) {

            $AVR = $chkAcess['status'];
            $resp = $chkAcess['data'];
        } else {        
               
                $this->form_validation->set_rules('user_id', 'user_id', 'trim|required');
                $this->form_validation->set_rules('sess_id', 'sess_id', 'trim|required');
                $this->form_validation->set_rules('password', 'password', 'trim|required');
                
                $this->form_validation->set_rules('account_password', 'account_password', 'trim|required');
                $this->form_validation->set_rules('account_name', 'account_name', 'trim|required');
                $this->form_validation->set_rules('account_id', 'account_id', 'trim|required');
                $this->form_validation->set_rules('name', 'name', 'trim|required');
                $this->form_validation->set_rules('mobile_number', 'mobile_number', 'trim|required|numeric');
                $this->form_validation->set_rules('account_email', 'account_email', 'trim|required|valid_email');

                $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

                    switch ($contentType) {
                        case 'application/json':
                            $json = file_get_contents('php://input');
                            $_POST = json_decode($json, true);
                            $datapost = $_POST;
                            break;
                        default:
                    $datapost = array(
                    
                        'user_id' => $this->input->post('user_id', true),
                        'sess_id' => $this->input->post('sess_id', true),
                        'password' => $this->input->post('password', true),
                        
                        'account_password' => $this->input->post('account_password', true),
                        'account_name' => $this->input->post('account_name', true),
                        'account_id' => $this->input->post('account_id', true),
                        'name' => $this->input->post('name', true),
                        'mobile_number' => $this->input->post('mobile_number', true),
                        'account_email' => $this->input->post('account_email', true),
                   
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
                  
                    'user_id' => $this->sanitizeInput($datapost['user_id']), //user on login
                    'sess_id' => $this->sanitizeInput($datapost['sess_id']),
                    'password' => $this->sanitizeInput($datapost['password']),
                    
                    'account_password' => $this->sanitizeInput($datapost['account_password']),
                    'account_name' => $this->sanitizeInput($datapost['account_name']),
                    'account_id' => $this->sanitizeInput($datapost['account_id']), //id on user table
                    'name' => $this->sanitizeInput($datapost['name']),
                    'mobile_number' => $this->sanitizeInput($datapost['mobile_number']),
                    'email' => $this->sanitizeInput($datapost['account_email'])
                  
                );




                
                $validateSession = $this->modelrepo->validate_session($pdata);
                if ($validateSession == false) {

                    $AVR = false;
                    $resp['status'] = false;
                    $resp['status_code'] = 1001;
                    $resp['message'] = "Session denied";
                }elseif($pdata['user_id']==$pdata['account_id']){
                    
                    $AVR = false;
                    $resp['status'] = false;
                    $resp['message'] = "you are not allow to update this data" ;
                    
                }else {
                    $getUserAccess  =   $this->modelrepo->validate_user_access($pdata);

                    if($getUserAccess ==false ){
                      $AVR = false;
                      $resp['status'] = false;
                      $resp['status_code'] = 401;
                      $resp['message'] = "user_id not exist";
                    }else{
                       if($getUserAccess['password'] !=md5( $pdata['password'])){
                          
                          $AVR = false;
                          $resp['status'] = false;
                          $resp['status_code'] = 401;
                          $resp['message'] = "Incorrect password";
                        //   $resp['data']=$pdata;

                      }else{
                           
                        $userUpdate['password']= md5($pdata['account_password']) ;
                        $userUpdate['username']= $pdata['account_name'] ;
                        $userUpdate['user_id']= $pdata['account_id'] ;
                        $userUpdate['name']= $pdata['name'] ;
                        $userUpdate['mobile_number']= $pdata['mobile_number'] ;
                        $userUpdate['email']= $pdata['email'] ;
                        // $userUpdate['date_created']= $today ;

                      $doupdateClientData=   $this->modelrepo->do_user_update_data($userUpdate, $pdata ['account_id']);

                      if(  $doupdateClientData){
                        $resp['status'] = true;
                        $resp['status_code'] = 401;
                        $resp['message'] = 'Account updated successfully!';
                        // $resp['data'] = $getUserAccess;
                      }else{
                        $AVR = false;
                        $resp['status'] = false;
                        $resp['status_code'] = 401;
                        $resp['message'] = "failed to update";
                     
                      
                      }

                        
                        
                      }
                    
                    }
                    
                }
            }

                
        }
        if ($AVR) {

            $this->response($resp, Rest_Controller::HTTP_CREATED);
        } else {

            $this->response($resp, Rest_Controller::HTTP_UNAUTHORIZED);
        }
        
    }
  


    
///////////////////////////


	public function api_log_details_post()
	{

     $today = date('Y-m-d H:i:s');
        $AVR = true;
        $chkAcess = $this->vallidate_access();

        if ($chkAcess['status'] == false) {

            $AVR = $chkAcess['status'];
            $resp = $chkAcess['data'];
        } else {

            $this->form_validation->set_rules('sess_id', 'sess_id', 'trim|required');
			$this->form_validation->set_rules('id_from', 'id_from', 'trim|required');
			$this->form_validation->set_rules('id_to', 'id_to', 'trim|required');


			

            $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

            switch ($contentType) {
                case 'application/json':
                    $json = file_get_contents('php://input');
                    $_POST = json_decode($json, true);
                    $datapost = $_POST;
                    break;
                default:
                    $datapost = array(
                        'sess_id' => $this->input->post('sess_id', true),
						'id_from' => $this->input->post('id_from', true),
						'id_to' => $this->input->post('id_to', true)
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
                    'sess_id' => $this->sanitizeInput($datapost['sess_id']),
					'id_from' => $this->sanitizeInput($datapost['id_from']),
					'id_to' => $this->sanitizeInput($datapost['id_to'])
                );
                $validateSession = $this->modelrepo->validate_session($pdata);
                if ($validateSession == false) {

                    $AVR = false;
                    $resp['status'] = false;
                    $resp['status_code'] = 1001;
                    $resp['message'] = "Session denied";
                } else {


					if( $pdata['id_from']>$pdata['id_to']){


						    $AVR = false;
							$resp['status'] = false;
                            $resp['status_code'] = 401;
							$resp['message'] = "fetch data  invalid";

					}else{
					$apiLogData= $this->modelrepo->api_log_details($pdata);

                    $resp['status']      = true;
                    $resp['status_code'] = 200;
                    $resp['message']     = "success";
                    $resp['data']        = $apiLogData;
					}

                
                }
            }
        }
        if ($AVR) {

            $this->response($resp, Rest_Controller::HTTP_OK);
        } else {

            $this->response($resp, Rest_Controller::HTTP_UNAUTHORIZED);
        }


	}



	
	public function user_log_details_post()
	{

       $today = date('Y-m-d H:i:s');
        $AVR = true;
        $chkAcess = $this->vallidate_access();

        if ($chkAcess['status'] == false) {

            $AVR = $chkAcess['status'];
            $resp = $chkAcess['data'];
        } else {

            $this->form_validation->set_rules('sess_id', 'sess_id', 'trim|required');
			$this->form_validation->set_rules('id_from', 'id_from', 'trim|required');
			$this->form_validation->set_rules('id_to', 'id_to', 'trim|required');


			

            $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

            switch ($contentType) {
                case 'application/json':
                    $json = file_get_contents('php://input');
                    $_POST = json_decode($json, true);
                    $datapost = $_POST;
                    break;
                default:
                    $datapost = array(
                        'sess_id' => $this->input->post('sess_id', true),
						'id_from' => $this->input->post('id_from', true),
						'id_to' => $this->input->post('id_to', true)
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
                    'sess_id' => $this->sanitizeInput($datapost['sess_id']),
					'id_from' => $this->sanitizeInput($datapost['id_from']),
					'id_to' => $this->sanitizeInput($datapost['id_to'])
                );
                $validateSession = $this->modelrepo->validate_session($pdata);
                if ($validateSession == false) {

                    $AVR = false;
                    $resp['status'] = false;
                    $resp['status_code'] = 1001;
                    $resp['message'] = "Session denied";
                } else {


					if( $pdata['id_from']>$pdata['id_to']){


						    $AVR = false;
							$resp['status'] = false;
                            $resp['status_code'] = 401;
							$resp['message'] = "fetch data  invalid";

					}else{
					$userData= $this->modelrepo->user_log_details($pdata);

                    $resp['status'] = true;
                    $resp['status_code'] = 200;
                    $resp['message'] = "success";
                    $resp['data'] = $userData;
					}

                
                }
            }
        }
        if ($AVR) {

            $this->response($resp, Rest_Controller::HTTP_OK);
        } else {

            $this->response($resp, Rest_Controller::HTTP_UNAUTHORIZED);
        }





	}
}