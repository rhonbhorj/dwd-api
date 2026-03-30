<?php
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';

require APPPATH . 'libraries/Format.php';
require APPPATH . 'libraries/Authorization_Token.php';
require_once( APPPATH . 'services/DwdApiService.php' );

class Postback extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Manila');
        $this->load->model('Postback_model', 'modelrepo');
         $this->load->helper('matrix');
         
      
      
        $this->DwdApiService = new DwdApiService();

        $this->authorization_token = new Authorization_Token();
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

      private function sanitizeInput($input)
    {
        return preg_replace("/[^a-zA-Z0-9\s_,.-]/", "", trim($input));
    }



                        // $reference_number['reference_number']   = $pdata['reference_number'];
                        // $txn_reference['txn_reference']
                        // $payment_ref_no['payment_ref_no']
                        // $dwd_reference_num['dwd_reference_num']
                        // $amount['amount']
                        // $fee['fee']
                        // $total_amount['total_amount']
                        // $account_no['account_no']               = $get_billing_query['response']['Account_No']
                        // $account_name['account_name']           = $get_billing_query['response']['Account_Name']
                        // $payment_date['payment_date']
                        // $vaid_via['vaid_via']
                        // $paid_by['paid_by']
                        // $total_amount_due['total_amount_due']   = $get_billing_query['response']['Total_Amount_Due']
                        // $amount_after_due['amount_after_due']   = $get_billing_query['response']['Amount_After_Due']
                        // $due_date['due_date']                   = $get_billing_query['response']['Due_Date']
                        // $billing_month['billing_month']         = $get_billing_query['response']['Billing_Month']
                        // $reference_num['reference_num']         = $get_billing_query['response']['ReferenceNum']
    
    public function index_post($ref="0")
    {

        $dateToday=date('Y-m-d\TH:i:s.vP');

   
        $ref_number = $_GET[ 'txref' ];

        $this->output->set_content_type( 'application/json' );

        $data = json_decode( file_get_contents( 'php://input' ), true );

        $data_exist = $this->modelrepo->find_data( $ref_number );  ///tbl_callback

        $call_back_data[ 'reference_number' ] = $ref_number;

        $call_back_data[ 'callback_data' ] = json_encode( $data );

        $call_back_data[ 'date' ] = date( 'Y-m-d H:i:s' );

        $call_back_data[ 'TxId' ] = $data[ 'TxId' ];

        $call_back_data[ 'referenceNumber' ] = $data[ 'referenceNumber' ];

        $call_back_data[ 'callback_status' ] = $data[ 'status' ];   
//   var_dump($data_exist);
        // Get raw POST body
        // $rawBody = file_get_contents('php://input');
        // $encData = json_encode($rawBody);
  
        // $response['callback_data'] =  $data;
    
          $test=false;

        // if ( $data_exist !=false) {
              if ( $test !=false) {
     
            $this->modelrepo->callback_logs( $call_back_data );
            $this->response( [
                'messege' => 'Failed',
                'error' => 'invalid postback'
            ], Rest_Controller::HTTP_UNAUTHORIZED );
        } else {
            $callback_logs_id = $this->modelrepo->callback_logs($call_back_data);



            $TransData = $this->modelrepo->chk_reference_number($call_back_data);

            if($TransData){
                // this client response
           
                $update[ 'status' ] = $this->status_get( $data[ 'status' ] );
            
                $update[ 'merchant_ref' ] = $call_back_data[ 'TxId' ];
                $update[ 'modified_at' ]  = date( 'Y-m-d H:i:s' );
                $trans_updated = $this->modelrepo->update_tbl_transaction_data( $update, $ref_number );

                if( $update[ 'status' ]=='SUCCESS'){

                    $callbaUpdatData['client_response'] = json_encode( $TransData );

                    $this->modelrepo->update_callback($callbaUpdatData,$callback_logs_id);  
                    
                $pdata['reference_number'] = $TransData['txn_reference'];
                 
                $ngsi_txn_data = get_txn_data( $pdata );
                $matrix_datap["Account_No"]     =  $TransData['account_no']; 
                $matrix_datap["Amount_Paid"]    =  $TransData['amount'];
                $matrix_datap["Payment_Date"]   =  $update[ 'modified_at' ]; 
                $matrix_datap["Paid_Via" ]      =  'N';
                $matrix_datap["Paid_By"]        =  'N'; 
                $matrix_datap["PaymentRefNo"]   =  $ngsi_txn_data['response']['data']['payment-reference'];
                $matrix_datap["ReferenceNum" ]  =  $TransData['dwd_reference_num']; 
                $matrix_datap["Billing_Month"]  =  $TransData['billing_month']; 
                $matrix_datap["trace_id"]       =  $TransData['txn_reference'];
                        
                
                       
                 $generated_token  =     $this->get_token();
                $this->DwdApiService->upload_external_ap1( $generated_token,$matrix_datap ) ;
                
                }

                    $jresponse["message"]  =    "SUCCESS";
        
                            

                $this->response($jresponse , Rest_Controller::HTTP_OK);

            }else{

                $this->response( [
                            'messege' => 'Failed',
                            'error' => 'data not found'
                        ], Rest_Controller::HTTP_UNAUTHORIZED );
            }






        }
    }




   function status_get( $type )
    {
        $caffeine = '';
        $map = [
            '1' => 'STARTED ',
            '2' => 'PENDING',
            '3' => 'FAILED',
            '4' => 'SUCCESS'
        ];

        $caffeine = $map[ $type ];
        return $caffeine;
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