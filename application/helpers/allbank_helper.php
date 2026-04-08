<?php

function allbank_access()
{

	$access = array(
		'access_id' =>  $_ENV['AB_ACCESS_ID'],
		'secret_key'  => $_ENV['AB_SECRET_KEY'],
		"url" => $_ENV['AB_URL'],
		"SoapAction" => $_ENV['AB_SOAPACTION'],
		"acctno" => $_ENV['AB_ACCNO'],
			
	);

	return $access;
}


function array_to_xml_attributes($tag_name, $data)
{
	$xml = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
$xml .= '<' . $tag_name; foreach ($data as $key=> $value) {
    // Clean up and escape values
    $escaped_value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    $xml .= ' ' . $key . '="' . $escaped_value . '"';
    }

    $xml .= " />\n";
    return $xml;
    }

    function dto()
    {
    // date_default_timezone_set('Asia/Manila');
    $tdt = date('Y-m-d\TH:i:s.vP');
    // $allBankAccess= allbank_access();
    $data = [

    'tdt' => $tdt,
    'cmd' => 'DTO'

    ];


    return api_call($data);
    }

    // function allbank_cashin($request, $trans_reference,$clientData)
    function allbank_cashin()
    {
        date_default_timezone_set('Asia/Manila');
        // $tdt = $request['tdt'];
        $allBankAccess = allbank_access();




        // if ($request['method'] == "dynamic") {
        // $make_status = '0';
        // } else {
        // $make_status = '1';
        // }

        $tokenData= token();

        // $data = [
        // 'id' => $allBankAccess['access_id'],
        // 'tdt' => $tokenData['tdt'],
        // 'token' => $tokenData['token'],
        // 'cmd' => 'MERC-QR-REQ',
        // 'rf' => $trans_reference,
        // 'amt' => $request['amount'],
        // 'merc_tid' => $clientData['tid'],
        // 'make_static_qr' => $make_status
        // ];

          $data = [
        'id' => $allBankAccess['access_id'],
        'tdt' => $tokenData['tdt'],
        'token' => $tokenData['token'],
        'cmd' => 'MERC-QR-REQ',
        'rf' => "NGSI-".date("His").rand(1000,9999),
        'amt' => "10",
        'merc_tid' => "0",
        'make_static_qr' => 0
        ];

        // echo json_encode($data);
        return api_call($data);



    }

    function allcashout($request, $trans_reference,$clientData)
    {
        date_default_timezone_set('Asia/Manila');

        $allBankAccess = allbank_access();


        $tokenData= token();

        $data = [
        'id' => $allBankAccess['access_id'],
        'tdt' => $tokenData['tdt'],
        'token' => $tokenData['token'],
        'cmd' => $clientData['cmd'], //API command "IPAYPTR ,PNETPTR"
        // Source Account Number
        'acctno'=>$clientData['ab_accno_co'],
        'amt' => $request['total_txn_amt'],
        'dbk'=>$clientData['code'], //Destination Bank Code
        'acctno2'=>$request['account_number'], //Destination Account Number
        'ln'=> $request['name'], //Destination Account Name
        'ref_id' => $trans_reference
        //  'ref_id' => "2-001Ft133"
        ];

        // echo json_encode($data);
        // return api_call($data);

        // $array= array(   "1"=>"0",'2'=>"431" );
        $array= array(   "1"=>"0" );

        $randomValue = $array[array_rand($array)];

        $resp['response'] =[
        '@attributes' => [
        'peso_ref_id' => $trans_reference,
        'tdate' => 'Nov 7, 2025 2:03pm',
        'ReturnCode' => $randomValue,
        "ibft_id_code"=>"1480628",
        "inv"=>"986469",
        'ErrorMsg' => 'und Transfer is on process.'
        // 'ErrorMsg' => 'Error daw'
        ]
        ];
        $resp['status_code'] = 200;
        return $resp;

    }


    function soa_details($request)
    {
            date_default_timezone_set('Asia/Manila');
            $tdt = date('Y-m-d\TH:i:s.vP');



            $allBankAccess = allbank_access();

            $tokenData= token();

            $data = [
            'id' => $allBankAccess['access_id'],
            'tdt' => $tokenData['tdt'],
            'token' => $tokenData['token'],
            'cmd' => 'ACCOUNT-SOA',
            'acctno' => $allBankAccess['acctno'],
            'ds' => $request['ds'],
            'de' => $request['de'],
            'trans_idcode' => "1"
            ];



            return api_call($data);
    }
    function soa_cashout_details($request)
    {
               date_default_timezone_set('Asia/Manila');
            $tdt = date('Y-m-d\TH:i:s.vP');



            $allBankAccess = allbank_access();

            $tokenData= token();

            $data = [
            'id' => $allBankAccess['access_id'],
            'tdt' => $tokenData['tdt'],
            'token' => $tokenData['token'],
            'cmd' => 'ACCOUNT-SOA',
            'acctno' =>$request['account_number'],
            'ds' => $request['ds'],
            'de' => $request['de'],
            'trans_idcode' => "1"
            ];



            return api_call($data);



    }

    function soa_balance_inq($acctno)
    {
            date_default_timezone_set('Asia/Manila');
            $tdt = date('Y-m-d\TH:i:s.vP');



            $allBankAccess = allbank_access();

            $tokenData= token();

            $data = [
            'id' => $allBankAccess['access_id'],
            'tdt' => $tokenData['tdt'],
            'token' => $tokenData['token'],
            'cmd' => 'ACCOUNT-INQ',
            'acctno' => $acctno

            ];
        // $getBalance['@attributes']['AvailableBalance']="176605.28";
        //     //for testing only
        //     $resp['response']=$getBalance;
        
        //     $resp['status_code'] = 200;
        //     return $resp;

            return api_call($data);
    }


    // transaction status
    function mech_pay_chk($mtoken)
    {

    date_default_timezone_set('Asia/Manila');
    $tdt = date('Y-m-d\TH:i:s.vP');
    $allBankAccess = allbank_access();


    $tokenData= token();
    $data = [
    'id' => $allBankAccess['access_id'],
    'tdt' => $tokenData['tdt'],
    'token' => $tokenData['token'],
    'cmd' => 'MERC-PAY-CHK',
    'merc_token' => $mtoken
    ];



    return api_call($data);
    }

     function cashout_txn_status($request_data,$cmd)
    {
        //         <Account.Info
        // id="{{access_id}}" tdt="{{tdt}}" token="{{token}}"
        // cmd="IPAY-STATUS"
        // ref_id="242025ZAXSWTDY01"
        // />

            date_default_timezone_set('Asia/Manila');
            $tdt = date('Y-m-d\TH:i:s.vP');
            $allBankAccess = allbank_access();


            $tokenData= token();
            $data = [
            'id' => $allBankAccess['access_id'],
            'tdt' => $tokenData['tdt'],
            'token' => $tokenData['token'],
            'cmd' => $cmd,
            'ref_id' => $request_data['txn_reference']
            ];



            return api_call($data);
    }




    function mech_cancel($mtoken)
    {

    date_default_timezone_set('Asia/Manila');
    $tdt = date('Y-m-d\TH:i:s.vP');
    $allBankAccess = allbank_access();
    $tokenData= token();
    $data = [
    'id' => $allBankAccess['access_id'],
    'tdt' => $tokenData['tdt'],
    'token' => $tokenData['token'],
    'cmd' => 'MERC-CANCEL',
    'merc_token' => $mtoken
    ];

    return api_call($data);
    }


    function list_bank($data)
    {

    date_default_timezone_set('Asia/Manila');
    $tdt = date('Y-m-d\TH:i:s.vP');
    $allBankAccess = allbank_access();
    $tokenData= token();
    $data = [
    'id' => $allBankAccess['access_id'],
    'tdt' => $tokenData['tdt'],
    'token' => $tokenData['token'],
    'cmd' => $data
    ];
    return api_call($data);
    }



    function api_call($data)
    {

    date_default_timezone_set('Asia/Manila');
    $tdt = date('Y-m-d\TH:i:s.vP');
    $allBankAccess = allbank_access();

    $xml_output = array_to_xml_attributes('Account.Info', $data);

    $ch = curl_init($allBankAccess['url']);



    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_output);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20); # imeout after 20 seconds, you can increase it

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: text/xml',
    'SoapAction: ' . $allBankAccess['SoapAction']
    ));
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); //faster!!!!

    $response = curl_exec($ch);
    $http_status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $xml = simplexml_load_string($response);
    $json = json_encode($xml);
    $array = json_decode($json, TRUE);

    $resp['response'] = $array;
    $resp['status_code'] = $http_status_code;

    return $resp;
    }




    function token()
    {

    date_default_timezone_set('Asia/Manila');
    $tdt = date('Y-m-d\TH:i:s.vP');
    $allBankAccess = allbank_access();

    $shaStr = $allBankAccess['access_id'] . $allBankAccess['secret_key'] . $tdt;
    $token = sha1($shaStr);

    $returnData['token']= $token;
    $returnData['tdt']= $tdt;

    return $returnData;
    }