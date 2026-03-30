<?php


defined('BASEPATH') or exit('No direct script access allowed');


class Api_documentation extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Manila');


   
    }




    // public function swagger_api()
    // {
    //     $this->load->helper('url'); 
     
    //   	$this->load->view('swagger_api');
    // }
       public function redoc_api()
    {
        $this->load->helper('url'); 
  
      	$this->load->view('api_documentation/redoc');
    }

    // public function cashout_disbursment_swagger_api() //this is for cashout diabusment
    // {
    //     $this->load->helper('url'); 
    //   	$this->load->view('api_documentation/cashout_disbursment_swagger_api');
    // }

    // public function cashout_disbursment_redoc_api() //this is for cashout diabusment
    // {
    //     $this->load->helper('url'); 
    //   	$this->load->view('api_documentation/cashout_disbursment_redoc_api');
    // }

    // ///cashout V2
    // public function cashout_disbursment_redoc_api_v2() //this is for cashout diabusment
    // {
    //     $this->load->helper('url'); 
    //   	$this->load->view('api_documentation/cashout_disbursment_redoc_api_v2');
    // }

    // public function cashout_disbursment_swagger_api_v2() //this is for cashout diabusment
    // {
    //     $this->load->helper('url'); 
    //   	$this->load->view('api_documentation/cashout_disbursment_swagger_api_v2');
    // }

   
}
