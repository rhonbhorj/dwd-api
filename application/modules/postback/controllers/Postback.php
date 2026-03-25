<?php
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';

require APPPATH . 'libraries/Format.php';
require APPPATH . 'libraries/Authorization_Token.php';

class Postback extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Manila');
        $this->load->model('Postback_model', 'modelrepo');
        $this->load->model('scout/Scout_model');
    

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
        $data['message'] = 'Forbidden.';
        $this->response($data, Rest_Controller::HTTP_FORBIDDEN);
    }

      private function sanitizeInput($input)
    {
        return preg_replace("/[^a-zA-Z0-9\s_,.-]/", "", trim($input));
    }



    
    public function data_post($ref="0")
    {

        $dateToday=date('Y-m-d\TH:i:s.vP');

   
        $ref_number = $_GET[ 'refdata' ];

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
    
        //   $test=false;
        if ( $data_exist !=false) {
        // if ($test) {
            $this->modelrepo->callback_logs( $call_back_data );
            $this->response( [
                'messege' => 'Failed',
                'error' => 'invalid postback'
            ], Rest_Controller::HTTP_UNAUTHORIZED );
        } else {
                 $this->modelrepo->callback_logs($call_back_data);



            $TransData = $this->modelrepo->chk_reference_number($call_back_data);

            if($TransData){
                // this client response
           
                $transData[ 'status' ] = $this->status_get( $data[ 'status' ] );
            
                $transData[ 'merchant_ref' ] = $call_back_data[ 'TxId' ];
                $transData[ 'modified_at' ] = date( 'Y-m-d H:i:s' );
                $trans_updated = $this->modelrepo->update_tbl_transaction_data( $transData, $ref_number );

                
          
                $jresponse["message"]  = "Success";
        
                            

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



}