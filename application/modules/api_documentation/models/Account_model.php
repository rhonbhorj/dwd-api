<?php

class Account_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    public function validate_session($data)
    {
        $sql = "select * from users_logs where  sess_id like ? and log_type  ='1'";
        $Q = $this->db->query($sql, array(
            $data['sess_id']
        ));
        return $Q->row_array() ? $Q->row_array() : false;
    }
}