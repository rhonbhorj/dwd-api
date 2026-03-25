<?php

defined( 'BASEPATH' ) or exit( 'No direct script access allowed' );
if (! function_exists('brankas_postback')){ 
    
    
    
    function brankas_postback($data,$callback_post) { 

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $data["call_back" ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>json_encode($callback_post),
        CURLOPT_HTTPHEADER => array(
    
            'Content-Type: application/json',
        
        ),
        ));

        $jresponse = curl_exec($curl);

        curl_close($curl);

        return true;
    }
 }
     
    function client_postback($data,$callback_post) { 

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $data["call_back" ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>json_encode($callback_post),
        CURLOPT_HTTPHEADER => array(
    
            'Content-Type: application/json',
        
        ),
        ));

        $jresponse = curl_exec($curl);

        curl_close($curl);

        return  $jresponse;
    }