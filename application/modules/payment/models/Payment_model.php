<?php

class Payment_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    private function generateRandomString($length = 25)

    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i ++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    public function validate_user_access($data)
    {
        $sql = "select * from tbl_users where  user_id ='" .$data['user_id']."'";
        $Q = $this->db->query($sql);
        return $Q->row_array() ? $Q->row_array() : false;
    }

    function chk_token($company_id,$token)
    {
        
        $sql = "select * from ngsi_token  where company_id = ? and token = ? and status = 'ACTIVE'";

        $Q   = $this->db->query($sql,
                array($company_id,$token));
        return $Q->result_array() ? $Q->result_array() : false;
       
    }

    public function chk_reference($reference_number)
    {
        $sql = "select * from tbl_transactions  where reference_number = ? ";

        $Q   = $this->db->query($sql, array($reference_number));
        return $Q->result_array() ? $Q->result_array() : false;

    }
    public function latest_token()
    {
        $sql = "select * from merchant_token  
                ORDER BY m_id DESC 
                LIMIT 1" ;

        $Q   = $this->db->query($sql);
        return $Q->row_array() ? $Q->row_array() : false;

    }
    
    public function insert_metrix_token($data)
    {
        return $this->db->insert('merchant_token', $data);
    }

    

}
