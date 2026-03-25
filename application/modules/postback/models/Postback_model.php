<?php

class Postback_model extends CI_Model
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

 
     public function callback_logs($pdata)
    {
     
        return $this->db->insert_id($this->db->insert('tbl_callback', $pdata));
    }

           public function chk_reference_number($refNo)
    {
        $sql = "select * from tbl_transaction where reference_number like ?";
        $Q = $this->db->query($sql, array(
            $refNo['reference_number']
        ));


        
         return $Q->row_array() ? $Q->row_array() : false;

       
    }

    public function find_data($data)
    {

    $query = $this->db
        ->where('reference_number', $data)
        ->get('tbl_callback');

    if (!$query) {
        return false;
    }

    return $query->num_rows() > 0 ? $query->result_array() : false;



    //       $query = $this->db
    //     ->where('reference_number', $data)
    //     ->get('tbl_callback');

    // return $query->num_rows() > 0 ? $query->result_array() : false;
    }

    public function get_council_list()
    {
       return $this->db->get('tbl_bsp_council')->row_array(); 
        // return $Q->row_array() ? $Q->row_array() : false;get_district_list
    }

        public function get_district_list($data)
    {
          return $this->db->where('coucil_code', $data)
                        ->where('status', 'active')
                        ->get('tbl_district')
                        ->result(); 
    }

            public function get_sub_district_list($data)
    {
          return $this->db->where('district_code', $data)
                        ->where('status', 'active')
                        ->get('tbl_sub_district')
                        ->result(); 
    }

    public function chk_council_code($data)
    {
        return $this->db->where('coucil_code', $data)
            ->where('status', 'active')
            ->get('tbl_bsp_council')
            ->result(); 
    }

        public function get_school_list($data)
    {
        return $this->db->where('coucil_code', $data['coucil_code'])
            ->where('district_code', $data['district_code'])
            ->where('sub_district_code', $data['sub_district_code'])
            ->where('status', 'active')
            ->get('tbl_school')
            ->result(); 
    }
    public function chk_school($data)
    {

      return $this->db->where('coucil_code', $data['coucil_code'])
            ->where('district_code', $data['district_code'])
            ->where('sub_district_code', $data['sub_district_code'])
            ->where('shool_code', $data['shool_code'])
            ->where('status', 'active')
            ->get('tbl_school')
            ->result(); 

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

     public function do_apilogs($pdata)
    {
        return $this->db->insert_id($this->db->insert('api_logs', $pdata));
    }
       public function insert_payment_log($pdata)
    {
          return $this->db->insert('tbl_transaction', $pdata);
    }
      function do_insert($pdata)
    {
         return $this->db->insert('tbl_transaction', $pdata);
    }
    public function doUpdateApilogs($update, $where)
    {
        $this->db->where('api_id', $where)->update('api_logs', $update);
        return $this->db->affected_rows();
    }
            public function update_tbl_transaction_data($update, $where)
    {
        $this->db->where('reference_number', $where)->limit(1)->update('tbl_transaction', $update);
        return $this->db->affected_rows();

 
    }
}