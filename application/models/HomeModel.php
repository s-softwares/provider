<?php
defined('BASEPATH') or exit('No direct script access allowed');
class HomeModel extends CI_Model
{
   public function login_user($email, $pass)
   {
      $this->db->select('*');
      $this->db->from('user');
      $this->db->where("(user.email ='$email' OR user.mobile ='$email')");
      $this->db->where("password", $pass);

      if ($query = $this->db->get()) {
         return $query->row_array();
      } else {
         return false;
      }
   }

   public function add_user($data)
   {
      $this->load->helper('url');

      return $this->db->insert('user', $data);
   }

   public function email_phone_check($email)
   {
      $this->db->select('*');
      $this->db->from('user');
      $this->db->where("(user.email = '$email')");
      // $this->db->where('otp_status','1');
      $query = $this->db->get();

      if ($query->num_rows() > 0) {
         return false;
      } else {
         return true;
      }
   }

   public function Is_already_register($email)
   {
      $this->db->where('email', $email);
      $query = $this->db->get('user');
      if ($query->num_rows() > 0) {
         return true;
      } else {
         return false;
      }
   }

   public function Update_user_data($data, $email)
   {
      $this->db->where('email', $email);
      $this->db->update('user', $data);
   }

   public function Insert_user_data($data)
   {
      $this->db->insert('user', $data);
   }

   public function checkUser($userData = array())
   {
      if (!empty($userData)) {
         //check whether user data already exists in database with same oauth info
         $this->db->select("facebook_id");
         $this->db->from("user");
         $this->db->where(array('facebook_id' => $userData['oauth_provider'], 'oauth_uid' => $userData['oauth_uid']));
         $prevQuery = $this->db->get();
         $prevCheck = $prevQuery->num_rows();

         if ($prevCheck > 0) {
            $prevResult = $prevQuery->row_array();

            //update user data
            $userData['modified'] = date("Y-m-d H:i:s");
            $update = $this->db->update($this->tableName, $userData, array('id' => $prevResult['id']));

            //get user ID
            $userID = $prevResult['id'];
         } else {
            //insert user data
            $userData['created']  = date("Y-m-d H:i:s");
            $userData['modified'] = date("Y-m-d H:i:s");
            $insert = $this->db->insert($this->tableName, $userData);

            //get user ID
            $userID = $this->db->insert_id();
         }
      }

      //return user ID
      return $userID ? $userID : FALSE;
   }
}
