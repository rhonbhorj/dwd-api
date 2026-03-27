<?php

class Api_model extends CI_Model
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

        function chk_access($data)
    {
        if ($data) {
            $sql = "select * from api_keys ak left join api_users au on ak.id=au.key_id where ak.key like ? and au.api_name like ? and au.api_password like ?";
       
            $Q = $this->db->query($sql, array(
                $data['key'],
                $data['username'],
                $data['userpassword']
            ));
            return $Q->row_array();
        } else {
            return false;
        }
    }

    

}
