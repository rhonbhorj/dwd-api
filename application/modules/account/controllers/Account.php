<?php
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Authorization_Token.php';
require APPPATH . 'libraries/Format.php';

class Account extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Manila');
        $this->load->model('account_model', 'modelrepo');
        $this->load->helper('header_helper');
        $this->authorization_token = new Authorization_Token();
    }

    public function index_get()
    {
        $data['status'] = false;
        $data['message'] = 'Forbidden';

        $this->response($data, Rest_Controller::HTTP_FORBIDDEN);
    }

    public function index_post()
    {
        $data['status'] = false;
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
            $result['data'] = $validateToken;
        } elseif ($head['status'] == false) {

            $result['status'] = false;
            $result['data'] = $head;
        } else {

            $result['status'] = true;
            $result['data'] = $head;
        }

        return $result;
    }

   


   ///BSP account registration
    public function register_post()
    {
         $AVR = true;

        $today = date('Y-m-d H:i:s');

        $head = checkHeader($this);

        if ($head['status'] == false) {

            $AVR = false;

            $resp = $head;
        } else {

            $this->form_validation->set_rules('full_name', 'full_name', 'trim|required');

            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');

            $this->form_validation->set_rules('phone', 'Phone', 'trim|required|numeric');

            $this->form_validation->set_rules('member_type', 'member_type', 'trim|required');

            $this->form_validation->set_rules('member_id', 'member_id', 'trim|required');
         
    
          
            $this->form_validation->set_rules('username', 'username', 'trim|required');

            $this->form_validation->set_rules('password', 'password', 'trim|required');

			$this->form_validation->set_rules('ip', 'ip', 'trim|required');

			$this->form_validation->set_rules('user_agent', 'user_agent', 'trim|required');

            $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

            switch ($contentType) {
                case 'application/json':

                    $json = file_get_contents('php://input');

                    $_POST = json_decode($json, true);

                    $datapost = $_POST;

                    break;
                default:
                    $datapost = array(


                        'district' => $this->input->post('district', true),

                        'district_unit' => $this->input->post('district_unit', true),

                        'school' => $this->input->post('school', true),

                        'registration_number' => $this->input->post('full_name', true),
                    

                        'full_name' => $this->input->post('full_name', true),

                        'email' => $this->input->post('email', true),

                        'phone' => $this->input->post('phone', true),

                        'member_id' => $this->input->post('member_id', true),

                        'member_type' => $this->input->post('member_type', true),

                        'council' => $this->input->post('council', true),

                        'scout_type' => $this->input->post('scout_type', true),


                        'username' => $this->input->post('username', true),

                        'password' => $this->input->post('password', true),

                        'ip' => $this->input->post('ip', true),
                        
                        'user_agent' => $this->input->post('user_agent', true)
                    
                    );
            }

            if ($this->form_validation->run() == FALSE) {

                $FVE = $this->form_validation->error_array();

                $this->response([

                    'status' => false,
                    'message' => 'Error validation',
                    'data' => $FVE

                ], Rest_Controller::HTTP_UNAUTHORIZED);
            } else {

                $pdata['district'] = isset($datapost['district']) ?strip_tags(trim($datapost['district'])):"";

                $pdata['district_unit'] =  isset($datapost['district_unit']) ? strip_tags(trim($datapost['district_unit'])):"";
                
                $pdata['school'] = isset($datapost['school']) ? strip_tags(trim($datapost['school'])):"";

                $pdata['registration_number'] = isset($datapost['registration_number']) ?strip_tags(trim($datapost['registration_number'])):"";

                $pdata['council'] =  isset($datapost['district_unit']) ? strip_tags(trim($datapost['district_unit'])):"";

                $pdata['full_name'] = strip_tags(trim($datapost['full_name']));

                $pdata['email'] = strip_tags(trim($datapost['email']));

                $pdata['mobile_number'] = strip_tags(trim($datapost['phone']));

                $pdata['member_id'] = strip_tags(trim($datapost['member_id']));
                
                $pdata['scout_type'] = strip_tags(trim($datapost['scout_type']));
               
                $pdata['username'] = strip_tags(trim($datapost['username']));

                $pdata['password'] =md5(strip_tags(trim($datapost['password'])));

				$pdata['ip'] = strip_tags(trim($datapost['ip']));
                
				$pdata['user_agent'] = strip_tags(trim($datapost['user_agent']));

                        $result = $this->modelrepo->insert_users($pdata);

                        if ($result['status']) {

                            $resp['status']=true;
                            $resp['status_code']=201;
                            $resp['message']="Created";
                      
      
                            $this->response($resp, Rest_Controller::HTTP_CREATED);

                        } else {
                                $msg = 'Duplicate entry detected';

                                if (strpos($result['error']['message'], 'email') !== false) {

                                    $msg = 'Email already exists';

                                } elseif (strpos($result['error']['message'], 'full_name') !== false) {

                                    $msg = 'name already exists.';

                                }elseif (strpos($result['error']['message'], 'username') !== false) {

                                    $msg = 'Username already exists';

                                } elseif (strpos($result['error']['message'], 'member_id') !== false) {

                                    $msg = 'Member ID already exists';

                                } elseif (strpos($result['error']['message'], 'mobile_number') !== false) {
                                    
                                    $msg = 'Mobile number already exists';
                                }
                                
                                    $result= [
                                        'status'      => false,
                                        'status_code' => 401,
                                        'message'     => $msg,
                                        "data"        => $result    
                                    ];

                                $this->response($result, Rest_Controller::HTTP_UNAUTHORIZED);
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