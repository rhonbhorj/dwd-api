<?php
defined('BASEPATH') or exit('No direct script access allowed');

class DwdApiService
{
  protected $CI;
  protected $client_id;
  protected $client_secret;
  protected $grant_type;
  protected $dwdendpoint_base_url;

  public function __construct()
  {
    $this->CI = &get_instance();
    $this->CI->load->database();

    $this->client_id      = $_ENV['CLIENT_ID'];
    $this->client_secret  = $_ENV['CLIENT_SECRET'];
    $this->grant_type     = $_ENV['GRANT_TYPE'];
    $this->dwd_endpoint_base_url = $_ENV['DWD_ENDPOINT_BASE_URL'];
  }

  public function generate_token()
  {
    $ch = curl_init();

    curl_setopt_array($ch, array(
      CURLOPT_URL =>  $this->dwd_endpoint_base_url.'/auth/token',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'{
                              "client_id": "'.$this->client_id.'",
                              "client_secret": "'.$this->client_secret.'",
                              "grant_type": "'.$this->grant_type.'"
                            }',
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
      ),
    ));

      $response = curl_exec( $ch );
      $http_status_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
    
    curl_close( $ch );
    
            // expecting to be a json encoded response
            $resp[ 'response' ] =  json_decode( $response, true );
          
            $resp[ 'status_code' ] = $http_status_code;
    return $resp;
  }

  public function call_external_api($data)
  {
    $generated_token = $this->generate_token();



    $endpoint = $this->endpoint_base_url . $data['endpoint'];
		
    $dataToSend = $data;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $generated_token, 
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dataToSend, JSON_PRESERVE_ZERO_FRACTION));

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);

        http_response_code(500);

        return [
          'status_code' => 500,
          'error' => $error
        ];
    }
    curl_close($ch);

    // expecting to be a json encoded response
    return $response;
  }

}
