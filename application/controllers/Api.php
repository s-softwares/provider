<?php

class Api extends CI_Controller
{
   public function __construct()
   {
      parent::__construct();
      $this->load->model('front_model');
      $this->load->model('firebase_model');
   }

   public function register()
   {
      header('Content-Type: application/json');

      //print_r($user);
      if (!isset($_POST['email']) || !isset($_POST['password']) || !isset($_POST['username'])) {
         $temp["response_code"] = "0";
         $temp["message"] = "Enter Data";
         $temp["status"] = "failure";
         echo json_encode($temp);
      } else {
         $user = array(
            'email' => $this->input->post('email'),
            'password' => md5($this->input->post('password')),
            'username' => $this->input->post('username')
         );
         $email_check = $this->front_model->email_check($user['email']);
         $username_check = $this->front_model->username_check($user['username']);
         $temp = array();
         if ($email_check || $username_check) {

            $user['date'] = time();

            // Make Database Post
            $reg = $this->front_model->register_user($user);
            if ($reg) {
               $temp["response_code"] = "1";
               $temp["message"] = "user register success";
               $temp["status"] = "success";
               $temp["user"] = $reg;
               echo json_encode($temp);
            } else {
               $temp["response_code"] = "0";
               $temp["message"] = "user register failure";
               $temp["status"] = "failure";
               echo json_encode($temp);
            }
         } else {

            $temp["response_code"] = "0";
            $temp["message"] = "Email id Already Registered";
            $temp["status"] = "failure";
            echo json_encode($temp);
         }
      }
   }

   public function login()
   {
      $login = array(
         'email' => $this->input->post('email'),
         'password' => md5($this->input->post('password')),
         'device_token' => $this->input->post('device_token')
      );
      if ($login['email'] == "") {
         $temp["response_code"] = "0";
         $temp["message"] = "Enter Data";
         $temp["status"] = "failure";
         echo json_encode($temp);
      } else {
         $temp = array();
         $data = $this->front_model->login_user($login['email'], $login['password']);

         if (!empty($data)) {
            $device_token = $this->input->post('device_token');
            if ($data['email'] == $login['email']) {
               $this->db->where(array("email" => $login['email']));
               $this->db->update('user', array('device_token' => $device_token));
            } else {
               $this->db->where(array("username" => $login['email']));
               $this->db->update('user', array('device_token' => $device_token));
            }
         }

         $data = $this->front_model->login_user($login['email'], $login['password']);

         if ($data) {
            $temp["response_code"] = "1";
            $temp["message"] = "user login success";
            $temp["status"] = "success";
            $temp["user_id"] = $data['id'];
            $temp["user_token"] = "";
            echo json_encode($temp);
            return;
         } else {
            $temp["response_code"] = "0";
            $temp["message"] = "user login failure";
            $temp["user_id"] = "";
            $temp["user_token"] = "";
            $temp["status"] = "failure";
            echo json_encode($temp);
         }
      }
   }

   public function social_login()
   {
      header('Content-Type: application/json');
      $device_token = $this->input->post('device_token');
      if (!isset($_POST['type'])) {
         $temp["response_code"] = "0";
         $temp["message"] = "Enter Data";
         $temp["status"] = "failure";
         echo json_encode($temp);
      } else {
         $type = $this->input->post("type");

         // if($type == "facebook" || $type == "google") {

         $email = $this->input->post("email");

         $username = $this->input->post("username");

         $facebook_id = $this->input->post("facebook_id");
         $image_url = $this->input->post("image_url");

         if ($username == "") {
            $temp["response_code"] = "0";
            $temp["message"] = "No User Name or Email Given!";
            $temp["status"] = "failure";
            echo json_encode($temp);
            return;
         }

         $email_check = $this->front_model->email_check($email);
         $facebook_id_check = $this->front_model->facebook_id_check($facebook_id);
         $time = time();
         $user = array(
            "email" => $email,
            "mobile" => "",
            "password" => "",
            "username" => $username,
            "facebook_id" => $facebook_id,
            "type" => $type,
            "address" => "",
            "city" => "",
            "country" => "",
            "isGold" => "",
            "device_token" => $device_token,
            "date" => "$time"
         );

         if (empty($image_url)) {
            $user["profile_pic"] = "";
         } else {
            $user["profile_pic"] = $image_url;
         }

         if (empty($facebook_id)) {
            if (!$email_check) {

               $this->db->where(array("email" => $email));
               $this->db->update('user', array('device_token' => $device_token));

               $user = $this->db->get_where("user", array("email" => $email))->row();
               if (empty($image_url)) {
                  if ($user->profile_pic != "") {
                     $user->profile_pic = base_url("uploads/profile_pics/" . $user->profile_pic);
                  }
               } else {
                  $user->profile_pic = $image_url;
               }

               $temp["response_code"] = "1";
               $temp["message"] = "user register success";
               $temp["user_id"] = $user->id;
               $temp["user_token"] = "";
               $temp["status"] = "success";
               echo json_encode($temp);
               return;
            }
         } else {
            if (!$facebook_id_check) {

               $this->db->where(array("facebook_id" => $facebook_id));
               $this->db->update('user', array('device_token' => $device_token));

               $user = $this->db->get_where("user", array("facebook_id" => $facebook_id))->row();

               if (empty($image_url)) {
                  if ($user->profile_pic != "") {
                     // $user->profile_pic = base_url("uploads/profile_pics/" . $user->profile_pic);
                     $user->profile_pic = $user->profile_pic;
                  }
               } else {
                  $user->profile_pic = $image_url;
               }

               $temp["response_code"] = "1";
               $temp["message"] = "user register success";
               $temp["user_id"] = $user->id;
               $temp["user_token"] = "";
               $temp["status"] = "success";
               echo json_encode($temp);
               return;
            }
         }

         if ($this->db->insert("user", $user)) {
            // $user["id"] = $this->db->insert_id();

            $id = $this->db->insert_id();
            $user["id"] = "$id";

            $temp["response_code"] = "1";
            $temp["message"] = "user register success";
            $temp["user_id"] = "$id";
            $temp["user_token"] = "";
            $temp["status"] = "success";
            echo json_encode($temp);
            return;
         } else {
            $temp["response_code"] = "0";
            $temp["message"] = "user register fail";
            $temp["status"] = "fail";
            $temp["user_id"] = "";
            $temp["user_token"] = "";
            echo json_encode($temp);
            return;
         }
      }
   }

   public function vendor_register()
   {
      header('Content-Type: application/json');

      if (!isset($_POST['email']) || !isset($_POST['password']) || !isset($_POST['uname'])) {
         $temp["response_code"] = "0";
         $temp["message"] = "Enter Data";
         $temp["status"] = "failure";
         echo json_encode($temp);
      } else {
         $user = array(
            'email' => $this->input->post('email'),
            'uname' => $this->input->post('uname'),
            'password' => md5($this->input->post('password')),
            'gender' => $this->input->post('gender'),
            'date_of_birth' => $this->input->post('date_of_birth'),
         );
         $email_check = $this->front_model->vendor_email_check($user['email']);
         $temp = array();

         if ($email_check) {

            $user['date'] = time();

            $reg = $this->front_model->register_vendor($user);
            if ($reg) {

               $temp["response_code"] = "1";
               $temp["message"] = "user register success";
               $temp["status"] = "success";
               $temp["user"] = "$reg";
               echo json_encode($temp);
            } else {
               $temp["response_code"] = "0";
               $temp["message"] = "user register failure";
               $temp["status"] = "failure";
               echo json_encode($temp);
            }
         } else {

            $temp["response_code"] = "0";
            $temp["message"] = "Email id Already Registered";
            $temp["status"] = "failure";
            echo json_encode($temp);
         }
      }
   }

   public function vendor_login()
   {

      header('Content-Type: application/json; charset=utf-8');

      $login = array(
         'email' => $this->input->post('email'),
         'password' => md5($this->input->post('password')),
         'device_token' => $this->input->post('device_token')
      );
      if ($login['email'] == "") {
         $temp["response_code"] = "0";
         $temp["message"] = "Enter Data";
         $temp["status"] = "failure";
         echo json_encode($temp);
      } else {
         $temp = array();
         $data = $this->front_model->login_vendor($login['email'], $login['password']);
         if ($data) {
            $temp["response_code"] = "1";
            $temp["message"] = "user login success";
            $temp["status"] = "success";
            $temp["user_id"] = $data['id'];
            //   $temp["payment_status"]=$data['pstatus'];
            $temp["user_token"] = md5(uniqid(rand(), true));
            echo json_encode($temp);
            $this->db->where(array("email" => $login['email'], "password" => $login['password']));
            $this->db->update('vendor', array('device_token' => $login['device_token']));
         } else {
            $temp["response_code"] = "0";
            $temp["message"] = "user login failure";
            $temp["status"] = "failure";
            $temp["user_id"] = "";
            $temp["user_token"] = md5(uniqid(rand(), true));
            echo json_encode($temp);
         }
      }
   }

   public function vendor_social_login()
   {
      header('Content-Type: application/json');
      $device_token = $this->input->post('device_token');
      if (!isset($_POST['login_type'])) {
         $temp["response_code"] = "0";
         $temp["message"] = "Enter Data";
         $temp["status"] = "failure";
         echo json_encode($temp);
      } else {
         $login_type = $this->input->post("login_type");

         $email = $this->input->post("email");

         $username = $this->input->post("username");

         $facebook_id = $this->input->post("facebook_id");
         $image_url = $this->input->post("image_url");

         // if ($username == "") {
         // 	$temp["response_code"] = "0";
         // 	$temp["message"] = "No User Name or Email Given!";
         // 	$temp["status"] = "failure";
         // 	echo json_encode($temp);
         // 	return;
         // }

         $email_check = $this->front_model->vendor_email_check($email);
         $facebook_id_check = $this->front_model->vendor_facebook_id_check($facebook_id);
         $time = time();
         $user = array(
            "uname" => $username,
            "email" => $email,
            "password" => "",
            "facebook_id" => $facebook_id,
            "login_type" => $login_type,
            "gender" => "",
            "date_of_birth" => "",
            "device_token" => $device_token,
            "date" => "$time",
         );

         if (empty($image_url)) {
            $user["profile_image"] = "";
         } else {
            $user["profile_image"] = $image_url;
         }

         $mobile = $this->input->post("mobile");
         if ($login_type == 'phone') {
            $user["uname"] = "";
            $user["mobile"] = $mobile;
         } else {
            $user["uname"] = $username;
            $user["mobile"] = "";
         }

         if (empty($facebook_id)) {
            if (!$email_check) {

               $this->db->where(array("email" => $email));
               $this->db->update('vendor', array('device_token' => $device_token));

               $user = $this->db->get_where("vendor", array("email" => $email))->row();
               if (empty($image_url)) {
                  if ($user->profile_image != "") {
                     $user->profile_image = base_url("uploads/profile_pics/" . $user->profile_image);
                  }
               } else {
                  $user->profile_image = $image_url;
               }

               $temp["response_code"] = "1";
               $temp["message"] = "user register success";
               $temp["status"] = "success";
               $temp["user_id"] = $user->id;
               $temp["user_token"] = "";
               echo json_encode($temp);
               return;
            }
         } else {
            if (!$facebook_id_check) {

               $this->db->where(array("facebook_id" => $facebook_id));
               $this->db->update('vendor', array('device_token' => $device_token));

               $user = $this->db->get_where("vendor", array("facebook_id" => $facebook_id))->row();

               if (empty($image_url)) {
                  if ($user->profile_image != "") {
                     // $user->profile_image = base_url("uploads/profile_pics/" . $user->profile_image);
                     $user->profile_image = $user->profile_image;
                  }
               } else {
                  $user->profile_image = $image_url;
               }

               $temp["response_code"] = "1";
               $temp["message"] = "user register success";
               $temp["status"] = "success";
               $temp["user_id"] = $user->id;
               $temp["user_token"] = "";
               echo json_encode($temp);
               return;
            }
         }

         if ($this->db->insert("vendor", $user)) {
            // $user["id"] = $this->db->insert_id();

            $id = $this->db->insert_id();
            $user["id"] = "$id";

            $temp["response_code"] = "1";
            $temp["message"] = "user register success";
            $temp["status"] = "success";
            $temp["user_id"] = "$id";
            $temp["user_token"] = "";
            echo json_encode($temp);
            return;
         } else {
            $temp["response_code"] = "0";
            $temp["message"] = "user register fail";
            $temp["status"] = "fail";
            $temp["user_id"] = "";
            $temp["user_token"] = "";
            echo json_encode($temp);
            return;
         }
      }
   }

   public function user_edit()
   {

      $this->load->helper('form');
      $this->load->library('form_validation');

      $id = $this->input->post('id');

      $user = $this->db->get_where('user', array('id' => $id), 1)->row();

      // $this->form_validation->set_rules('email', 'Email', 'required');
      $this->form_validation->set_rules('username', 'username', 'required');

      if ($this->form_validation->run() === FALSE) {
         $temp["response_code"] = "0";
         $temp["message"] = "Enter Data";
         $temp["status"] = "failure";
         echo json_encode($temp);
      } else {
         $res_image = $user->profile_pic;

         if (isset($_FILES['profile_pic']['name'])) {
            $image_exts = array("tif", "jpg", "jpeg", "gif", "png");

            $configVideo['upload_path'] = './uploads/profile_pics/'; # check path is correct
            $configVideo['max_size'] = '102400';
            $configVideo['allowed_types'] = $image_exts; # add video extenstion on here
            $configVideo['overwrite'] = FALSE;
            $configVideo['remove_spaces'] = TRUE;
            $configVideo['file_name'] = uniqid();

            $this->load->library('upload', $configVideo);
            $this->upload->initialize($configVideo);

            if (!$this->upload->do_upload('profile_pic')) # form input field attribute
            {
               $temp["response_code"] = "0";
               $temp["message"] = "Image Type Error";
               $temp["status"] = "failure";
               echo json_encode($temp);
            } else {
               # Upload Successfull
               $upload_data = $this->upload->data();
               $res_image = $upload_data['file_name'];
            }
         }

         $user = array(
            'email' => $this->input->post('email'),
            'username' => $this->input->post('username'),
            'mobile' => $this->input->post('mobile'),
            'address' => $this->input->post('address'),
            'city' => $this->input->post('city'),
            'country' => $this->input->post('country'),
            'profile_pic' => $res_image
         );

         $this->db->where('id', $id);
         $update = $this->db->update('user', $user);
         if ($update) {
            $temp["response_code"] = "1";
            $temp["message"] = "Update Successfully";
            $temp["status"] = "success";
            echo json_encode($temp);
         } else {
            $temp["response_code"] = "0";
            $temp["message"] = "Database error";
            $temp["status"] = "failure";
            echo json_encode($temp);
         }
      }
   }

   public function vendor_edit()
   {

      $this->load->helper('form');
      $this->load->library('form_validation');

      $id = $this->input->post('id');

      $this->form_validation->set_rules('email', 'Email', 'required');
      $this->form_validation->set_rules('uname', 'username', 'required');

      if ($this->form_validation->run() === FALSE) {
         $temp["response_code"] = "0";
         $temp["message"] = "Enter Data";
         $temp["status"] = "failure";
         echo json_encode($temp);
      } else {
         $restaurant = $this->db->get_where('vendor', array('id' => $id), 1)->row();
         $profile_image = $restaurant->profile_image;
         if (isset($_FILES['profile_image']['name']) && $_FILES['profile_image']['name'] != "") {
            // File upload configuration
            $config['upload_path'] = './uploads/profile_pics/';
            $config['allowed_types'] = 'gif|jpg|png|jpeg';
            $config['file_name'] = uniqid();
            $config['overwrite'] = TRUE;


            // Load and initialize upload library
            $this->load->library('upload');
            $this->upload->initialize($config);

            // Upload file to server
            if ($this->upload->do_upload('profile_image')) {
               // Uploaded file data
               $fileData = $this->upload->data();
               $profile_image = $fileData['file_name'];
            } else {
               $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
               $temp["response_code"] = "0";
               $temp["message"] = $error['error'];
               $temp["status"] = "failure";
               echo json_encode($temp);
            }
         }

         $user = array(
            'uname' => $this->input->post('uname'),
            'email' => $this->input->post('email'),
            'mobile' => $this->input->post('mobile'),
            'gender' => $this->input->post('gender'),
            'date_of_birth' => $this->input->post('date_of_birth'),
            'profile_image' => $profile_image,
         );

         $this->db->where('id', $id);
         $update = $this->db->update('vendor', $user);
         if ($update) {
            $temp["response_code"] = "1";
            $temp["message"] = "Update Successfully";
            $temp["status"] = "success";
            echo json_encode($temp);
         } else {
            $temp["response_code"] = "0";
            $temp["message"] = "Database error";
            $temp["status"] = "failure";
            echo json_encode($temp);
         }
      }
   }

   public function forgot_pass()
   {
      header('Content-Type: application/json');
      $email = $this->input->post('email');

      $result = array();

      $user = $this->db->get_where('user', array('email' => $email), 1)->row();

    //   if ($user->email != "") {
        if (!empty($user->email)) {
         $data = array();
         // $new_pass = uniqid();
         $new_pass = mt_rand(100000, 999999);
         $data['password'] = md5($new_pass);

         $this->db->where('email', $email);
         $this->db->update('user', $data);

         //Send Email
         $message = "<h1>Hello " . $user->email . "</h1>";
         $message .= "<h1>Your password reset was Successful. New Pass: " . $new_pass . "</h1>";


         $this->load->library('email');
         $config = array();
         $config['protocol'] = 'smtp';
         $config['smtp_host'] = 'smtp.gmail.com';
         $config['smtp_user'] = 'pesapapp09@gmail.com';
         $config['smtp_pass'] = 'svxhzsuqotpvrvjm';
         $config['smtp_port'] = 25;

         // Mail config
         $to = $user->email;
         $from = "pesapapp09@gmail.com";
         $fromName = "Swaft App Team";
         $mailSubject = "Password Reset Success";

         $config['mailtype'] = 'html';
         $this->email->initialize($config);
         $this->email->to($to);
         $this->email->from($from, $fromName);
         $this->email->subject($mailSubject);
         $this->email->message($message);

         // Send email & return status
         $send = $this->email->send();

         if ($send) {
            $result['status'] = 1;
            $result['msg'] = "Password Changed";
            $result['new_pass'] = $new_pass;

            echo json_encode($result);
         } else {
            $result['status'] = 0;
            $result['msg'] = "Mail Sent Error";
            $result['new_pass'] = $new_pass;

            echo json_encode($result);
         }
      } else {
         $result['status'] = 0;
         $result['msg'] = "invalid Email";

         echo json_encode($result);
      }
   }

   public function vendor_forgot_pass()
   {
      header('Content-Type: application/json');
      $email = $this->input->post('email');

      $result = array();

      $user = $this->db->get_where('vendor', array('email' => $email), 1)->row();

      if ($user->email != "") {
         $data = array();
         // $new_pass = uniqid();
         $new_pass = mt_rand(100000, 999999);
         $data['password'] = md5($new_pass);

         $this->db->where('email', $email);
         $this->db->update('vendor', $data);

         //Send Email
         $message = "<h1>Hello " . $user->email . "</h1>";
         $message .= "<h1>Your password reset was Successful. New Pass: " . $new_pass . "</h1>";


         $this->load->library('email');
         $config['protocol'] = 'smtp';
         $config['smtp_host'] = 'smtp.gmail.com';
         $config['smtp_user'] = 'pesapapp09@gmail.com';
         $config['smtp_pass'] = 'svxhzsuqotpvrvjm';
         $config['smtp_port'] = 25;

         // Mail config
         $to = $user->email;
         $from = "pesapapp09@gmail.com";
         $fromName = "Swaft App Team";
         $mailSubject = "Password Reset Success";

         $config['mailtype'] = 'html';
         $this->email->initialize($config);
         $this->email->to($to);
         $this->email->from($from, $fromName);
         $this->email->subject($mailSubject);
         $this->email->message($message);

         // Send email & return status
         $send = $this->email->send();

         if ($send) {
            $result['status'] = 1;
            $result['msg'] = "Password Changed";
            $result['new_pass'] = $new_pass;

            echo json_encode($result);
         } else {
            $result['status'] = 0;
            $result['msg'] = "Mail Sent Error";
            $result['new_pass'] = $new_pass;

            echo json_encode($result);
         }
      } else {
         $result['status'] = 0;
         $result['msg'] = "invalid Email";

         echo json_encode($result);
      }
   }

   public function search()
   {
      //header('Content-Type: application/json; charset=utf-8');
      if (isset($_POST['text'])) {

         $text = $_POST['text'];


         $this->db->like('res_name', $text);
         $this->db->or_like('res_name_u', $text);
         $this->db->or_like('res_address', $text);

         $restaurants = $this->db->get('restaurants');

         if ($restaurants->num_rows() > 0) {

            foreach ($restaurants->result() as $restaurant) {
               //$restaurant->res_image = base_url('uploads/'.$restaurant->res_image);
               $images = explode("::::", $restaurant->res_image);
               $imgs = array();
               $imgsa = array();
               foreach ($images as $key => $image) {
                  $imgs = base_url('uploads/') . $image;

                  array_push($imgsa, $imgs);
               }

               $restaurant->all_image = $imgsa;

               $imgsb = array();
               $imgsab = array();
               foreach ($images as $key => $image) {
                  $imgsb['res_imag' . $key] = base_url('uploads/') . $image;
               }

               $restaurant->res_image = $imgsb;

               $restaurant->logo = base_url() . 'uploads/' . $restaurant->logo;

               $resid = $restaurant->res_id;
               $querycount = $this->db->query("SELECT * FROM reviews WHERE rev_res = '$resid'");
               $mcount = $querycount->num_rows();

               $restaurant->review_count = $mcount;

               if ($restaurant->res_video == "") {
                  $restaurant->res_video = "";
               } else {
                  $restaurant->res_video = base_url() . 'uploads/' . $restaurant->res_video;
               }
            }

            $temp["response_code"] = "1";
            $temp["message"] = "Restaurants Found!";
            $temp['restaurants'] = $restaurants->result();
            $temp["status"] = "success";
            echo json_encode($temp);

            return;
         } else {
            $temp["response_code"] = "0";
            $temp["message"] = "No restaurants found";
            $temp["status"] = "fail";
            echo json_encode($temp);

            return;
         }
      }
   }

   public function get_discounted_res()
   {
      header('Content-Type: application/json');
      $restaurants = $this->db->get_where("restaurants", array('discount >' => 0));

      if ($restaurants->num_rows() > 0) {

         foreach ($restaurants->result() as $restaurant) {
            $restaurant->res_image = base_url('uploads/' . $restaurant->res_image);
         }

         $temp["response_code"] = "1";
         $temp["message"] = "Restaurants Found!";
         $temp['restaurants'] = $restaurants->result();
         $temp["status"] = "success";
         echo json_encode($temp);

         return;
      } else {

         $temp["response_code"] = "0";
         $temp["message"] = "No restaurants found";
         $temp["status"] = "fail";
         echo json_encode($temp);

         return;
      }
   }

   public function addDiscount()
   {
      header('Content-Type: application/json');
      if (isset($_POST['res_id']) && $_POST['discount']) {

         $discount = (int)$this->input->post('discount');

         if (!is_numeric($discount)) {
            $temp["response_code"] = "0";
            $temp["message"] = "Discount Must Be Integer";
            $temp["status"] = "fail";
            echo json_encode($temp);

            return;
         }

         $this->db->where('res_id', $this->input->post('res_id'));
         if ($this->db->update('restaurants', array('discount' => $discount))) {
            $temp["response_code"] = "1";
            $temp["message"] = "Discount Added";
            $temp["status"] = "success";
            echo json_encode($temp);

            return;
         } else {
            $temp["response_code"] = "0";
            $temp["message"] = "Database Error";
            $temp["status"] = "fail";
            echo json_encode($temp);

            return;
         }
      } else {

         $temp["response_code"] = "0";
         $temp["message"] = "Missing Fields";
         $temp["status"] = "fail";
         echo json_encode($temp);

         return;
      }
   }

   public function likeRes()
   {
      if (isset($_POST['res_id']) && isset($_POST['user_id'])) {

         $like = array();
         $like['res_id'] = $this->input->post('res_id');
         $like['user_id'] = $this->input->post('user_id');
         $like['date'] = time();

         $checkLike = $this->front_model->likeCheck($like['user_id'], $like['res_id']);

         if (!$checkLike) {
            $temp["response_code"] = "0";
            $temp["message"] = "Already Liked Restaurant";
            $temp["status"] = "fail";
            echo json_encode($temp);

            return;
         }

         if ($this->db->insert('likes', $like)) {

            $temp["response_code"] = "1";
            $temp["message"] = "Liked Restaurant";
            $temp["status"] = "success";
            echo json_encode($temp);
         } else {

            $temp["response_code"] = "0";
            $temp["message"] = "Databse Error";
            $temp["status"] = "failure";
            echo json_encode($temp);
         }
      } else {

         $temp["response_code"] = "0";
         $temp["message"] = "Missing Fields";
         $temp["status"] = "failure";
         echo json_encode($temp);
      }
   }

   public function user_data()
   {
      header('Content-Type: application/json');
      $id = $this->input->post('user_id');
      if (empty($id)) {
         $temp["response_code"] = "0";
         $temp["message"] = "Enter Data";
         $temp["status"] = "failure";
         echo json_encode($temp);
      } else {
         $temp = array();
         $profile = array();
         $profile = $this->front_model->get_user($id);
         $reviews = $this->front_model->get_res($id);

         $user = array();
         $user['username'] = $profile->username;
         $user['email'] = $profile->email;
         $user['mobile'] = $profile->mobile;
         $user['address'] = $profile->address;
         $user['city'] = $profile->city;
         $user['country'] = $profile->country;
         $user['isGold'] = $profile->isGold;
         $user['device_token'] = $profile->device_token;
         $user['profile_pic'] = base_url("uploads/profile_pics/" . $profile->profile_pic);
         $user['profile_created'] = gmdate('d M Y', $profile->date);

         for ($i = 0; $i < count($reviews); $i++) {
            $res_id = $reviews[$i]->rev_res;

            $restaurant = $this->front_model->get_res_by_id($res_id);

            if ($restaurant) {
               $reviews[$i]->rev_res_id = $restaurant->res_id;

               $reviews[$i]->rev_res = $restaurant->res_name;

               $reviews[$i]->rev_username = $profile->username;

               $exp = explode("::::", $restaurant->res_image);
               foreach ($exp as $xx) {
                  $reviews[$i]->rev_res_image = base_url() . 'uploads/' . $xx;
               }
            }
         }

         $temp["response_code"] = "1";
         $temp["message"] = "User Found";
         $temp['user'] = $user;
         $temp['review'] = $reviews;
         $temp["status"] = "success";
         echo json_encode($temp);
      }
   }

   public function change_password()
   {

      $id = $this->input->post('user_id');

      if (empty($id)) {
         show_404();
      }

      $this->load->helper('form');
      $this->load->library('form_validation');

      $data['profile'] = $this->front_model->get_user($id);


      $password = md5($this->input->post('password'));
      $npassword = md5($this->input->post('npassword'));
      $cpassword = md5($this->input->post('cpassword'));

      if ($npassword == $cpassword) {
         $password_check = $this->front_model->password_check($password, $id);

         if ($password_check) {
            $this->front_model->change_pass($npassword, $id);
            $temp = array();
            $temp["response_code"] = "1";
            $temp["message"] = "Successfully Changed";
            $temp["status"] = "success";
            echo json_encode($temp);
         } else {
            $temp = array();
            $temp["response_code"] = "0";
            $temp["message"] = "Old Password Wrong";
            $temp["status"] = "failure";
            echo json_encode($temp);
         }
      } else {
         $temp = array();
         $temp["response_code"] = "0";
         $temp["message"] = "'New Password And Confirm Password Not Match..";
         $temp["status"] = "failure";
         echo json_encode($temp);
      }
   }

   public function create_res()
   {
      $result = array();
      header('Content-Type: application/json');

      if (isset($_POST['name'])) {
         if (!isset($_POST['name']) || !isset($_FILES['image']['name']) || !isset($_POST['description']) || !isset($_POST['cat_id']) || !isset($_POST['city']) || !isset($_POST['area'])) {
            $result['status'] = 0;
            $result['msg'] = "Missing Data";

            echo json_encode($result);
         } else {

            $cat_id = $this->input->post('cat_id');
            $cat_name = $this->db->get_where('categories', array('cat_id' => $cat_id), 1)->row();

            if (!$cat_name) {
               $result['status'] = 0;
               $result['msg'] = 'Invalid Category';

               echo json_encode($result);

               return;
            }

            $res_image = "";
            $image_exts = array("tif", "jpg", "gif", "png");

            $configVideo['upload_path'] = './uploads/'; # check path is correct
            $configVideo['max_size'] = '102400';
            $configVideo['allowed_types'] = $image_exts; # add video extenstion on here
            $configVideo['overwrite'] = FALSE;
            $configVideo['remove_spaces'] = TRUE;
            $configVideo['file_name'] = uniqid();

            $this->load->library('upload', $configVideo);
            $this->upload->initialize($configVideo);

            if (!$this->upload->do_upload('image')) # form input field attribute
            {
               # Upload Failed
               $result['status'] = 0;
               $result['msg'] = 'Image Upload Error';
               $result['erros'] = ($this->upload->display_errors());

               echo json_encode($result);
            } else {
               # Upload Successfull
               $upload_data = $this->upload->data();
               $res_image = $upload_data['file_name'];
            }

            $address = array(
               'city' => $_POST['city'],
               'area' => $_POST['area']
            );


            $data = array();
            $data['res_name'] = $this->input->post('name');
            $data['res_desc'] = $this->input->post('description');
            if ($this->input->post('website')) {
               $data['res_website'] = $this->input->post('website');
            }
            $data['res_image'] = $res_image;
            $data['res_isOpen'] = 'open';
            $data['res_status'] = 'active';
            $data['res_cat'] = $this->input->post('cat_id');
            $data['res_address'] = $_POST['city'] . ', ' . $_POST['area'];
            $data['res_cat_name'] = $cat_name->name;
            $data['res_create_date'] = time();
            $data['lat'] = $this->input->post('lat');
            $data['long'] = $this->input->post('long');


            if ($this->db->insert('restaurants', $data)) {
               $result['status'] = 1;
               $result['msg'] = "Restaurant Created";
               $result['restaurant'] = $data;

               echo json_encode($result);
            } else {
               $result['status'] = 0;
               $result['msg'] = "Database Error";


               echo json_encode($result);
            }
         }
      } else {
         $result['status'] = 0;
         $result['msg'] = "No name given";

         echo json_encode($result);
      }
   }

   public function get_liked_res()
   {
      header('Content-Type: application/json');
      if (isset($_POST['user_id'])) {
         $user_id = $this->input->post('user_id');
         $likes = $this->db->select('likes.like_id, restaurants.*')
            ->from('likes')
            ->where('likes.user_id', $user_id)
            ->join('restaurants', 'likes.res_id = restaurants.res_id')
            ->get();

         if ($likes->num_rows() > 0) {

            foreach ($likes->result() as $restaurant) {
               //$restaurant->res_image = base_url().'uploads/'.$restaurant->res_image;
               $restaurant->res_create_date = gmdate('d M Y', $restaurant->res_create_date);
               $catnm = $this->db->get_where('categories', array('id' => $restaurant->cat_id), 1)->row();
               $restaurant->c_name = $catnm->c_name;

               $images = explode("::::", $restaurant->res_image);
               $imgs = array();
               $imgsa = array();
               foreach ($images as $key => $image) {
                  $imgs = base_url('uploads/') . $image;

                  array_push($imgsa, $imgs);
               }

               $restaurant->all_image = $imgsa;

               $imgsb = array();
               $imgsab = array();
               foreach ($images as $key => $image) {
                  $imgsb['res_imag' . $key] = base_url('uploads/') . $image;
               }
               $restaurant->res_image = $imgsb;

               $restaurant->logo = base_url() . 'uploads/' . $restaurant->logo;

               if ($restaurant->res_video == "") {
                  $restaurant->res_video = "";
               } else {
                  $restaurant->res_video = base_url() . 'uploads/' . $restaurant->res_video;
               }

               $resid = $restaurant->res_id;
               $querycount = $this->db->query("SELECT * FROM reviews WHERE rev_res = '$resid'");
               $mcount = $querycount->num_rows();

               $restaurant->review_count = $mcount;
            }

            $result['status'] = 1;
            $result['msg'] = "Restaurants Found";
            $result['restaurants'] = $likes->result();
            echo json_encode($result);
         } else {
            $result['status'] = 0;
            $result['msg'] = "No Restaurants Found";
            echo json_encode($result);
         }
      } else {
         $result['status'] = 0;
         $result['msg'] = "Missing Fields";
         echo json_encode($result);
      }
   }

   public function update_to_gold()
   {
      header('Content-Type: application/json');
      if (isset($_POST['user_id'])) {
         $user_id = $this->input->post('user_id');
         $this->db->where('id', $user_id);
         if ($this->db->update('user', array('isGold' => 1))) {
            $result['status'] = 1;
            $result['msg'] = "User Updated to Gold";
            echo json_encode($result);
         } else {
            $result['status'] = 0;
            $result['msg'] = "Database Error";
            echo json_encode($result);
         }
      }
   }

   public function add_cat()
   {
      header('Content-Type: application/json');
      if (isset($_POST['cat_name'])) {

         if ($this->db->insert('categories', array('name' => $this->input->post('cat_name')))) {
            $result['status'] = 1;
            $result['msg'] = "Category Added";
            $result['category'] = $this->input->post('cat_name');

            echo json_encode($result);
         }
      } else {

         $result['status'] = 0;
         $result['msg'] = "No name given";

         echo json_encode($result);
      }
   }



   public function get_all_cat()
   {
      $result = array();
      header('Content-Type: application/json');

      $result['status'] = 1;
      $result['msg'] = "Restaurnats Found";
      $res = $this->db->get('categories')->result();

      for ($i = 0; $i < count($res); $i++) {
         $res[$i]->icon = base_url() . 'uploads/' . $res[$i]->icon;
         $res[$i]->img = base_url() . 'uploads/' . $res[$i]->img;
      }

      $result['categories'] = $res;
      //$result['restaurant']->c_name = $catnm->c_name;

      echo json_encode($result);
   }

   public function get_all_scat()
   {
      $result = array();
      header('Content-Type: application/json');

      $result['status'] = 1;
      $result['msg'] = "Restaurnats Found";
      $res = $this->db->get('sub_categories')->result();

      for ($i = 0; $i < count($res); $i++) {
         $res[$i]->icon = base_url() . 'uploads/' . $res[$i]->icon;
         $res[$i]->img = base_url() . 'uploads/' . $res[$i]->img;

         $res[$i]->cat_name = $this->db->get_where('categories', array('id' => $res[$i]->cat_id))->row()->c_name;
      }



      $result['categories'] = $res;
      //$result['restaurant']->c_name = $catnm->c_name;

      echo json_encode($result);
   }

   public function get_scat_by_cat()
   {
      $result = array();
      header('Content-Type: application/json');

      $id = $this->input->post('cat_id');
      $result['status'] = 1;
      $result['msg'] = "Sub Category Found";
      $res = $this->db->get_where('sub_categories', array('cat_id' => $id))->result();

      for ($i = 0; $i < count($res); $i++) {
         $res[$i]->icon = base_url() . 'uploads/' . $res[$i]->icon;
         $res[$i]->img = base_url() . 'uploads/' . $res[$i]->img;

         $res[$i]->cat_name = $this->db->get_where('categories', array('id' => $res[$i]->cat_id))->row()->c_name;
      }



      $result['categories'] = $res;
      //$result['restaurant']->c_name = $catnm->c_name;

      echo json_encode($result);
   }

   public function get_all_vip()
   {
      $result = array();
      header('Content-Type: application/json');

      $result['status'] = 1;
      $result['msg'] = "Restaurnats Found";
      //$res = $this->db->get_where('restaurants', array('status' => '1'))->result();

      $res = $this->db->query('SELECT * FROM restaurants WHERE FIND_IN_SET(1,status)')->result();

      for ($i = 0; $i < count($res); $i++) {
         //$res[$i]->res_image = base_url().'uploads/'.$res[$i]->res_image;
         $catnm = $this->db->get_where('categories', array('id' => $res[$i]->cat_id), 1)->row();
         $res[$i]->c_name = $catnm->c_name;

         $images = explode("::::", $res[$i]->res_image);
         $imgs = array();
         $imgsa = array();
         foreach ($images as $key => $image) {
            $imgs = base_url('uploads/') . $image;

            array_push($imgsa, $imgs);
         }

         $imgsb = array();
         $imgsab = array();
         foreach ($images as $key => $image) {
            $imgsb['res_imag' . $key] = base_url('uploads/') . $image;
         }

         $resid = $res[$i]->res_id;
         $querycount = $this->db->query("SELECT * FROM reviews WHERE rev_res = '$resid'");
         $mcount = $querycount->num_rows();

         $res[$i]->review_count = $mcount;

         $res[$i]->all_image = $imgsa;
         $res[$i]->res_image = $imgsb;
         $res[$i]->logo = base_url() . 'uploads/' . $res[$i]->logo;
         if ($res[$i]->res_video == "") {
            $res[$i]->res_video = "";
         } else {
            $res[$i]->res_video = base_url() . 'uploads/' . $res[$i]->res_video;
         }
      }

      $result['restaurants'] = $res;
      //$result['restaurant']->c_name = $catnm->c_name;

      echo json_encode($result);
   }

   public function get_all_nonvip()
   {
      $result = array();
      header('Content-Type: application/json');

      $result['status'] = 1;
      $result['msg'] = "Restaurnats Found";
      $res = $this->db->query('SELECT * FROM restaurants WHERE FIND_IN_SET(0,status)')->result();

      for ($i = 0; $i < count($res); $i++) {
         //$res[$i]->res_image = base_url().'uploads/'.$res[$i]->res_image;
         $catnm = $this->db->get_where('categories', array('id' => $res[$i]->cat_id), 1)->row();
         $res[$i]->c_name = $catnm->c_name;

         $images = explode("::::", $res[$i]->res_image);
         $imgs = array();
         $imgsa = array();
         foreach ($images as $key => $image) {
            $imgs = base_url('uploads/') . $image;

            array_push($imgsa, $imgs);
         }

         $imgsb = array();
         $imgsab = array();
         foreach ($images as $key => $image) {
            $imgsb['res_imag' . $key] = base_url('uploads/') . $image;
         }

         $resid = $res[$i]->res_id;
         $querycount = $this->db->query("SELECT * FROM reviews WHERE rev_res = '$resid'");
         $mcount = $querycount->num_rows();

         $res[$i]->review_count = $mcount;

         $res[$i]->all_image = $imgsa;
         $res[$i]->res_image = $imgsb;
         $res[$i]->logo = base_url() . 'uploads/' . $res[$i]->logo;
         if ($res[$i]->res_video == "") {
            $res[$i]->res_video = "";
         } else {
            $res[$i]->res_video = base_url() . 'uploads/' . $res[$i]->res_video;
         }
      }

      $result['restaurants'] = $res;
      //$result['restaurant']->c_name = $catnm->c_name;

      echo json_encode($result);
   }

   public function get_all_cat_byid()
   {
      $result = array();
      header('Content-Type: application/json');

      $cat_id = $this->input->post('cat_id');

      $result['status'] = 1;
      $result['msg'] = "Restaurnats Found";
      //$res = $this->db->get_where('restaurants', array('status' => '0','cat_id' => $cat_id))->result();

      $res = $this->db->query("SELECT * FROM restaurants WHERE FIND_IN_SET(2,status) AND cat_id='$cat_id' ")->result();


      for ($i = 0; $i < count($res); $i++) {
         //$res[$i]->res_image = base_url().'uploads/'.$res[$i]->res_image;
         $catnm = $this->db->get_where('categories', array('id' => $res[$i]->cat_id), 1)->row();
         $res[$i]->c_name = $catnm->c_name;

         $images = explode("::::", $res[$i]->res_image);
         $imgs = array();
         $imgsa = array();
         foreach ($images as $key => $image) {
            $imgs = base_url('uploads/') . $image;

            array_push($imgsa, $imgs);
         }

         $imgsb = array();
         $imgsab = array();
         foreach ($images as $key => $image) {
            $imgsb['res_imag' . $key] = base_url('uploads/') . $image;
         }

         $resid = $res[$i]->res_id;
         $querycount = $this->db->query("SELECT * FROM reviews WHERE rev_res = '$resid'");
         $mcount = $querycount->num_rows();

         $res[$i]->review_count = $mcount;

         $res[$i]->all_image = $imgsa;
         $res[$i]->res_image = $imgsb;
         $res[$i]->logo = base_url() . 'uploads/' . $res[$i]->logo;
         if ($res[$i]->res_video == "") {
            $res[$i]->res_video = "";
         } else {
            $res[$i]->res_video = base_url() . 'uploads/' . $res[$i]->res_video;
         }
      }

      $result['restaurants'] = $res;
      //$result['restaurant']->c_name = $catnm->c_name;

      echo json_encode($result);
   }

   public function get_all_cat_nvip()
   {
      $result = array();
      header('Content-Type: application/json');

      $cat_id = $this->input->post('cat_id');

      $result['status'] = 1;
      $result['msg'] = "Restaurnats Found";
      //$res = $this->db->get_where('restaurants', array('status' => '0','cat_id' => $cat_id))->result();

      $res = $this->db->query("SELECT * FROM restaurants WHERE FIND_IN_SET(1,status) AND cat_id='$cat_id' ")->result();


      for ($i = 0; $i < count($res); $i++) {
         //$res[$i]->res_image = base_url().'uploads/'.$res[$i]->res_image;
         $catnm = $this->db->get_where('categories', array('id' => $res[$i]->cat_id), 1)->row();
         $res[$i]->c_name = $catnm->c_name;

         $images = explode("::::", $res[$i]->res_image);
         $imgs = array();
         $imgsa = array();
         foreach ($images as $key => $image) {
            $imgs = base_url('uploads/') . $image;

            array_push($imgsa, $imgs);
         }

         $imgsb = array();
         $imgsab = array();
         foreach ($images as $key => $image) {
            $imgsb['res_imag' . $key] = base_url('uploads/') . $image;
         }

         $resid = $res[$i]->res_id;
         $querycount = $this->db->query("SELECT * FROM reviews WHERE rev_res = '$resid'");
         $mcount = $querycount->num_rows();

         $res[$i]->review_count = $mcount;

         $res[$i]->all_image = $imgsa;
         $res[$i]->res_image = $imgsb;

         $res[$i]->logo = base_url() . 'uploads/' . $res[$i]->logo;
         if ($res[$i]->res_video == "") {
            $res[$i]->res_video = "";
         } else {
            $res[$i]->res_video = base_url() . 'uploads/' . $res[$i]->res_video;
         }
      }

      $result['restaurants'] = $res;
      //$result['restaurant']->c_name = $catnm->c_name;

      echo json_encode($result);
   }

   public function get_all_cat_nvip_sorting()
   {
      $result = array();
      header('Content-Type: application/json');

      $cat_id = $this->input->post('cat_id');

      $result['status'] = 1;
      $result['msg'] = "Restaurnats Found";
      //$res = $this->db->get_where('restaurants', array('status' => '0','cat_id' => $cat_id))->result();

      $res = $this->db->query("SELECT * FROM restaurants  ORDER BY res_ratings DESC")->result();


      for ($i = 0; $i < count($res); $i++) {
         //$res[$i]->res_image = base_url().'uploads/'.$res[$i]->res_image;
         $catnm = $this->db->get_where('categories', array('id' => $res[$i]->cat_id), 1)->row();
         if (!empty($catnm)) {
            $res[$i]->c_name = $catnm->c_name;
         } else {
            $res[$i]->c_name = "";
         }


         $images = explode("::::", $res[$i]->res_image);
         $imgs = array();
         $imgsa = array();
         foreach ($images as $key => $image) {
            $imgs = base_url('uploads/') . $image;

            array_push($imgsa, $imgs);
         }

         $imgsb = array();
         $imgsab = array();
         foreach ($images as $key => $image) {
            $imgsb['res_imag' . $key] = base_url('uploads/') . $image;
         }

         $resid = $res[$i]->res_id;
         $querycount = $this->db->query("SELECT * FROM reviews WHERE rev_res = '$resid'");
         $mcount = $querycount->num_rows();

         $res[$i]->review_count = $mcount;

         $res[$i]->all_image = $imgsa;
         $res[$i]->res_image = $imgsb;

         $res[$i]->logo = base_url() . 'uploads/' . $res[$i]->logo;
         if ($res[$i]->res_video == "") {
            $res[$i]->res_video = "";
         } else {
            $res[$i]->res_video = base_url() . 'uploads/' . $res[$i]->res_video;
         }

         $imgsac = array();
         $imgsaca = array();
         $producta = unserialize($res[$i]->structure);
         if (!empty($producta)) {

            for ($ja = 0; $ja < count($producta); $ja++) {
               $fee_details_ida = $producta[$ja];
               $explodea = explode(',', $fee_details_ida);

               $imgsaca['type'] = $explodea[0];

               $imgsaca['type_name'] = $this->db->get_where('type', array('id' => $imgsaca['type']))->row()->c_name;

               $imgsaca['price'] = $explodea[1];
               array_push($imgsac, $imgsaca);
            }
            $res[$i]->type = $imgsac;
         } else {
            $res[$i]->type = array();
         }

         $reviewsa = array();
         //$reviews = array();
         $reviewsp = $this->front_model->get_rev_by_id_res($res[$i]->res_id);

         for ($iac = 0; $iac < count($reviewsp); $iac++) {
            $res_id = $reviewsp[$iac]->rev_res;
            $user_id = $reviewsp[$iac]->rev_user;

            $restaurantac = $this->front_model->get_res_by_id($res_id);
            $userac = $this->front_model->get_rev_by_id_user($user_id);

            $reviewsp[$iac]->rev_res = $restaurantac->res_name;
            $rev_user_data = $this->db->get_where("user", array("id" => $reviewsp[$iac]->rev_user))->row();
            if ($rev_user_data) {
               if ($rev_user_data->profile_pic) {
                  $rev_user_data->profile_pic = base_url("uploads/profile_pics/" . $rev_user_data->profile_pic);
               }
            }
            $reviewsp[$iac]->rev_user_data = $rev_user_data;

            if (empty($userac->username)) {
               $username = "";
            } else {
               $username = $userac->username;
            }

            $reviewsp[$iac]->rev_user = $username;

            // array_push($reviewsa,$reviewsp);
         }

         $res[$i]->reviews = $reviewsp;
      }



      $result['restaurants'] = $res;
      //$result['restaurant']->c_name = $catnm->c_name;

      echo json_encode($result);
   }

   public function get_all_cat_vip()
   {
      $result = array();
      header('Content-Type: application/json');

      $result['status'] = 1;
      $result['msg'] = "Restaurnats Found";
      //$res = $this->db->get_where('restaurants', array('status' => '2'))->result();

      $res = $this->db->query("SELECT * FROM restaurants WHERE FIND_IN_SET(2,status) ")->result();


      for ($i = 0; $i < count($res); $i++) {
         //$res[$i]->res_image = base_url().'uploads/'.$res[$i]->res_image;
         $catnm = $this->db->get_where('categories', array('id' => $res[$i]->cat_id), 1)->row();
         if (!empty($catnm)) {
            $res[$i]->c_name = $catnm->c_name;
         } else {
            $res[$i]->c_name = "";
         }

         $images = explode("::::", $res[$i]->res_image);
         $imgs = array();
         $imgsa = array();
         foreach ($images as $key => $image) {
            $imgs = base_url('uploads/') . $image;

            array_push($imgsa, $imgs);
         }

         $imgsb = array();
         $imgsab = array();
         foreach ($images as $key => $image) {
            $imgsb['res_imag' . $key] = base_url('uploads/') . $image;
         }

         $res[$i]->res_image = $imgsb;

         $resid = $res[$i]->res_id;
         $querycount = $this->db->query("SELECT * FROM reviews WHERE rev_res = '$resid'");
         $mcount = $querycount->num_rows();

         $res[$i]->review_count = $mcount;

         $res[$i]->all_image = $imgsa;
         $res[$i]->logo = base_url() . 'uploads/' . $res[$i]->logo;
         if ($res[$i]->res_video == "") {
            $res[$i]->res_video = "";
         } else {
            $res[$i]->res_video = base_url() . 'uploads/' . $res[$i]->res_video;
         }
      }



      $result['restaurants'] = $res;
      //$result['restaurant']->c_name = $catnm->c_name;

      echo json_encode($result);
   }

   public function get_all_res()
   {
      $result = array();
      header('Content-Type: application/json');

      $result['status'] = 1;
      $result['msg'] = "Restaurnats Found";
      $res = $this->db->get('restaurants')->result();

      for ($i = 0; $i < count($res); $i++) {

         $images = explode("::::", $res[$i]->res_image);
         $imgs = array();
         $imgsa = array();
         foreach ($images as $key => $image) {
            $imgs = base_url('uploads/') . $image;

            array_push($imgsa, $imgs);
         }

         $res[$i]->all_image = $imgsa;

         $imgsb = array();
         $imgsab = array();
         foreach ($images as $key => $image) {
            $imgsb['res_imag' . $key] = base_url('uploads/') . $image;
         }

         $res[$i]->res_image = $imgsb;
         if ($res[$i]->res_video == "") {
            $res[$i]->res_video = "";
         } else {
            $res[$i]->res_video = base_url() . 'uploads/' . $res[$i]->res_video;
         }
         $res[$i]->logo = base_url() . 'uploads/' . $res[$i]->logo;
         $catnm = $this->db->get_where('categories', array('id' => $res[$i]->cat_id), 1)->row();
         if (!empty($catnm)) {
            $res[$i]->c_name = $catnm->c_name;
         } else {
            $res[$i]->c_name = "";
         }

         if ($res[$i]->mfo == "") {
            $res[$i]->mfo = "";
         } else {
            $res[$i]->mfo = $res[$i]->mfo;
         }

         $resid = $res[$i]->res_id;
         $querycount = $this->db->query("SELECT A.rev_id,A.rev_res,A.rev_user,A.rev_stars,A.rev_text,A.rev_date, B.username,B.profile_pic FROM reviews A, user B WHERE A.rev_res = '$resid' AND A.rev_user = B.id ORDER BY A.rev_id DESC");
         $mcount = $querycount->num_rows();

         $res[$i]->review_count = "$mcount";

         $vid = $res[$i]->vid;
         $vendor = $this->db->get_where("vendor", array("id" => $vid))->row();

         if (!empty($vendor)) {
            $res[$i]->vendor_name = $vendor->uname;
         } else {
            $res[$i]->vendor_name = "";
         }

         $reviewsa = array();
         //$reviews = array();
         // $reviewsp = $this->front_model->get_rev_by_id_res($res[$i]->res_id);

         $res_id = $res[$i]->res_id;

         $query = $this->db->query("SELECT A.rev_id,A.rev_res,A.rev_user,A.rev_stars,A.rev_text,A.rev_date, B.username,B.profile_pic FROM reviews A, user B WHERE A.rev_res = '$res_id' AND A.rev_user = B.id ORDER BY A.rev_id DESC");

         $reviewsp = $query->result();

         for ($iac = 0; $iac < count($reviewsp); $iac++) {
            $res_id = $reviewsp[$iac]->rev_res;
            $user_id = $reviewsp[$iac]->rev_user;

            $restaurantac = $this->front_model->get_res_by_id($res_id);
            $userac = $this->front_model->get_rev_by_id_user($user_id);

            $reviewsp[$iac]->rev_res = $restaurantac->res_name;
            $rev_user_data = $this->db->get_where("user", array("id" => $reviewsp[$iac]->rev_user))->row();
            if ($rev_user_data) {
               if ($rev_user_data->profile_pic) {
                  $rev_user_data->profile_pic = base_url("uploads/profile_pics/" . $rev_user_data->profile_pic);
               }
            }
            $reviewsp[$iac]->rev_user_data = $rev_user_data;

            if (empty($userac->username)) {
               $username = "";
            } else {
               $username = $userac->username;
            }

            $reviewsp[$iac]->rev_user = $username;

            // array_push($reviewsa,$reviewsp);
         }

         $res[$i]->reviews = $reviewsp;
      }



      $result['restaurants'] = $res;
      //$result['restaurant']->c_name = $catnm->c_name;

      echo json_encode($result);
   }

   public function get_res_details()
   {
      $result = array();
      header('Content-Type: application/json');

      if (isset($_POST['res_id'])) {

         $res_id = $this->input->post('res_id');
         $res = $this->db->get_where('restaurants', array('res_id' => $res_id), 1);

         if ($res->num_rows() > 0) {

            $images = explode('::::', $res->row()->res_image);
            $imgs = array();
            $imgsa = array();
            foreach ($images as $key => $image) {
               $imgs = base_url('uploads/') . $image;

               array_push($imgsa, $imgs);
            }

            $imgsb = array();
            $imgsab = array();
            foreach ($images as $key => $image) {
               $imgsb['res_imag' . $key] = base_url('uploads/') . $image;
            }

            // $reviews = $this->front_model->get_rev_by_id_res($res_id);

            $query = $this->db->query("SELECT A.rev_id,A.rev_res,A.rev_user,A.rev_stars,A.rev_text,A.rev_date, B.username,B.profile_pic FROM reviews A, user B WHERE A.rev_res = '$res_id' AND A.rev_user = B.id ORDER BY A.rev_id DESC");

            $reviews = $query->result();

            for ($i = 0; $i < count($reviews); $i++) {
               $res_id = $reviews[$i]->rev_res;
               $user_id = $reviews[$i]->rev_user;

               $restaurant = $this->front_model->get_res_by_id($res_id);
               $user = $this->front_model->get_rev_by_id_user($user_id);

               $reviews[$i]->rev_res = $restaurant->res_name;
               $rev_user_data = $this->db->get_where("user", array("id" => $reviews[$i]->rev_user))->row();
               if ($rev_user_data) {
                  if ($rev_user_data->profile_pic) {
                     $rev_user_data->profile_pic = base_url("uploads/profile_pics/" . $rev_user_data->profile_pic);
                  }
               }
               $reviews[$i]->rev_user_data = $rev_user_data;

               if (empty($user->username)) {
                  $username = "";
               } else {
                  $username = $user->username;
               }

               $reviews[$i]->rev_user = $username;
            }

            $cname_get = $res->row();
            $catnm = $this->db->get_where('categories', array('id' => $cname_get->cat_id), 1)->row();
            if (!empty($catnm)) {
               $c_name = $catnm->c_name;
            } else {
               $c_name = "";
            }
            //$timmings = json_decode($res->row()->timmings);
            
            $vid = $res->row()->vid;
            $vendor = $this->db->get_where('vendor', array('id' => $vid), 1)->row();

            if (empty($vendor->uname)) {
                $v_username = "";
            } else {
                $v_username = $vendor->uname;
            }

            if (!empty($vendor->profile_image)) {
                $url = explode(":", $vendor->profile_image);
                if ($url[0] == "https" || $url[0] == "http") {
                    $v_profile = $vendor->profile_image;
                } elseif (!empty($vendor->profile_image)) {
                    $v_profile = base_url() . "uploads/profile_pics/" . $vendor->profile_image;
                } else {
                    $v_profile = $vendor->profile_image;
                }
            } else {
                $v_profile = "";
            }


            $result['status'] = 1;
            $result['msg'] = "Restaurnat Found";
            $result['restaurant'] = $res->row();
            $result['restaurant']->res_image = $imgsb;
            $result['restaurant']->all_image = $imgsa;
            //$result['restaurant']->timmings = $timmings;
            $result['restaurant']->c_name = $c_name;
            if ($result['restaurant']->res_video == "") {
               $result['restaurant']->res_video = "";
            } else {
               $result['restaurant']->res_video = base_url() . 'uploads/' . $result['restaurant']->res_video;
            }
            $result['restaurant']->logo = base_url() . 'uploads/' . $result['restaurant']->logo;
            
            $result['restaurant']->v_username = $v_username;
            $result['restaurant']->v_profile = $v_profile;

            $result['review'] = $reviews;

            echo json_encode($result);
         } else {
            $result['status'] = 0;
            $result['msg'] = "Restaurnat not Found";

            echo json_encode($result);
         }
      }
   }

   public function calcAverageRating($ratings)
   {

      $totalWeight = 0;
      $totalReviews = 0;

      foreach ($ratings as $weight => $numberofReviews) {
         $WeightMultipliedByNumber = $weight * $numberofReviews;
         $totalWeight += $WeightMultipliedByNumber;
         $totalReviews += $numberofReviews;
      }

      if ($totalReviews == 0) {
         $totalReviews = 1;
      } else {
         $totalReviews = $totalReviews;
      }
      //divide the total weight by total number of reviews
      //   $averageRating = number_format(($totalWeight / $totalReviews), 2);

      $averageRating = round(($totalWeight / $totalReviews), 2);

      return $averageRating;
   }

    public function give_review()
    {

        $result = array();
        header('Content-Type: application/json');
        
        if (isset($_POST['user_id']) && isset($_POST['res_id']) && isset($_POST['ratings']) && isset($_POST['text'])) {
            $data = array();
            $data['rev_user'] = $this->input->post('user_id');
            $data['rev_res'] = $this->input->post('res_id');
            $data['rev_stars'] = $this->input->post('ratings');
            $data['rev_text'] = $this->input->post('text');
            $data['rev_date'] = time();
            
            $reviews = $this->db->get_where('reviews', array('rev_user' => $data['rev_user'], 'rev_res' => $data['rev_res']), 1)->row();
            if (!empty($reviews)) {

                $u_data['rev_stars'] = $this->input->post('ratings');
                $u_data['rev_text'] = $this->input->post('text');
    
                $this->db->where('rev_user', $data['rev_user']);
                $this->db->where('rev_res', $data['rev_res']);
                $this->db->update('reviews', $u_data);
    
                // Get all reviews
                $reviews = $this->front_model->get_res_reviews($data['rev_res']);
    
                // make array
                $ratings = array(
                   '0' => $this->db->get_where('reviews', array("rev_res" => $data['rev_res'], "rev_stars" => "0"))->num_rows(),
                   '1.0' => $this->db->get_where('reviews', array("rev_res" => $data['rev_res'], "rev_stars" => "1.0"))->num_rows(),
                   '1.5' => $this->db->get_where('reviews', array("rev_res" => $data['rev_res'], "rev_stars" => "1.5"))->num_rows(),
                   '2.0' => $this->db->get_where('reviews', array("rev_res" => $data['rev_res'], "rev_stars" => "2.0"))->num_rows(),
                   '2.5' => $this->db->get_where('reviews', array("rev_res" => $data['rev_res'], "rev_stars" => "2.5"))->num_rows(),
                   '3.0' => $this->db->get_where('reviews', array("rev_res" => $data['rev_res'], "rev_stars" => "3.0"))->num_rows(),
                   '3.5' => $this->db->get_where('reviews', array("rev_res" => $data['rev_res'], "rev_stars" => "3.5"))->num_rows(),
                   '4.0' => $this->db->get_where('reviews', array("rev_res" => $data['rev_res'], "rev_stars" => "4.0"))->num_rows(),
                   '4.5' => $this->db->get_where('reviews', array("rev_res" => $data['rev_res'], "rev_stars" => "4.5"))->num_rows(),
                   '5.0' => $this->db->get_where('reviews', array("rev_res" => $data['rev_res'], "rev_stars" => "5.0"))->num_rows(),
                );
    
                // calculate reviews
                $rat_data['res_ratings'] = $this->calcAverageRating($ratings);
                $this->db->where('res_id', $data['rev_res']);
                $this->db->update('restaurants', $rat_data);
    
                $result['status'] = 1;
                $result['msg'] = "Review Given";
                $result['preview'] = $rat_data['res_ratings'];
    
                echo json_encode($result);
            } else {

                if ($this->db->insert('reviews', $data)) {
    
                   // Get all reviews
                   $reviews = $this->front_model->get_res_reviews($data['rev_res']);
    
                   // make array
                   $ratings = array(
                      '0' => $this->db->get_where('reviews', array("rev_res" => $data['rev_res'], "rev_stars" => "0"))->num_rows(),
                      '1.0' => $this->db->get_where('reviews', array("rev_res" => $data['rev_res'], "rev_stars" => "1.0"))->num_rows(),
                      '1.5' => $this->db->get_where('reviews', array("rev_res" => $data['rev_res'], "rev_stars" => "1.5"))->num_rows(),
                      '2.0' => $this->db->get_where('reviews', array("rev_res" => $data['rev_res'], "rev_stars" => "2.0"))->num_rows(),
                      '2.5' => $this->db->get_where('reviews', array("rev_res" => $data['rev_res'], "rev_stars" => "2.5"))->num_rows(),
                      '3.0' => $this->db->get_where('reviews', array("rev_res" => $data['rev_res'], "rev_stars" => "3.0"))->num_rows(),
                      '3.5' => $this->db->get_where('reviews', array("rev_res" => $data['rev_res'], "rev_stars" => "3.5"))->num_rows(),
                      '4.0' => $this->db->get_where('reviews', array("rev_res" => $data['rev_res'], "rev_stars" => "4.0"))->num_rows(),
                      '4.5' => $this->db->get_where('reviews', array("rev_res" => $data['rev_res'], "rev_stars" => "4.5"))->num_rows(),
                      '5.0' => $this->db->get_where('reviews', array("rev_res" => $data['rev_res'], "rev_stars" => "5.0"))->num_rows(),
                   );
    
                   // calculate reviews
                   $rat_data['res_ratings'] = $this->calcAverageRating($ratings);
                   $this->db->where('res_id', $data['rev_res']);
                   $this->db->update('restaurants', $rat_data);
    
                   $result['status'] = 1;
                   $result['msg'] = "Review Given";
                   $result['preview'] = $rat_data['res_ratings'];
    
                   echo json_encode($result);
                } else {
                   $result['status'] = 0;
                   $result['msg'] = "Database Error";
    
                   echo json_encode($result);
                }
            }
        } else {
            $result['status'] = 0;
            $result['msg'] = "Missing Data";
            
            echo json_encode($result);
        }
    }

    public function reviews_product()
    {

        $result = array();
        header('Content-Type: application/json');
        
        if (isset($_POST['user_id']) && isset($_POST['product_id']) && isset($_POST['ratings']) && isset($_POST['text'])) {
            $data = array();
            $data['rev_user'] = $this->input->post('user_id');
            $data['rev_pro'] = $this->input->post('product_id');
            $data['rev_stars'] = $this->input->post('ratings');
            $data['rev_text'] = $this->input->post('text');
            $data['rev_date'] = time();
            
            
            $reviews_product = $this->db->get_where('reviews_product', array('rev_user' => $data['rev_user'], 'rev_pro' => $data['rev_pro']), 1)->row();
            
            if (!empty($reviews_product)) {

                $u_data['rev_stars'] = $this->input->post('ratings');
                $u_data['rev_text'] = $this->input->post('text');
    
                $this->db->where('rev_user', $data['rev_user']);
                $this->db->where('rev_pro', $data['rev_pro']);
                $this->db->update('reviews_product', $u_data);
    
                // Get all reviews
                $reviews = $this->front_model->get_pro_reviews($data['rev_pro']);
    
                // make array
                $ratings = array(
                   '0' => $this->db->get_where('reviews_product', array("rev_pro" => $data['rev_pro'], "rev_stars" => "0"))->num_rows(),
                   '1.0' => $this->db->get_where('reviews_product', array("rev_pro" => $data['rev_pro'], "rev_stars" => "1.0"))->num_rows(),
                   '1.5' => $this->db->get_where('reviews_product', array("rev_pro" => $data['rev_pro'], "rev_stars" => "1.5"))->num_rows(),
                   '2.0' => $this->db->get_where('reviews_product', array("rev_pro" => $data['rev_pro'], "rev_stars" => "2.0"))->num_rows(),
                   '2.5' => $this->db->get_where('reviews_product', array("rev_pro" => $data['rev_pro'], "rev_stars" => "2.5"))->num_rows(),
                   '3.0' => $this->db->get_where('reviews_product', array("rev_pro" => $data['rev_pro'], "rev_stars" => "3.0"))->num_rows(),
                   '3.5' => $this->db->get_where('reviews_product', array("rev_pro" => $data['rev_pro'], "rev_stars" => "3.5"))->num_rows(),
                   '4.0' => $this->db->get_where('reviews_product', array("rev_pro" => $data['rev_pro'], "rev_stars" => "4.0"))->num_rows(),
                   '4.5' => $this->db->get_where('reviews_product', array("rev_pro" => $data['rev_pro'], "rev_stars" => "4.5"))->num_rows(),
                   '5.0' => $this->db->get_where('reviews_product', array("rev_pro" => $data['rev_pro'], "rev_stars" => "5.0"))->num_rows(),
                );
    
                // calculate reviews
                $rat_data['pro_ratings'] = $this->calcAverageRating($ratings);
    
                $this->db->where('product_id', $data['rev_pro']);
                $this->db->update('products', $rat_data);
    
                $result['status'] = "1";
                $result['msg'] = "Review Given";
                $result['preview'] = $data['rev_stars'];
    
                echo json_encode($result);
            } else {

                if ($this->db->insert('reviews_product', $data)) {
    
                   // Get all reviews
                   $reviews = $this->front_model->get_pro_reviews($data['rev_pro']);
    
                   // make array
                   $ratings = array(
                      '0' => $this->db->get_where('reviews_product', array("rev_pro" => $data['rev_pro'], "rev_stars" => "0"))->num_rows(),
                      '1.0' => $this->db->get_where('reviews_product', array("rev_pro" => $data['rev_pro'], "rev_stars" => "1.0"))->num_rows(),
                      '1.5' => $this->db->get_where('reviews_product', array("rev_pro" => $data['rev_pro'], "rev_stars" => "1.5"))->num_rows(),
                      '2.0' => $this->db->get_where('reviews_product', array("rev_pro" => $data['rev_pro'], "rev_stars" => "2.0"))->num_rows(),
                      '2.5' => $this->db->get_where('reviews_product', array("rev_pro" => $data['rev_pro'], "rev_stars" => "2.5"))->num_rows(),
                      '3.0' => $this->db->get_where('reviews_product', array("rev_pro" => $data['rev_pro'], "rev_stars" => "3.0"))->num_rows(),
                      '3.5' => $this->db->get_where('reviews_product', array("rev_pro" => $data['rev_pro'], "rev_stars" => "3.5"))->num_rows(),
                      '4.0' => $this->db->get_where('reviews_product', array("rev_pro" => $data['rev_pro'], "rev_stars" => "4.0"))->num_rows(),
                      '4.5' => $this->db->get_where('reviews_product', array("rev_pro" => $data['rev_pro'], "rev_stars" => "4.5"))->num_rows(),
                      '5.0' => $this->db->get_where('reviews_product', array("rev_pro" => $data['rev_pro'], "rev_stars" => "5.0"))->num_rows(),
                   );
    
                   // calculate reviews
                   $rat_data['pro_ratings'] = $this->calcAverageRating($ratings);
                   // $rat_data['pro_ratings'] =  $data['rev_stars'];
    
                   $this->db->where('product_id', $data['rev_pro']);
                   $this->db->update('products', $rat_data);
    
    
                   $result['status'] = 1;
                   $result['msg'] = "Review Given";
                   $result['preview'] = $data['rev_stars'];
                   // $result['preview'] = $rat_data['pro_ratings'];
    
                   echo json_encode($result);
                } else {
                   $result['status'] = 0;
                   $result['msg'] = "Database Error";
    
                   echo json_encode($result);
                }
            }
        } else {
            $result['status'] = 0;
            $result['msg'] = "Missing Data";
            
            echo json_encode($result);
        }
    }

   public function unlike()
   {
      header('Content-Type: application/json');

      $res_id = $this->input->post('res_id');
      $user_id = $this->input->post('user_id');

      if ($this->front_model->unlike($res_id, $user_id)) {
         $result['status'] = "1";
         $result['msg'] = "Successfully Unlike";

         echo json_encode($result);
      } else {
         $result['status'] = "0";
         $result['msg'] = "Something Wrong";

         echo json_encode($result);
      }
   }

   public function get_cat_res()
   {
      header('Content-Type: application/json');
      if (isset($_POST['cat_id'])) {
         $cat_id = $this->input->post('cat_id');
         $likes = $this->db->select('restaurants.*')
            ->from('restaurants')
            ->where(array('cat_id' => $cat_id))
            ->order_by('res_ratings', 'DESC')
            ->get();

         if ($likes->num_rows() > 0) {

            foreach ($likes->result() as $restaurant) {
               //$restaurant->res_image = base_url().'uploads/'.$restaurant->res_image;
               $restaurant->res_create_date = gmdate('d M Y', $restaurant->res_create_date);
               $catnm = $this->db->get_where('categories', array('id' => $restaurant->cat_id), 1)->row();
               $restaurant->c_name = $catnm->c_name;

               $images = explode("::::", $restaurant->res_image);
               $imgs = array();
               $imgsa = array();
               foreach ($images as $key => $image) {
                  $imgs = base_url('uploads/') . $image;

                  array_push($imgsa, $imgs);
               }

               $restaurant->all_image = $imgsa;

               $imgsb = array();
               $imgsab = array();
               foreach ($images as $key => $image) {
                  $imgsb['res_imag' . $key] = base_url('uploads/') . $image;
               }

               $restaurant->res_image = $imgsb;

               $restaurant->logo = base_url() . 'uploads/' . $restaurant->logo;
               if ($restaurant->res_video == "") {
                  $restaurant->res_video = "";
               } else {
                  $restaurant->res_video = base_url() . 'uploads/' . $restaurant->res_video;
               }
               $resid = $restaurant->res_id;
               $querycount = $this->db->query("SELECT A.rev_id,A.rev_res,A.rev_user,A.rev_stars,A.rev_text,A.rev_date, B.username,B.profile_pic FROM reviews A, user B WHERE A.rev_res = '$resid' AND A.rev_user = B.id ORDER BY A.rev_id DESC");
               $mcount = $querycount->num_rows();

               $restaurant->review_count = "$mcount";

               $vid = $restaurant->vid;
               $vendor = $this->db->get_where("vendor", array("id" => $vid))->row();

               if (!empty($vendor)) {
                  $restaurant->vendor_name = $vendor->uname;
               } else {
                  $restaurant->vendor_name = "";
               }

               if ($restaurant->mfo == "") {
                  $restaurant->mfo = "";
               } else {
                  $restaurant->mfo = $restaurant->mfo;
               }

               $imgsac = array();
               $imgsaca = array();

               $reviewsa = array();
               //$reviews = array();
               // $reviewsp = $this->front_model->get_rev_by_id_res($restaurant->res_id);

               $query = $this->db->query("SELECT A.rev_id,A.rev_res,A.rev_user,A.rev_stars,A.rev_text,A.rev_date, B.username,B.profile_pic FROM reviews A, user B WHERE A.rev_res = '$restaurant->res_id' AND A.rev_user = B.id ORDER BY A.rev_id DESC");
               $reviewsp = $query->result();

               for ($iac = 0; $iac < count($reviewsp); $iac++) {
                  $res_id = $reviewsp[$iac]->rev_res;
                  $user_id = $reviewsp[$iac]->rev_user;

                  $restaurantac = $this->front_model->get_res_by_id($res_id);
                  $userac = $this->front_model->get_rev_by_id_user($user_id);

                  $reviewsp[$iac]->rev_res = $restaurantac->res_name;
                  $rev_user_data = $this->db->get_where("user", array("id" => $reviewsp[$iac]->rev_user))->row();
                  if ($rev_user_data) {
                     if ($rev_user_data->profile_pic) {
                        $rev_user_data->profile_pic = base_url("uploads/profile_pics/" . $rev_user_data->profile_pic);
                     }
                  }
                  $reviewsp[$iac]->rev_user_data = $rev_user_data;

                  if (empty($userac->username)) {
                     $username = "";
                  } else {
                     $username = $userac->username;
                  }

                  $reviewsp[$iac]->rev_user = $username;

                  // array_push($reviewsa,$reviewsp);
               }

               $restaurant->reviews = $reviewsp;
            }

            $result['status'] = 1;
            $result['msg'] = "Restaurants Found";
            $result['restaurants'] = $likes->result();
            echo json_encode($result);
         } else {
            $result['status'] = 0;
            $result['msg'] = "No Restaurants Found";
            $result['restaurants'] = [];
            echo json_encode($result);
         }
      } else {
         $result['status'] = 0;
         $result['msg'] = "Missing Fields";
         echo json_encode($result);
      }
   }

   public function get_scat_res()
   {
      header('Content-Type: application/json');
      if (isset($_POST['scat_id'])) {
         $cat_id = $this->input->post('scat_id');
         $likes = $this->db->select('restaurants.*')
            ->from('restaurants')
            ->where(array('scat_id' => $cat_id))
            ->order_by('res_ratings', 'DESC')
            ->get();

         if ($likes->num_rows() > 0) {

            foreach ($likes->result() as $restaurant) {
               //$restaurant->res_image = base_url().'uploads/'.$restaurant->res_image;
               $restaurant->res_create_date = gmdate('d M Y', $restaurant->res_create_date);
               $catnm = $this->db->get_where('categories', array('id' => $restaurant->cat_id), 1)->row();
               $restaurant->c_name = $catnm->c_name;

               $images = explode("::::", $restaurant->res_image);
               $imgs = array();
               $imgsa = array();
               foreach ($images as $key => $image) {
                  $imgs = base_url('uploads/') . $image;

                  array_push($imgsa, $imgs);
               }

               $restaurant->all_image = $imgsa;

               $imgsb = array();
               $imgsab = array();
               foreach ($images as $key => $image) {
                  $imgsb['res_imag' . $key] = base_url('uploads/') . $image;
               }

               $restaurant->res_image = $imgsb;

               $restaurant->logo = base_url() . 'uploads/' . $restaurant->logo;
               if ($restaurant->res_video == "") {
                  $restaurant->res_video = "";
               } else {
                  $restaurant->res_video = base_url() . 'uploads/' . $restaurant->res_video;
               }
               $resid = $restaurant->res_id;
               $querycount = $this->db->query("SELECT * FROM reviews WHERE rev_res = '$resid'");
               $mcount = $querycount->num_rows();

               $restaurant->review_count = $mcount;

               $imgsac = array();
               $imgsaca = array();
               $producta = unserialize($restaurant->structure);
               if (!empty($producta)) {

                  for ($ja = 0; $ja < count($producta); $ja++) {
                     $fee_details_ida = $producta[$ja];
                     $explodea = explode(',', $fee_details_ida);

                     $imgsaca['type'] = $explodea[0];

                     $imgsaca['type_name'] = $this->db->get_where('type', array('id' => $imgsaca['type']))->row()->c_name;

                     $imgsaca['price'] = $explodea[1];
                     array_push($imgsac, $imgsaca);
                  }
                  $restaurant->type = $imgsac;
               } else {
                  $restaurant->type = array();
               }

               $reviewsa = array();
               //$reviews = array();
               $reviewsp = $this->front_model->get_rev_by_id_res($restaurant->res_id);

               for ($iac = 0; $iac < count($reviewsp); $iac++) {
                  $res_id = $reviewsp[$iac]->rev_res;
                  $user_id = $reviewsp[$iac]->rev_user;

                  $restaurantac = $this->front_model->get_res_by_id($res_id);
                  $userac = $this->front_model->get_rev_by_id_user($user_id);

                  $reviewsp[$iac]->rev_res = $restaurantac->res_name;
                  $rev_user_data = $this->db->get_where("user", array("id" => $reviewsp[$iac]->rev_user))->row();
                  if ($rev_user_data) {
                     if ($rev_user_data->profile_pic) {
                        $rev_user_data->profile_pic = base_url("uploads/profile_pics/" . $rev_user_data->profile_pic);
                     }
                  }
                  $reviewsp[$iac]->rev_user_data = $rev_user_data;

                  if (empty($userac->username)) {
                     $username = "";
                  } else {
                     $username = $userac->username;
                  }

                  $reviewsp[$iac]->rev_user = $username;

                  // array_push($reviewsa,$reviewsp);
               }

               $restaurant->reviews = $reviewsp;
            }

            $result['status'] = 1;
            $result['msg'] = "Restaurants Found";
            $result['restaurants'] = $likes->result();
            echo json_encode($result);
         } else {
            $result['status'] = 0;
            $result['msg'] = "No Restaurants Found";
            echo json_encode($result);
         }
      } else {
         $result['status'] = 0;
         $result['msg'] = "Missing Fields";
         echo json_encode($result);
      }
   }

   public function get_text()
   {
      $result = array();
      header('Content-Type: application/json');

      $result['status'] = 1;
      $result['msg'] = "welcome text";
      $res = $this->db->get('wc_text')->result();

      $result['text'] = $res;
      //$result['restaurant']->c_name = $catnm->c_name;

      echo json_encode($result);
   }

   public function contact_url()
   {
      $result = array();
      header('Content-Type: application/json');

      $result['status'] = 1;
      $result['msg'] = "contact url";
      $res = $this->db->get('weblink')->result();

      $result['text'] = $res;
      //$result['restaurant']->c_name = $catnm->c_name;

      echo json_encode($result);
   }


   public function add_restaurant()
   {
      if (isset($_POST['name']) && isset($_POST['description']) && isset($_POST['address'])) {

         $res_image = array();
         $res_video = "";
         $logo = "";

         if ($_FILES['res_image']['name'] != "") {
            //echo "image detected";
            if (is_array($_FILES['res_image']['name'])) {
               $filesCount = count($_FILES['res_image']['name']);
               for ($i = 0; $i < $filesCount; $i++) {
                  $_FILES['file']['name']     = $_FILES['res_image']['name'][$i];
                  $_FILES['file']['type']     = $_FILES['res_image']['type'][$i];
                  $_FILES['file']['tmp_name'] = $_FILES['res_image']['tmp_name'][$i];
                  $_FILES['file']['error']     = $_FILES['res_image']['error'][$i];
                  $_FILES['file']['size']     = $_FILES['res_image']['size'][$i];

                  // File upload configuration
                  $config['upload_path'] = './uploads';
                  $config['allowed_types'] = 'gif|jpg|png|jpeg';
                  $config['file_name'] = uniqid();
                  $config['overwrite'] = TRUE;


                  // Load and initialize upload library
                  $this->load->library('upload');
                  $this->upload->initialize($config);

                  // Upload file to server
                  if ($this->upload->do_upload('file')) {
                     // Uploaded file data
                     $fileData = $this->upload->data();
                     array_push($res_image, $fileData['file_name']);
                     //$res_image = $fileData['file_name'];

                  } else {
                     $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));

                     $temp["response_code"] = "0";
                     $temp["message"] = $error['error'];
                     $temp["status"] = "failure";
                     echo json_encode($temp);
                  }
               }
            }
         }

         if (isset($_FILES['logo']['name']) != "") {
            $image_exts = array("jpeg", "jpg", "jpeg", "png");

            $configVideo['upload_path'] = './uploads'; # check path is correct
            $configVideo['max_size'] = '102400';
            $configVideo['allowed_types'] = $image_exts; # add video extenstion on here
            $configVideo['overwrite'] = TRUE;
            $configVideo['remove_spaces'] = TRUE;
            $configVideo['file_name'] = uniqid();

            $this->load->library('upload');
            $this->upload->initialize($config);

            if (!$this->upload->do_upload('logo')) # form input field attribute
            {
               $temp["response_code"] = "0";
               $temp["message"] = "Image Type Error";
               $temp["status"] = "failure";
               echo json_encode($temp);
            } else {
               # Upload Successfull
               $fileData = $this->upload->data();
               $logo = $fileData['file_name'];
            }
         }


         // if($_FILES['res_video']['name'] != "") {
         if (isset($_FILES['res_video']['name']) && $_FILES['res_video']['name'] != "") {
            // File upload configuration
            $config['upload_path'] = './uploads';
            $config['allowed_types'] = 'mp4|mkv';
            $config['file_name'] = uniqid();
            $config['overwrite'] = TRUE;


            // Load and initialize upload library
            $this->load->library('upload');
            $this->upload->initialize($config);

            // Upload file to server
            if ($this->upload->do_upload('res_video')) {
               // Uploaded file data
               $fileData = $this->upload->data();
               $res_video = $fileData['file_name'];
            } else {
               $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
               $temp["response_code"] = "0";
               $temp["message"] = $error['error'];
               $temp["status"] = "failure";
               echo json_encode($temp);
            }
         }

         $data['res_name'] = $this->input->post('name');
         $data['res_desc'] = $this->input->post('description');


         if ($this->input->post('website')) {
            $data['res_website'] = $this->input->post('website');
         }
         $data['res_address'] = $this->input->post('address');
         $data['res_phone'] = $this->input->post('phone');
         // $data['res_email'] = $this->input->post('res_email');

         $data['cat_id'] = $this->input->post('cat_id');
         // $data['status'] = $this->input->post('status');

         $data['mfo'] = $this->input->post('otime_mon');

         $address = $this->input->post('address');
         $lat =  0;
         $long = 0;

         $address = str_replace(',,', ',', $address);
         $address = str_replace(', ,', ',', $address);

         $address = str_replace(" ", "+", $address);

         $json = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . $address . '&key=AIzaSyCqQW9tN814NYD_MdsLIb35HRY65hHomco');
         $json1 = json_decode($json);

         if (isset($json1->results)) {

            $lat = ($json1->{'results'}[0]->{'geometry'}->{'location'}->{'lat'});
            $long = ($json1->{'results'}[0]->{'geometry'}->{'location'}->{'lng'});
         }


         // $data['lat'] = $this->input->post('lat');
         // $data['lon'] = $this->input->post('lon');

         $data['lat'] = $lat;
         $data['lon'] = $long;

         $data['vid'] = $this->input->post('vid');

         $data['res_image'] = implode('::::', $res_image);
         $data['res_video'] = $res_video;
         // $data['res_url'] = $this->input->post('res_url');
         // $data['logo'] = implode('::::', $logo);
         $data['logo'] = $logo;
         $data['res_isOpen'] = 'open';
         $data['res_status'] = 'active';

         $data['monday_from'] = $this->input->post('monday_from');
         $data['monday_to'] = $this->input->post('monday_to');
         $data['tuesday_from'] = $this->input->post('tuesday_from');
         $data['tuesday_to'] = $this->input->post('tuesday_to');
         $data['wednesday_from'] = $this->input->post('wednesday_from');
         $data['wednesday_to'] = $this->input->post('wednesday_to');
         $data['thursday_from'] = $this->input->post('thursday_from');
         $data['thursday_to'] = $this->input->post('thursday_to');
         $data['friday_from'] = $this->input->post('friday_from');
         $data['friday_to'] = $this->input->post('friday_to');
         $data['saturday_from'] = $this->input->post('saturday_from');
         $data['saturday_to'] = $this->input->post('saturday_to');
         $data['sunday_from'] = $this->input->post('sunday_from');
         $data['sunday_to'] = $this->input->post('sunday_to');

         $data['res_create_date'] = time();

         if ($this->front_model->add_restaurants($data)) {

            $temp["response_code"] = "1";
            $temp["message"] = "success";
            $temp["status"] = "success";
            echo json_encode($temp);
         } else {

            $temp["response_code"] = "0";
            $temp["message"] = "Database Error";
            $temp["status"] = "failure";
            echo json_encode($temp);
         }
      } else {

         $temp["response_code"] = "0";
         $temp["message"] = "Enter Data";
         $temp["status"] = "failure";
         echo json_encode($temp);
      }
   }

   public function edit_restaurant()
   {

      if (isset($_POST['name']) && isset($_POST['description']) && isset($_POST['address'])) {

         $res_image = array();
         $res_video = "";
         $logo = array();
         $product_image = array();

         $list_image = array();
         $res_id = $this->input->post('res_id');
         $restaurant = $this->front_model->get_restaurant_by_id($res_id);
         // if($_FILES['res_image']['name'][0] !="") {
         if (isset($_FILES['res_image']['name'][0]) && $_FILES['res_image']['name'][0] != "") {
            //echo "image detected";
            if (is_array($_FILES['res_image']['name'])) {
               $filesCount = count($_FILES['res_image']['name']);
               for ($i = 0; $i < $filesCount; $i++) {
                  $_FILES['file']['name']     = $_FILES['res_image']['name'][$i];
                  $_FILES['file']['type']     = $_FILES['res_image']['type'][$i];
                  $_FILES['file']['tmp_name'] = $_FILES['res_image']['tmp_name'][$i];
                  $_FILES['file']['error']     = $_FILES['res_image']['error'][$i];
                  $_FILES['file']['size']     = $_FILES['res_image']['size'][$i];

                  // File upload configuration
                  $config['upload_path'] = './uploads';
                  $config['allowed_types'] = 'gif|jpg|png|jpeg';
                  $config['file_name'] = uniqid();
                  $config['overwrite'] = TRUE;


                  // Load and initialize upload library
                  $this->load->library('upload');
                  $this->upload->initialize($config);

                  // Upload file to server
                  if ($this->upload->do_upload('file')) {
                     // Uploaded file data
                     $fileData = $this->upload->data();
                     array_push($product_image, $fileData['file_name']);
                     //$res_image = $fileData['file_name'];

                  } else {
                     $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));

                     $temp["response_code"] = "0";
                     $temp["message"] = $error['error'];
                     $temp["status"] = "failure";
                     echo json_encode($temp);
                  }
               }

               $image_list = $this->input->post('image_list');
               // 	$url = explode("http://localhost/eshield_multivendor/uploads/", $image_list);
            //   $url = explode("https://primocysapp.com/eshield_multivendor/uploads/", $image_list);
               $url = explode("https://eshield.theprimoapp.com/uploads/", $image_list);

               foreach ($url as $key => $list) {

                  $url_li = explode(",", $list);

                  foreach ($url_li as $key => $img_list) {
                     array_push($product_image, $img_list);
                  }
               }
               $result = array_merge($product_image, $list_image);

               foreach ($product_image as $key => $list) {
                  if (!empty($list)) {
                     array_push($list_image, $list);
                  }
               }
               // $data['product_image'] = implode("::::", $list_image);
               $data['res_image'] = implode("::::", $list_image);
            }
         }

         if (!empty($restaurant->res_video)) {
            $res_video = $restaurant->res_video;
         } else {
            $res_video = "";
         }

         // if($_FILES['res_video']['name'] !="") {
         if (isset($_FILES['res_video']['name']) && $_FILES['res_video']['name'] != "") {
            // File upload configuration
            $config['upload_path'] = './uploads';
            $config['allowed_types'] = 'mp4|mkv';
            $config['file_name'] = uniqid();
            $config['overwrite'] = TRUE;

            // Load and initialize upload library
            $this->load->library('upload');
            $this->upload->initialize($config);

            // Upload file to server
            if ($this->upload->do_upload('res_video')) {
               // Uploaded file data
               $fileData = $this->upload->data();
               $res_video = $fileData['file_name'];
            } else {
               $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
               $temp["response_code"] = "0";
               $temp["message"] = $error['error'];
               $temp["status"] = "failure";
               echo json_encode($temp);
            }
         }

         // $address = $this->input->post('address');

         $data['res_name'] = $this->input->post('name');
         $data['res_desc'] = $this->input->post('description');

         if ($this->input->post('website')) {
            $data['res_website'] = $this->input->post('website');
         }
         $data['res_phone'] = $this->input->post('phone');
         // $data['res_email'] = $this->input->post('res_email');

         $data['cat_id'] = $this->input->post('cat_id');
         $data['status'] = $this->input->post('status');

         // $data['mfo'] = $this->input->post('otime_mon');
         $data['res_url'] = $this->input->post('res_url');

         $data['monday_from'] = $this->input->post('monday_from');
         $data['monday_to'] = $this->input->post('monday_to');
         $data['tuesday_from'] = $this->input->post('tuesday_from');
         $data['tuesday_to'] = $this->input->post('tuesday_to');
         $data['wednesday_from'] = $this->input->post('wednesday_from');
         $data['wednesday_to'] = $this->input->post('wednesday_to');
         $data['thursday_from'] = $this->input->post('thursday_from');
         $data['thursday_to'] = $this->input->post('thursday_to');
         $data['friday_from'] = $this->input->post('friday_from');
         $data['friday_to'] = $this->input->post('friday_to');
         $data['saturday_from'] = $this->input->post('saturday_from');
         $data['saturday_to'] = $this->input->post('saturday_to');
         $data['sunday_from'] = $this->input->post('sunday_from');
         $data['sunday_to'] = $this->input->post('sunday_to');

         // $data['original_price'] = $this->input->post('original_price');
         // $data['bid_closes_in'] = $this->input->post('bid_closes_in');
         // $data['buyout_price'] = $this->input->post('buyout_price');

         $address = $this->input->post('address');
         $lat =  0;
         $long = 0;

         $address = str_replace(',,', ',', $address);
         $address = str_replace(', ,', ',', $address);

         $address = str_replace(" ", "+", $address);

         $json = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . $address . '&key=AIzaSyCqQW9tN814NYD_MdsLIb35HRY65hHomco');
         $json1 = json_decode($json);

         if (isset($json1->results)) {

            $lat = ($json1->{'results'}[0]->{'geometry'}->{'location'}->{'lat'});
            $long = ($json1->{'results'}[0]->{'geometry'}->{'location'}->{'lng'});
         }


         // $data['lat'] = $this->input->post('lat');
         // $data['lon'] = $this->input->post('lon');

         $data['lat'] = $lat;
         $data['lon'] = $long;

         $data['vid'] = $this->input->post('vid');
         $res_id = $this->input->post('res_id');
         // $data['promo_offer']=$this->input->post('promo_offer');
         //$data['res_image'] = implode('::::', $res_image);
         $data['res_video'] = $res_video;

         $data['res_isOpen'] = 'open';
         $data['res_status'] = 'active';
         $data['res_address'] = $this->input->post('address');

         $this->db->where('res_id', $res_id);
         if ($this->db->update('restaurants', $data)) {


            $temp["response_code"] = "1";
            $temp["message"] = "success";
            $temp["status"] = "success";
            echo json_encode($temp);
         } else {

            $temp["response_code"] = "0";
            $temp["message"] = "Database Error";
            $temp["status"] = "failure";
            echo json_encode($temp);
         }
      } else {

         $temp["response_code"] = "0";
         $temp["message"] = "Enter Data";
         $temp["status"] = "failure";
         echo json_encode($temp);
      }
   }

   public function vendor_data()
   {
      header('Content-Type: application/json');
      $id = $this->input->post('vid');
      if (empty($id)) {
         $temp["response_code"] = "0";
         $temp["message"] = "Enter Data";
         $temp["status"] = "failure";
         echo json_encode($temp);
      } else {
         $temp = array();
         $profile = array();
         $profile = $this->front_model->get_vendor($id);

         if (!empty($profile)) {

            $user = array();
            $user['uname'] = $profile->uname;
            $user['email'] = $profile->email;
            $user['mobile'] = $profile->mobile;
            $user['gender'] = $profile->gender;
            $user['date_of_birth'] = $profile->date_of_birth;

            if (!empty($profile->profile_image)) {
               $url = explode(":", $profile->profile_image);
               if ($url[0] == "https" || $url[0] == "http") {
                  $user['profile_image'] = $profile->profile_image;
               } elseif (!empty($profile->profile_image)) {
                  $user['profile_image'] = base_url() . "uploads/profile_pics/" . $profile->profile_image;
               } else {
                  $user['profile_image'] = $profile->profile_image;
               }
            } else {
               $user['profile_image'] = "";
            }
            $user['device_token'] = $profile->device_token;

            $temp["response_code"] = "1";
            $temp["message"] = "Vendor Found";
            $temp['user'] = $user;
            $temp["status"] = "success";
            echo json_encode($temp);
         } else {
            $temp["response_code"] = "0";
            $temp["message"] = "Not Found";
            $temp["status"] = "failure";
            echo json_encode($temp);
         }
      }
   }

   public function get_v_res()
   {
      $result = array();
      header('Content-Type: application/json');
      $vid = $this->input->post('vid');

      //$res = $this->db->get_where('restaurants', array('status' => '1'))->result();

      $services_count = $this->db->query("SELECT * FROM services WHERE v_id='$vid'")->num_rows();
      $booking_count = $this->db->query("SELECT * FROM booking WHERE vid='$vid'")->num_rows();
      $products_count = $this->db->query("SELECT * FROM products WHERE vid='$vid'")->num_rows();

      $res = $this->db->query("SELECT * FROM restaurants WHERE vid='$vid'")->result();
      if (!empty($res)) {

         for ($i = 0; $i < count($res); $i++) {
            //$res[$i]->res_image = base_url().'uploads/'.$res[$i]->res_image;
            $catnm = $this->db->get_where('categories', array('id' => $res[$i]->cat_id), 1)->row();

            if (!empty($catnm)) {
               $res[$i]->c_name = $catnm->c_name;
            } else {
               $res[$i]->c_name = "";
            }

            $images = explode("::::", $res[$i]->res_image);
            $imgs = array();
            $imgsa = array();
            foreach ($images as $key => $image) {
               $imgs = base_url('uploads/') . $image;

               array_push($imgsa, $imgs);
            }

            $imgsb = array();
            $imgsab = array();
            foreach ($images as $key => $image) {
               $imgsb['res_imag' . $key] = base_url('uploads/') . $image;
            }

            $resid = $res[$i]->res_id;
            $querycount = $this->db->query("SELECT * FROM reviews WHERE rev_res = '$resid'");
            $mcount = $querycount->num_rows();

            $res[$i]->review_count = $mcount;

            $querycounta = $this->db->query("SELECT * FROM view_store WHERE store_id = '$resid'");
            $mcounta = $querycounta->num_rows();

            $res[$i]->view_count = $mcounta;

            $res[$i]->all_image = $imgsa;
            $res[$i]->res_image = $imgsb;
            $res[$i]->logo = base_url() . 'uploads/' . $res[$i]->logo;
            if ($res[$i]->res_video == "") {
               $res[$i]->res_video = "";
            } else {
               $res[$i]->res_video = base_url() . 'uploads/' . $res[$i]->res_video;
            }
         }
         $result['status'] = "1";
         $result['msg'] = "Restaurnats Found";
         $result['services_count'] = "$services_count";
         $result['booking_count'] = "$booking_count";
         $result['products_count'] = "$products_count";
         $result['restaurants'] = $res;
      } else {
         $result['status'] = "0";
         $result['msg'] = "Restaurnats Not Found";
         $result['services_count'] = "$services_count";
         $result['booking_count'] = "$booking_count";
         $result['products_count'] = "$products_count";
         $result['restaurants'] = $res;
      }
      //$result['restaurant']->c_name = $catnm->c_name;
      echo json_encode($result);
   }

   public function view_count()
   {
      header('Content-Type: application/json');


      if (!isset($_POST['user_id']) || !isset($_POST['store_id'])) {
         $temp["response_code"] = "0";
         $temp["message"] = "Enter both id";
         $temp["status"] = "failure";
         echo json_encode($temp);
      } else {

         $user_id = $this->input->post('user_id');
         $store_id = $this->input->post('store_id');


         $count = $this->db->get_where('view_store', array('user_id' => $user_id, 'store_id' => $store_id), 1)->num_rows();

         if ($count > 0) {
            $temp["response_code"] = "0";
            $temp["message"] = "Already View";
            $temp["status"] = "failure";
            echo json_encode($temp);
         } else {
            $res = $this->db->get_where('restaurants', array('res_id' => $store_id), 1)->row();

            $user = array(
               'user_id' => $this->input->post('user_id'),
               'store_id' => $this->input->post('store_id'),
               'vid' => $res->vid,
            );

            $update = $this->db->insert('view_store', $user);
            if ($update) {
               $temp["response_code"] = "1";
               $temp["message"] = "Added View";
               $temp["status"] = "success";
               echo json_encode($temp);
            } else {
               $temp["response_code"] = "0";
               $temp["message"] = "database error";
               $temp["status"] = "failure";
               echo json_encode($temp);
            }
         }
      }
   }

    public function filter()
    {
        $result = array();
        header('Content-Type: application/json');
        $category = $this->input->post('category');
        
        if (empty($category)) {
         $temp["response_code"] = "0";
         $temp["message"] = "Missing Data";
         $temp["status"] = "failure";
         echo json_encode($temp);
         return;
      }
      $price = $this->input->post('price');

      $data = array(
         'category' => $category,
         'price' => $price
      );

      if (empty($price)) {
         $services = $this->db->query("SELECT * FROM services WHERE cat_id IN ('" . implode("','", $category) . "')  ORDER BY id DESC")->result();
      } else {

         if ($price == 1) {
            $sql = "Select * from services where cat_id IN (" . implode(',', $category) . ") AND service_price <= 100";
            $query = $this->db->query($sql);
         }

         if ($price == 2) {
            $sql = "Select * from services where cat_id IN (" . implode(',', $category) . ") AND service_price >= 100 AND service_price <= 500";
            $query = $this->db->query($sql);
         }

         if ($price == 3) {
            $sql = "Select * from services where cat_id IN (" . implode(',', $category) . ") AND service_price >= 500 AND service_price <= 1000";
            $query = $this->db->query($sql);
         }

         if ($price == 4) {
            $sql = "Select * from services where cat_id IN (" . implode(',', $category) . ") AND service_price >= 1000";
            $query = $this->db->query($sql);
         }
         $services =  $query->result();
      }

        if (!empty($services)) {

            for ($i = 0; $i < count($services); $i++) {

                $catnm = $this->db->get_where('categories', array('id' => $services[$i]->cat_id), 1)->row();
                if (!empty($catnm)) {
                   $services[$i]->category_name = $catnm->c_name;
                } else {
                   $services[$i]->category_name = "";
                }
    
                $images = explode("::::", $services[$i]->service_image);
                $imgs = array();
                $imgsa = array();
                foreach ($images as $key => $image) {
                   $imgs = base_url('uploads/service_images/') . $image;
    
                   array_push($imgsa, $imgs);
                }
    
                if (!empty($images)) {
                   $services[$i]->service_image = base_url('uploads/service_images/') . $images[0];
                } else {
                   $services[$i]->service_image = "";
                }
    
                $id = $services[$i]->id;
                $querycount = $this->db->query("SELECT A.rev_id,A.rev_service,A.rev_user,A.rev_stars,A.rev_text,A.rev_date, B.username,B.profile_pic FROM services_reviews A, user B WHERE A.rev_service = '$id' AND A.rev_user = B.id ORDER BY A.rev_id DESC");
                $mcount = $querycount->num_rows();
                if (!empty($mcount)) {
                    $services[$i]->review_count = "$mcount";
                }else{
                    $services[$i]->review_count = "0";
                }

                $res_id = $services[$i]->res_id;
                $store = $this->db->get_where('restaurants', array('res_id' => $res_id), 1)->row();
                if (!empty($store)) {
                   $services[$i]->store_name = $store->res_name;
                } else {
                   $services[$i]->store_name = "";
                }
    
                if (!empty($store)) {
                   $services[$i]->store_address = $store->res_address;
                } else {
                   $services[$i]->store_address = "";
                }
    
                if (!empty($store)) {
                   $services[$i]->store_latitude = $store->lat;
                } else {
                   $services[$i]->store_latitude = "";
                }
    
                if (!empty($store)) {
                   $services[$i]->store_longitude = $store->lon;
                } else {
                   $services[$i]->store_longitude = "";
                }
    
                $v_id = $services[$i]->v_id;
                $vendor = $this->db->get_where('vendor', array('id' => $v_id), 1)->row();
                if (!empty($vendor)) {
                   $services[$i]->vendor_name = $vendor->uname;
                } else {
                   $services[$i]->vendor_name = "";
                }
            }

            $result['response_code'] = "1";
            $result['message'] = "servic Found";
            $result['servic'] = $services;
            $result["status"] = "success";
            echo json_encode($result);
        } else {
            $result["response_code"] = "0";
            $result["message"] = "service Not Found";
            $result['servic'] = [];
            $result["status"] = "failure";
            echo json_encode($result);
        }
    }

    public function booking()
    {
        header('Content-Type: application/json');
        
        if (isset($_POST['res_id']) && isset($_POST['user_id']) && isset($_POST['date']) && isset($_POST['slot'])) {

            $like = array();
            $like['vid'] = $this->input->post('vid');
            $like['res_id'] = $this->input->post('res_id');
            $like['service_id'] = $this->input->post('service_id');
            $like['user_id'] = $this->input->post('user_id');
            $like['date'] = $this->input->post('date');
            $like['slot'] = $this->input->post('slot');
            
            $size = $this->input->post('size');
            if (!empty($size)) {
                $like['size'] = $this->input->post('size');
            }else{
                $like['size'] = "0";
            }
         
            $like['notes'] = $this->input->post('notes');
            $like['address'] = $this->input->post('address');
            $like['create_date'] = date("d M, H:i A");
            
            if ($this->db->insert('booking', $like)) {

                $like['booking_id'] = $this->db->insert_id();
                
                $title = "Booking Confirm";
                $message = "Your Booking Successfully Confirm";
                
                $user_id = $this->input->post('user_id');
                $res_id = $this->input->post('res_id');
                $response = $this->firebase_model->send_user_notification($user_id, $title, $message, "order", array());
                $this->firebase_model->save_user_notification($user_id, $title, $message, "booking", $like['booking_id']);
                
                $service_id = $this->input->post('service_id');
                $service = $this->db->get_where('services', array('id' => $service_id), 1)->row();
                
                $user = $this->db->get_where('user', array('id' => $user_id), 1)->row();
                $v_title = "New Booking";
                $v_message = "" . $user->username . " Booking for service " . $service->service_name . ".";
                
                $response = $this->firebase_model->send_vendor_notification($like['vid'], $v_title, $v_message, "Message");
                $this->firebase_model->save_vendor_notification($like['vid'], $v_title, $v_message,  "booking", $like['booking_id']);
                
                $data['status'] = "Confirm";
                $this->db->where('id', $like['booking_id']);
                $this->db->update('booking', $data);
                
                $temp["response_code"] = "1";
                $temp["message"] = "Service Booked";
                $temp["status"] = "success";
                $temp["booking"] = $like;
                echo json_encode($temp);
            } else {

                $temp["response_code"] = "0";
                $temp["message"] = "Databse Error";
                $temp["status"] = "failure";
                echo json_encode($temp);
            }
        } else {

            $temp["response_code"] = "0";
            $temp["message"] = "Missing Fields";
            $temp["status"] = "failure";
            echo json_encode($temp);
        }
    }
   
    public function payment_success()
    {
        header('Content-Type: application/json');
        
        if (!isset($_POST['txn_id']) || !isset($_POST['booking_id'])) {
            $temp["response_code"] = "0";
            $temp["message"] = "Missing Data";
            $temp["status"] = "failure";
            echo json_encode($temp);
            return;
        }

        $pay = array(
            'txn_id' => $this->input->post('txn_id'),
            'payment_status' => 'Success',
            'amount' => $this->input->post('amount'),
            'payment_mode' => $this->input->post('payment_mode'),
            'p_date' => date('Y-m-d')
        );
        $date = date("Y-m-d H:i:s");
        $order_id = $this->input->post('booking_id');
        $order = $this->front_model->get_order_by_id($order_id);

        if (!$order) {
            $temp["response_code"] = "0";
            $temp["message"] = "Booking Not Found";
            $temp["status"] = "failure";
            echo json_encode($temp);
            return;
        }

        if ($this->front_model->update_payment($pay, $order_id)) {

            $user = $this->front_model->get_user_by_id($order->user_id);
            
            //Send Email
            $message = "<h1>Hello " . $user->username . "</h1>";
            $message .= "<h1>Your Transaction was Successful.</h1>";
            $message .= "<p>Transaction ID: " . $pay['txn_id'] . "</p>";
            $message .= "<p>Amount Paid: " . $pay['amount'] . "</p>";
            $message .= "<p>Date: " . $date . "</p>";
            $message .= '<p>Your Swaft Team</p>';
            
            $this->load->library('email');
            $config['protocol'] = 'smtp';
            $config['smtp_host'] = 'smtp.gmail.com';
            $config['smtp_user'] = 'pesapapp09@gmail.com';
            $config['smtp_pass'] = 'svxhzsuqotpvrvjm';
            $config['smtp_port'] = 25;
            
            // Mail config
            $to = $user->email;
            $from = "pesapapp09@gmail.com";
            $fromName = "Swaft App Team";
            $mailSubject = "Payment Success";
            
            $config['mailtype'] = 'html';
            $this->email->initialize($config);
            $this->email->to($to);
            $this->email->from($from, $fromName);
            $this->email->subject($mailSubject);
            $this->email->message($message);
            
            // Send email & return status
            $send = $this->email->send();
            $temp["response_code"] = "1";
            $temp["message"] = "Order Updated";
            $temp["status"] = "success";
            echo json_encode($temp);
            
            // if ($send) {
            //     $temp["response_code"] = "1";
            //     $temp["message"] = "Order Updated";
            //     $temp["status"] = "success";
            //     echo json_encode($temp);
            //     return;
            // } else {
            //     $temp["response_code"] = "0";
            //     $temp["message"] = "Mail Error, Order has been Updated!";
            //     $temp["status"] = "failure";
            //     echo json_encode($temp);
            //     return;
            // }
        } else {
            $temp["response_code"] = "0";
            $temp["message"] = "Database Error!";
            $temp["status"] = "failure";
            echo json_encode($temp);
            return;
        }
    }

    public function get_booking_by_user()
    {
        $result = array();
        header('Content-Type: application/json');
        
        if (!isset($_POST['user_id']) || !isset($_POST['status'])) {
            $temp["response_code"] = "0";
            $temp["message"] = "Missing Data";
            $temp["status"] = "failure";
            echo json_encode($temp);
            return;
        }

        $id = $this->input->post('user_id');
        $status = $this->input->post('status');
        
        $res =  $this->db->order_by('id', 'DESC')->get_where('booking', array('user_id' => $id, 'status' => $status))->result();
        
        if (!empty($res)) {

            $service = array();
            for ($i = 0; $i < count($res); $i++) {

                $service =  $this->db->get_where('services', array('id' => $res[$i]->service_id))->row();
    
                $catnm = $this->db->get_where('categories', array('id' => $service->cat_id), 1)->row();
                if (!empty($catnm)) {
                   $service->c_name = $catnm->c_name;
                } else {
                   $service->c_name = "";
                }

                $images = explode("::::", $service->service_image);
                $imgs = array();
                $imgsa = array();
                foreach ($images as $key => $image) {
                    $imgs = base_url('uploads/service_images/') . $image;

                    array_push($imgsa, $imgs);
                }

                $resid = $service->res_id;
                $serviceid = $service->id;
                $querycount = $this->db->query("SELECT * FROM services_reviews WHERE rev_service = '$serviceid'");
                $mcount = $querycount->num_rows();
    
                $service->review_count = "$mcount";
    
                $querycounta = $this->db->query("SELECT * FROM view_store WHERE store_id = '$resid'");
                $mcounta = $querycounta->num_rows();
    
                $service->view_count = "$mcounta";
    
                $service->service_image = $imgsa;
    
                $res[$i]->service = $service;
            }

            $result['response_code'] = "1";
            $result['message'] = "Booking Found";
            $result['booking'] = $res;
            $result["status"] = "success";
            echo json_encode($result);
        } else {
            $result["response_code"] = "0";
            $result["message"] = "Booking Not Found";
            $result['booking'] = [];
            $result["status"] = "failure";
            echo json_encode($result);
        }
    }

   public function get_all_banners()
   {
      $result = array();
      header('Content-Type: application/json');

      $res = $this->front_model->get_banners();

      // $res = $this->db->get('banners')->result();
      if (empty($res)) {
         $temp["response_code"] = "0";
         $temp['msg'] = "Banners Not Found";
         echo json_encode($temp);
      } else {
         $imgs = array();
         $imgsa = array();
         foreach ($res as $key => $category) {
            $imgs = base_url('uploads/' . $category->image);

            array_push($imgsa, $imgs);
         }
         $result['status'] = 1;
         $result['msg'] = "Banners Found";
         $result['Banners'] = $imgsa;

         echo json_encode($result);
      }
   }

   public function get_all_products()
   {
      $result = array();
      header('Content-Type: application/json');

      $products = $this->db->get('products')->result();

      for ($i = 0; $i < count($products); $i++) {

         $images = explode("::::", $products[$i]->product_image);

         $imgs = array();
         $imgsa = array();
         foreach ($images as $key => $image) {

            $imgs = base_url() . 'uploads/product_images/' . $image;

            array_push($imgsa, $imgs);
         }

         if (!empty($products[$i]->product_image)) {
            $products[$i]->product_image = $imgsa;
         } else {
            $products[$i]->product_image = [];
         }

         // $products[$i]->product_image = base_url() . 'uploads/product_images/' . $products[$i]->product_image;
      }

      if (empty($products)) {
         $result["status"] = "0";
         $result["message"] = "Products Not Found";
         $result['products'] = $products;

         echo json_encode($result);
         return;
      } else {
         $result['status'] = "1";
         $result['message'] = "Products Found";
         $result['products'] = $products;

         echo json_encode($result);
      }
   }

   public function add_to_cart()
   {
      header('Content-Type: application/json');

      if (!isset($_POST['user_id']) || !isset($_POST['product_id']) || !isset($_POST['quantity'])) {
         $temp["response_code"] = "0";
         $temp["message"] = "Missing Fields";
         $temp["status"] = "fail";
         echo json_encode($temp);
         return;
      }

      $product_id = $this->input->post('product_id');

      $product = $this->front_model->get_product_by_id($product_id);

      if (!$product) {

         $temp["response_code"] = "0";

         $temp["message"] = "Product Not Found";

         $temp["status"] = "fail";

         echo json_encode($temp);

         return;
      }

      $quantity = (int)$this->input->post('quantity');

      // $bulk_qty = (int)$this->input->post('bulk_qty');

      // if ($product->mqty > $quantity) {
      //    $temp["response_code"] = "0";
      //    $temp["message"] = "Minimum quantity is low";
      //    $temp["status"] = "fail";
      //    echo json_encode($temp);

      //    return;
      // }

      // Product found Calculate Price
      // if ((float)$product->product_sale_price > 0) {

      //    $price = ($quantity > 0) ? (float)$product->product_sale_price * $quantity : (float)$product->product_sale_price;
      // } else {

      //    $price = ($quantity > 0) ? (float)$product->product_price * $quantity : (float)$product->product_price;
      // }

      $price = ($quantity > 0) ? (float)$product->product_price * $quantity : (float)$product->product_price;

      // check for new cart
      $user_id = $this->input->post('user_id');
      $user = $this->db->get_where("user", array("id" => $user_id))->row();

      $is_new_cart_item = $this->front_model->is_new_cart_item($user_id, $product_id);

      if ($is_new_cart_item) {

         $data = array();

         $data['user_id'] = $user_id;

         $data['product_id'] = $product_id;

         $data['quantity'] = $quantity;

         $data['price'] = $price;


         if ($this->db->insert('cart_items', $data)) {

            $temp["response_code"] = "1";

            $temp["message"] = "New Item Added to Cart";

            $temp["status"] = "success";

            echo json_encode($temp);

            return;
         } else {

            $temp["response_code"] = "0";

            $temp["message"] = "Database Error";

            $temp["status"] = "fail";

            echo json_encode($temp);

            return;
         }
      } else {

         $data = array();

         $data['quantity'] = $quantity;

         $data['price'] = $price;

         $this->db->where(array('user_id' => $user_id, 'product_id' => $product_id));

         if ($this->db->update('cart_items', $data)) {

            $temp["response_code"] = "1";

            $temp["message"] = "Cart Updated";

            $temp["status"] = "success";

            echo json_encode($temp);

            return;
         } else {

            $temp["response_code"] = "0";

            $temp["message"] = "Database Error";

            $temp["status"] = "fail";

            echo json_encode($temp);

            return;
         }
      }
   }

   public function get_cart_items()
   {

      header('Content-Type: application/json');

      if (!isset($_POST['user_id'])) {

         $temp["response_code"] = "0";

         $temp["message"] = "Missing Fields";

         $temp["status"] = "fail";

         echo json_encode($temp);

         return;
      }

      $user_id = $this->input->post('user_id');

      $cart_items = $this->db->get_where('cart_items', array('user_id' => $user_id));

      if ($cart_items->num_rows() <= 0) {

         $temp["response_code"] = "0";

         $temp["message"] = "Cart is Empty";

         $temp["status"] = "fail";

         echo json_encode($temp);

         return;
      }

      $cart = array();

      $total = 0;

      $total_items = 0;

      foreach ($cart_items->result() as $key => $cart_item) {

         $product = $this->front_model->get_product_by_id($cart_item->product_id);

         if (!empty($product)) {
            $product_id = $product->product_id;
            $product_name = $product->product_name;

            $images = explode("::::", $product->product_image);
            // $product_image = base_url('uploads/product_images/' . $product->product_image);
            $product_image = base_url('uploads/product_images/') . $images[0];
         } else {
            $product_id = "";
            $product_name = "";
            $product_image = "";
            $min_qty = "";
         }

         $item = array(

            'product_id' => $product_id,

            'cart_id' => $cart_item->cart_id,

            'product_image' => $product_image,

            'product_name' => $product_name,

            'quantity' => $cart_item->quantity,

            'price' => $cart_item->price

         );

         array_push($cart, $item);

         $total = $total + $cart_item->price;
         $total_items = $total_items + $cart_item->quantity;
      }

      $temp["response_code"] = "1";

      $temp["message"] = "Cart Items Found";

      $temp["cart"] = $cart;

      $temp["cart_total"] = "$total";

      // $temp["cart_stripe_total"] = (float)$total * 100;

      $temp["total_items"] = "$total_items";

      $temp["status"] = "success";

      echo json_encode($temp);

      return;
   }

   public function get_product_details()
   {

      header('Content-Type: application/json');

      if (!isset($_POST['product_id'])) {

         $temp["response_code"] = "0";

         $temp["message"] = "Product ID Not Given";

         $temp["status"] = "fail";

         echo json_encode($temp);

         return;
      }

      $product_id = $this->input->post('product_id');
      $user_id = $this->input->post('user_id');

      $product = $this->front_model->get_product_by_id($product_id);

      if (!$product) {

         $temp["response_code"] = "0";

         $temp["message"] = "Product Not Found";

         $temp["status"] = "fail";

         echo json_encode($temp);

         return;
      }

      // $cart = $this->db->get_where('cart_items', array('user_id' => $user_id,'product_id' => $product->product_id))->row();
      // if(!empty($cart))
      // {
      //    $product->cart_id = $cart->cart_id;
      //    $product->cart_quantity = $cart->quantity;
      // }
      // else
      // {
      //    $products->cart_id = "";
      //    $product->cart_quantity = "";
      // }

      $images = explode("::::", $product->product_image);

      $imgs = array();
      $imgsa = array();
      foreach ($images as $key => $image) {
         $imgs = base_url('uploads/product_images/') . $image;

         array_push($imgsa, $imgs);
      }
      if (!empty($product->product_image)) {
         $product->product_image = $imgsa;
         // print_r($imgsa);
      } else {
         $product->product_image = [];
      }

      $cat_id = $product->cat_id;
      $categories = $this->db->get_where('product_category', array('id' => $cat_id), 1)->row();
      if (empty($categories->c_name)) {
         $c_name = "";
      } else {
         $c_name = $categories->c_name;
      }
      $product->categories = $c_name;

      $reviews = $this->front_model->get_rev_by_pro_id($product_id);

      for ($i = 0; $i < count($reviews); $i++) {
         $product_id = $reviews[$i]->rev_pro;
         $user_ida = $reviews[$i]->rev_user;

         $producta = $this->front_model->get_pro_by_id($product_id);
         $user = $this->front_model->get_user_by_rev_id($user_ida);

         $reviews[$i]->rev_pro = $producta->product_name;
         $rev_user_data = $this->db->get_where("user", array("id" => $reviews[$i]->rev_user))->row();
         if ($rev_user_data) {
            if ($rev_user_data->profile_pic) {
               $rev_user_data->profile_pic = base_url("uploads/profile_pics/" . $rev_user_data->profile_pic);
            }
         }
         $reviews[$i]->rev_user_data = $rev_user_data;

         if (empty($user->first_name)) {
            $first_name = "";
         } else {
            $first_name = $user->first_name;
         }

         $reviews[$i]->rev_user = $first_name;
      }
      // $is_likes = $this->front_model->likeCheck($user_id, $product_id);
      // if (!empty($is_likes)) {
      //    $product->is_likes = "false";
      // } else {
      //    $product->is_likes = "true";
      // }


      $temp["response_code"] = "1";

      $temp["message"] = "Product Found";

      $temp["product"] = $product;

      $temp['review'] = $reviews;

      $temp["status"] = "success";

      echo json_encode($temp);

      return;
   }

   public function likePro()
   {
      if (isset($_POST['pro_id']) && isset($_POST['user_id'])) {

         $like = array();
         $like['pro_id'] = $this->input->post('pro_id');
         $like['user_id'] = $this->input->post('user_id');
         $like['date'] = time();

         $checkLike = $this->front_model->likeCheck_product($like['user_id'], $like['pro_id']);

         if (!$checkLike) {
            $temp["response_code"] = "0";
            $temp["message"] = "Already Liked Product";
            $temp["status"] = "failure";
            echo json_encode($temp);

            return;
         }

         if ($this->db->insert('likes_product', $like)) {

            $temp["response_code"] = "1";
            $temp["message"] = "Liked Product";
            $temp["status"] = "success";
            echo json_encode($temp);
         } else {

            $temp["response_code"] = "0";
            $temp["message"] = "Databse Error";
            $temp["status"] = "failure";
            echo json_encode($temp);
         }
      } else {

         $temp["response_code"] = "0";
         $temp["message"] = "Missing Fields";
         $temp["status"] = "failure";
         echo json_encode($temp);
      }
   }

   public function unlike_product()
   {
      header('Content-Type: application/json');

      $pro_id = $this->input->post('pro_id');
      $user_id = $this->input->post('user_id');

      if ($this->front_model->unlike_product($pro_id, $user_id)) {
         $result['status'] = 1;
         $result['msg'] = "Successfully Unlike";

         echo json_encode($result);
      } else {
         $result['status'] = 0;
         $result['msg'] = "Something Wrong";

         echo json_encode($result);
      }
   }

   public function remove_cart_items()
   {
      header('Content-Type: application/json');

      $cart_id = $this->input->post('cart_id');

      if ($this->front_model->remove_cart($cart_id)) {
         $result['response_code'] = "1";
         $result['message'] = "Remove Successfully";
         $result["status"] = "success";

         echo json_encode($result);
      } else {
         $result['response_code'] = "0";
         $result['message'] = "Something Wrong";
         $result["status"] = "failure";

         echo json_encode($result);
      }
   }

   public function wishlist()
   {
      $result = array();
      header('Content-Type: application/json');

      $user_id = $this->input->post('user_id');

      $query = $this->db->query("SELECT * FROM likes_product WHERE user_id = '$user_id'");

      $query = $this->db->query("SELECT A.pro_id, B.product_name,B.product_price,B.product_image FROM likes_product A, products B WHERE A.pro_id = B.product_id AND A.user_id = '$user_id' ORDER BY A.like_id DESC");

      $res = $query->result();

      for ($i = 0; $i < count($res); $i++) {
         if (!empty($res[$i]->product_image)) {
            $images = explode("::::", $res[$i]->product_image);

            $res[$i]->product_image = base_url('uploads/product_images/') . $images[0];
         }
      }

      if (empty($res)) {
         $result['response_code'] = "0";
         $result['message'] = "Products Not Found";
         $result['wishlist'] = $res;
         $result["status"] = "failure";

         echo json_encode($result);
      } else {
         $result['response_code'] = "1";
         $result['message'] = "Products Found";
         $result['wishlist'] = $res;
         $result["status"] = "success";

         echo json_encode($result);
      }
   }

   public function checkout()
   {

      header('Content-Type: application/json');

      if (!isset($_POST['user_id']) || !isset($_POST['total']) || !isset($_POST['payment_mode']) || !isset($_POST['address'])) {

         $temp["response_code"] = "0";
         $temp["message"] = "User ID Not Given";
         $temp["status"] = "fail";
         echo json_encode($temp);
         return;
      }

      $user_id = $this->input->post('user_id');

      $cart_items = $this->front_model->get_user_cart($user_id);

      if (!$cart_items) {

         $temp["response_code"] = "0";
         $temp["message"] = "Cart Empty";
         $temp["status"] = "fail";
         echo json_encode($temp);
         return;
      }

      $order = $this->front_model->get_order();

      if (!empty($order)) {
         $order_r = str_replace("order000", "", $order->order_id);
         $order_id = (int)$order_r + 1;
      } else {
         $order_id = 1;
      }

      $param = array(
         'order_id' => 'order000' . $order_id,
         'user_id' =>  $user_id,
         'p_status' => 'pending',
         'total' => $this->input->post('total'),
         'date' => time(),
         'payment_mode' => $this->input->post('payment_mode'),
         'address' => $this->input->post('address'),
         // 'number' => $this->input->post('number'),
         'datea' => date('Y-m-d'),

      );

      $addorder_id = $this->front_model->add_order($param);

      $cart_items = $this->front_model->get_user_cart($user_id);

      foreach ($cart_items as $row) {

         $data = array(
            'order_id' => $addorder_id,
            'product_id' => $row->product_id,
            'qty' => $row->quantity,
            'price' => $row->price,
            'total' => $row->price,
         );
         $this->front_model->add_orderitems($data);
      }

      if ($addorder_id) {

         // $order_id = $this->db->insert_id();

         $title = "Order Placed";
         $message = "Your Order Successfully Placed";

         $response = $this->firebase_model->send_user_notification($user_id, $title, $message, "order", array());

         $this->firebase_model->save_user_notification($user_id, $title, $message, "order", $addorder_id);

         $temp["response_code"] = "1";
         $temp["message"] = "Order Placed";
         $temp["order_id"] = "$addorder_id";
         $temp["status"] = "success";
         echo json_encode($temp);

         $cyear = date("Y");

         $cmonth = date("F");

         $chart = $this->db->get_where('chart_data', array('year' => $cyear, 'month' => $cmonth))->row();
         if (!empty($chart)) {
            $ctotal = $chart->profit;
            $total = $this->input->post('total');

            $mtotal = (float)$ctotal + (float)$total;

            $chartdata = array(

               'profit' => $mtotal,
            );

            $this->db->where('id', $chart->id);
            $this->db->update('chart_data', $chartdata);
         } else {
            $chartdata = array(

               'year' => $cyear,
               'month' => $cmonth,
            );

            $this->db->insert('chart_data', $chartdata);
         }

         // $this->front_model->clear_cart($user_id);
      } else {

         $temp["response_code"] = "0";
         $temp["message"] = "Database Error";
         $temp["status"] = "fail";
         echo json_encode($temp);
      }
   }

   public function product_payment_success()
   {
      header('Content-Type: application/json');

      if (!isset($_POST['txn_id']) || !isset($_POST['order_id'])) {
         $temp["response_code"] = "0";
         $temp["message"] = "Missing Data";
         $temp["status"] = "failure";
         echo json_encode($temp);
         return;
      }

      $pay = array(
         'txn_id' => $this->input->post('txn_id'),
         'p_status' => 'success',
         'p_date' => time()
      );
      $date = date("Y-m-d H:i:s");
      $order_id = $this->input->post('order_id');
      $order = $this->front_model->get_product_order_by_id($order_id);

      if (!$order) {
         $temp["response_code"] = "0";
         $temp["message"] = "Order Not Found";
         $temp["status"] = "failure";
         echo json_encode($temp);
         return;
      }

      if ($this->front_model->update_product_payment($pay, $order_id)) {

         $this->front_model->clear_cart($order->user_id);

         $temp["response_code"] = "1";
         $temp["message"] = "Order Updated";
         $temp["status"] = "success";
         echo json_encode($temp);
         return;
      } else {
         $temp["response_code"] = "0";
         $temp["message"] = "Database Error!";
         $temp["status"] = "failure";
         echo json_encode($temp);
         return;
      }
   }

   public function get_user_orders()
   {

      header('Content-Type: application/json');

      if ($_POST['user_id'] == "") {

         $temp["response_code"] = "0";

         $temp["message"] = "Enter Data";

         $temp["status"] = "failure";

         echo json_encode($temp);
         return;
      }

      $user_id = $this->input->post('user_id');


      $orders = $this->front_model->get_user_orders($user_id);

      if (!$orders) {
         $temp["response_code"] = "0";

         $temp["message"] = "No Orders Found";

         $temp["status"] = "failure";

         echo json_encode($temp);

         return;
      }

      $order_list = array();

      foreach ($orders as $key => $order) {

         $order_list_item['order_id'] = $order->order_id;
         $order_list_item['total'] = $order->total;
         $order_list_item['date'] = gmdate('Y-m-d', $order->date);
         $order_list_item['payment_mode'] = $order->payment_mode;
         $order_list_item['address'] = $order->address;
         $order_list_item['txn_id'] = $order->txn_id;
         $order_list_item['p_status'] = $order->p_status;

         $order_list_item['p_date'] = gmdate('d M Y', (int)$order->p_date);

         $products_arr = array();
         $products_count = 0;

         // $products = json_decode($order->items);

         $products = $this->db->get_where('order_items', array('order_id' => $order->id))->result();

         foreach ($products as $key => $product) {

            $product_details = $this->front_model->get_product_by_id($product->product_id);

            if (!empty($product_details->product_image)) {
               //  $product_details->product_image = base_url() . 'uploads/product_images/' . $product_details->product_image;
               $images = explode('::::', $product_details->product_image);

               $product_details->product_image = base_url() . 'uploads/product_images/' . $images[0];
            }

            $product_details->quantity = $product->qty;

            array_push($products_arr, $product_details);

            $products_count = $products_count + $product->qty;
         }

         $order_list_item['products'] = $products_arr;
         $order_list_item['count'] = $products_count;

         array_push($order_list, $order_list_item);
      }

      $temp["response_code"] = "1";
      $temp["message"] = "Orders Found";
      $temp["orders"] = $order_list;
      $temp["status"] = "success";
      echo json_encode($temp);
   }

   public function service_wishlist()
   {
      $result = array();
      header('Content-Type: application/json');

      $user_id = $this->input->post('user_id');

      $query = $this->db->query("SELECT * FROM likes WHERE user_id = '$user_id'");

      $query = $this->db->query("SELECT A.res_id, B.res_name,B.res_desc,B.res_image FROM likes A, restaurants B WHERE A.res_id = B.res_id AND A.user_id = '$user_id' ORDER BY A.like_id DESC");

      $res = $query->result();

      for ($i = 0; $i < count($res); $i++) {
         if (!empty($res[$i]->res_image)) {
            $images = explode("::::", $res[$i]->res_image);

            $res[$i]->res_image = base_url('uploads/') . $images[0];
         }
      }

      if (empty($res)) {
         $result['response_code'] = "0";
         $result['message'] = "Products Not Found";
         $result['wishlist'] = $res;
         $result["status"] = "failure";

         echo json_encode($result);
      } else {
         $result['response_code'] = "1";
         $result['message'] = "Products Found";
         $result['wishlist'] = $res;
         $result["status"] = "success";

         echo json_encode($result);
      }
   }

   public function get_all_product_category()
   {

      $result = array();
      header('Content-Type: application/json');
      $product_category = $this->db->get('product_category')->result();

      for ($i = 0; $i < count($product_category); $i++) {
         $product_category[$i]->image = base_url() . 'uploads/' . $product_category[$i]->image;
      }

      if (empty($product_category)) {
         $result['response_code'] = "0";
         $result['message'] = "Category Not Found";
         $result['category'] = $product_category;
         $result["status"] = "failure";

         echo json_encode($result);
      } else {
         $result['response_code'] = "1";
         $result['message'] = "Category Found";
         $result['category'] = $product_category;
         $result["status"] = "success";
      }

      echo json_encode($result);
   }

   public function get_pro_by_cat_id()
   {
      header('Content-Type: application/json');

      $cat_id = $this->input->post('cat_id');

      $products = $this->front_model->get_pro_by_cat_id($cat_id);

      foreach ($products as $key => $product) {

         $images = explode("::::", $product->product_image);

         $products[$key]->product_image = base_url('uploads/product_images/') . $images[0];
      }

      // for ($i = 0; $i < count($products); $i++) {
      //    $cat_id = $products[$i]->cat_id;
      //    $categories = $this->db->get_where('product_category', array('id' => $cat_id), 1)->row();
      //    if (empty($categories->c_name)) {
      //       $c_name = "";
      //    } else {
      //       $c_name = $categories->c_name;
      //    }

      //    $products[$i]->categories = $c_name;
      // }

      if (empty($products)) {
         $temp["response_code"] = "1";

         $temp["message"] = "Products Not Found";

         $temp['products'] = $products;

         $temp["status"] = "success";

         echo json_encode($temp);
      } else {

         $temp["response_code"] = "1";

         $temp["message"] = "Products Found";

         $temp['products'] = $products;

         $temp["status"] = "success";

         echo json_encode($temp);
      }
   }

   public function get_all_testimonial_category()
   {

      $result = array();
      header('Content-Type: application/json');
      $query = $this->db->query("SELECT id,c_name FROM testimonial_category");
      $testimonial_category = $query->result();

      if (empty($testimonial_category)) {
         $result['response_code'] = "0";
         $result['message'] = "Category Not Found";
         $result['category'] = $testimonial_category;
         $result["status"] = "failure";

         echo json_encode($result);
      } else {
         $result['response_code'] = "1";
         $result['message'] = "Category Found";
         $result['category'] = $testimonial_category;
         $result["status"] = "success";
      }

      echo json_encode($result);
   }

   public function get_testimonial_by_cat_id()
   {
      header('Content-Type: application/json');

      $cat_id = $this->input->post('cat_id');

      $query = $this->db->query("SELECT * FROM testimonial WHERE cat_id = '$cat_id'");
      $testimonial = $query->result();

      for ($i = 0; $i < count($testimonial); $i++) {
         $cat_id = $testimonial[$i]->cat_id;
         $categories = $this->db->get_where('testimonial_category', array('id' => $cat_id), 1)->row();
         if (empty($categories->c_name)) {
            $c_name = "";
         } else {
            $c_name = $categories->c_name;
         }

         $testimonial[$i]->categories = $c_name;
      }

      if (empty($testimonial)) {
         $temp["response_code"] = "1";

         $temp["message"] = "Review Not Found";

         $temp['review'] = $testimonial;

         $temp["status"] = "success";

         echo json_encode($temp);
      } else {

         $temp["response_code"] = "1";

         $temp["message"] = "Review Found";

         $temp['review'] = $testimonial;

         $temp["status"] = "success";

         echo json_encode($temp);
      }
   }

   public function order_notification_listing()
   {
      header('Content-Type: application/json');
      $this->load->library('form_validation');
      $this->form_validation->set_rules('user_id', "User ID", 'required');

      $user_id = $this->input->post('user_id');

      $notifications = $this->db->get_where("notifications", array("user_id" => $user_id, "type" => "order"))->result();

      header('Content-Type: application/json');

      if ($_POST['user_id'] == "") {

         $temp["response_code"] = "0";

         $temp["message"] = "Enter Data";

         $temp["status"] = "failure";

         echo json_encode($temp);
         return;
      }

      $user_id = $this->input->post('user_id');

      $query = $this->db->query("SELECT A.*, B.* FROM notifications A, orders B WHERE A.user_id = '$user_id' AND A.type = 'order' AND A.data_id = B.id ORDER BY A.not_id DESC");

      $notifications = $query->result();
      
      if (!$notifications) {
         $temp["response_code"] = "0";

         $temp["message"] = "No Notifications Found";

         $temp["status"] = "failure";

         echo json_encode($temp);

         return;
      }

      $notifications_list = array();

      foreach ($notifications as $key => $noti) {

         $noti_list['not_id'] = $noti->not_id;
         $noti_list['user_id'] = $noti->user_id;
         $noti_list['data_id'] = $noti->data_id;
         $noti_list['type'] = $noti->type;
         $noti_list['title'] = $noti->title;
         $noti_list['message'] = $noti->message;
         $noti_list['date'] = $noti->date;

         $products_arr = array();
         // $products_count = 0;

         // $products = json_decode($noti->items);

         $products = $this->db->get_where('order_items', array('order_id' => $noti->id))->result();

         foreach ($products as $key => $product) {

            $product_details = $this->front_model->get_product_by_id($product->product_id);

            if (!empty($product_details->product_image)) {

               $images = explode('::::', $product_details->product_image);

               $product_details->product_image = base_url() . 'uploads/product_images/' . $images[0];
            }

            $product_details->qty = $product->qty;

            array_push($products_arr, $product_details);

            // $products_count = $products_count + $product->quantity;
         }

         $noti_list['products'] = $products_arr;
         // $noti_list['count'] = $products_count;

         array_push($notifications_list, $noti_list);
      }

      $temp["response_code"] = "1";
      $temp["message"] = "Notifications Found";
      $temp["notifications"] = $notifications_list;
      $temp["status"] = "success";
      echo json_encode($temp);
   }

   public function booking_notification_listing()
   {
      header('Content-Type: application/json');
      $this->load->library('form_validation');
      $this->form_validation->set_rules('user_id', "User ID", 'required');

      $user_id = $this->input->post('user_id');

      $notifications = $this->db->get_where("notifications", array("user_id" => $user_id, "type" => "booking"))->result();

      header('Content-Type: application/json');

      if ($_POST['user_id'] == "") {

         $temp["response_code"] = "0";

         $temp["message"] = "Enter Data";

         $temp["status"] = "failure";

         echo json_encode($temp);
         return;
      }

      $user_id = $this->input->post('user_id');

      $query = $this->db->query("SELECT A.*, B.* FROM notifications A, booking B WHERE A.user_id = '$user_id' AND A.type = 'booking' AND A.data_id = B.id ORDER BY A.not_id DESC");
      $notifications = $query->result();

      if (!$notifications) {
         $temp["response_code"] = "0";

         $temp["message"] = "No Notifications Found";

         $temp["status"] = "failure";

         echo json_encode($temp);

         return;
      }

      $notifications_list = array();

      foreach ($notifications as $key => $noti) {

         $noti_list['not_id'] = $noti->not_id;
         $noti_list['user_id'] = $noti->user_id;
         $noti_list['data_id'] = $noti->data_id;
         $noti_list['type'] = $noti->type;
         $noti_list['title'] = $noti->title;
         $noti_list['message'] = $noti->message;
         $noti_list['date'] = $noti->date;

         foreach ($notifications as $key => $product) {
            $products_arr = array();

            $services = $this->db->get_where('services', array('id' => $noti->service_id), 1)->row();

            $product_details['date'] = $noti->date;
            $product_details['slot'] = $noti->slot;
            $product_details['size'] = $noti->size;
            $product_details['amount'] = $noti->amount;
            $product_details['service_name'] = $services->service_name;
            $product_details['service_id'] = $services->id;

            if (!empty($services->service_image)) {

               $images = explode('::::', $services->service_image);

               $product_details['service_image'] = base_url() . 'uploads/service_images/' . $images[0];
            } else {
               $product_details['service_image'] = "";
            }

            array_push($products_arr, $product_details);
         }

         $noti_list['booking'] = $products_arr;

         array_push($notifications_list, $noti_list);
      }

      $temp["response_code"] = "1";
      $temp["message"] = "Notifications Found";
      $temp["notifications"] = $notifications_list;
      $temp["status"] = "success";
      echo json_encode($temp);
   }

   public function general_setting()
   {
      $result = array();
      header('Content-Type: application/json');

      $query = $this->db->query("SELECT * FROM general_setting ");
      $setting = $query->row();

      if (empty($setting)) {
         $temp["response_code"] = "0";
         $temp['msg'] = "Setting Not Found";
         echo json_encode($temp);
      } else {
         $result['status'] = 1;
         $result['msg'] = "Setting Found";
         $result['setting'] = $setting;

         echo json_encode($result);
      }
   }

   public function get_booking_by_vendor()
   {
      $result = array();
      header('Content-Type: application/json');

      $id = $this->input->post('vid');
      $status = $this->input->post('status');

      if ($_POST['vid'] == "") {

         $temp["response_code"] = "0";

         $temp["message"] = "Enter Data";

         $temp["status"] = "failure";

         echo json_encode($temp);
         return;
      }

      // 		$res =  $this->db->get_where('booking', array('vid' => $id))->result();
      $res =  $this->db->order_by('id', 'DESC')->get_where('booking', array('vid' => $id, 'status' => $status))->result();

      if (!empty($res)) {

         $resa = array();
         $resab = array();
         for ($i = 0; $i < count($res); $i++) {

            $date = explode(",", $res[$i]->create_date);
            $res[$i]->date = $date[0];
            $res[$i]->time = $date[1];

            $user = $this->db->get_where('user', array('id' => $res[$i]->user_id), 1)->row();
            if (!empty($user->username)) {
               $res[$i]->username = $user->username;
            } else {
               $res[$i]->username = "";
            }

            if (!empty($user->mobile)) {
               $res[$i]->mobile = $user->mobile;
            } else {
               $res[$i]->mobile = "";
            }

            $res[$i]->tax = "20.00";

            $store =  $this->db->get_where('restaurants', array('res_id' => $res[$i]->res_id))->row();
            $images = explode("::::", $store->res_image);
            $imgs = array();
            $imgsa = array();
            foreach ($images as $key => $image) {
               $imgs = base_url('uploads/') . $image;

               array_push($imgsa, $imgs);
            }
            $res[$i]->store_image = $imgsa;

            $services =  $this->db->get_where('services', array('id' => $res[$i]->service_id))->row();

            $catnm = $this->db->get_where('categories', array('id' => $services->cat_id), 1)->row();

            if (!empty($catnm->c_name)) {
               $services->c_name = $catnm->c_name;
            } else {
               $services->c_name = "";
            }

            // if ($services->service_image == "") {
            //   $services->service_image = "";
            // } else {
            //   $services->service_image = base_url() . 'uploads/service_images/' . $services->service_image;
            // }
            
            if (!empty($services->service_image)) {
               $images = explode('::::', $services->service_image);
               $services->service_image = base_url('uploads/service_images/') . $images[0];
            } else {
               $services->service_image = "";
            }

            // $imgsb = array();
            // $imgsab = array();
            // foreach ($images as $key => $image) {
            //    $imgsb['res_imag' . $key] = base_url('uploads/') . $image;
            // }

            // $resid = $services->res_id;
            // $querycount = $this->db->query("SELECT * FROM reviews WHERE rev_res = '$resid'");
            // $mcount = $querycount->num_rows();

            // $services->review_count = $mcount;

            // $querycounta = $this->db->query("SELECT * FROM view_store WHERE store_id = '$resid'");
            // $mcounta = $querycounta->num_rows();

            // $services->view_count = $mcounta;

            // $services->all_image = $imgsa;
            // $services->res_image = $imgsb;
            // $services->logo = base_url() . 'uploads/' . $services->logo;
            // if ($services->res_video == "") {
            //    $services->res_video = "";
            // } else {
            //    $services->res_video = base_url() . 'uploads/' . $services->res_video;
            // }

            $res[$i]->service = $services;
         }

         $result['response_code'] = "1";
         $result['message'] = "Booking Found";
         $result['booking'] = $res;
         echo json_encode($result);
      } else {

         $result["response_code"] = "0";
         $result['message'] = "Booking Not Found";
         $result['booking'] = $res;
         echo json_encode($result);
      }
   }

   public function get_booking_details_by_id()
   {
      $user_id = $this->input->post('user_id');
      $result = array();
      header('Content-Type: application/json');

      if (isset($_POST['id'])) {

         $id = $this->input->post('id');
         $booking = $this->db->get_where('booking', array('id' => $id), 1);

         $store =  $this->db->get_where('restaurants', array('res_id' => $booking->row()->res_id))->row();
         $images = explode('::::', $store->res_image);
         $imgs = array();
         $imgsa = array();
         foreach ($images as $key => $image) {
            $imgs = base_url('uploads/') . $image;
            array_push($imgsa, $imgs);
         }

         if ($booking->num_rows() > 0) {
            $result['status'] = "1";
            $result['message'] = "Booking Found";
            $result['booking'] = $booking->row();

            $date = explode(",", $booking->row()->create_date);
            $result['booking']->c_date = $date[0];
            $result['booking']->c_time = $date[1];

            $user = $this->db->get_where('user', array('id' => $booking->row()->user_id), 1)->row();

            if (!empty($user->username)) {
               $result['booking']->username = $user->username;
            } else {
               $result['booking']->username = "";
            }

            if (!empty($user->mobile)) {
               $result['booking']->customer_contact = $user->mobile;
            } else {
               $result['booking']->customer_contact = "";
            }

            $services =  $this->db->get_where('services', array('id' => $booking->row()->service_id))->row();

            if (!empty($services->service_name)) {
               $result['booking']->service_name = $services->service_name;
            } else {
               $result['booking']->service_name = "";
            }

            if (!empty($services->service_price)) {
               $result['booking']->service_price = $services->service_price;
            } else {
               $result['booking']->service_price = "";
            }

            if (!empty($services->service_image)) {
               $images = explode('::::', $services->service_image);
               $result['booking']->service_image  = base_url('uploads/service_images/') . $images[0];
            } else {
               $result['booking']->service_image  = "";
            }

            $catnm = $this->db->get_where('categories', array('id' => $services->cat_id), 1)->row();
            if (!empty($catnm->c_name)) {
               $result['booking']->c_name = $catnm->c_name;
            } else {
               $result['booking']->c_name = "";
            }

            if (!empty($store->res_image)) {
               $result['booking']->store_image = $imgsa;
            } else {
               $result['booking']->store_image = [];
            }

            $result['booking']->tax = "20.00";

            echo json_encode($result);
         } else {
            $result['status'] = "0";
            $result['message'] = "Booking not Found";

            echo json_encode($result);
         }
      }
   }

   public function booking_status_change_by_vendor()
   {
      header('Content-Type: application/json');

      $id = $this->input->post('id');
      $status = $this->input->post('status');

      if ($_POST['id'] == "") {

         $temp["response_code"] = "0";
         $temp["message"] = "Enter Data";
         $temp["status"] = "failure";
         echo json_encode($temp);
         return;
      }

      $data = array();

      $data['status'] = $status;
      $this->db->where('id', $id);
      $update = $this->db->update('booking', $data);

      if ($status == "Confirm") {

         $title = "Booking Confirm";
         $message = "Your Booking Successfully Confirm";
      } elseif ($status == "On Way") {

         $title = "Booking On Way";
         $message = "Your Booking On Way";
      } elseif ($status == "Completed") {
         
         $title = "Booking Completed";
         $message = "Your Booking Successfully Completed";
      } else {

         $title = "Booking Cancel";
         $message = "Your Booking Cancel";
      }

      $booking = $this->db->get_where('booking', array('id' => $id), 1)->row();

      $response = $this->firebase_model->send_user_notification($booking->user_id, $title, $message, "Message");

      $this->firebase_model->save_user_notification($booking->user_id, $title, $message, "booking", $booking->id);

      if ($update) {
         $temp["response_code"] = "1";
         $temp["message"] = "Update Successfully";
         $temp["status"] = "success";
         echo json_encode($temp);
      } else {
         $temp["response_code"] = "0";
         $temp["message"] = "Database error";
         $temp["status"] = "failure";
         echo json_encode($temp);
      }
   }

   public function add_vendor_product()
   {

      if ($_POST['product_name'] != "" || $_POST['vid'] != "" || $_POST['cat_id'] != "") {

         $product_image = array();

         if (isset($_FILES['product_image']['name']) && $_FILES['product_image']['name'] != "") {
            //echo "image detected";
            if (is_array($_FILES['product_image']['name'])) {
               $filesCount = count($_FILES['product_image']['name']);
               for ($i = 0; $i < $filesCount; $i++) {
                  $_FILES['file']['name']     = $_FILES['product_image']['name'][$i];
                  $_FILES['file']['type']     = $_FILES['product_image']['type'][$i];
                  $_FILES['file']['tmp_name'] = $_FILES['product_image']['tmp_name'][$i];
                  $_FILES['file']['error']     = $_FILES['product_image']['error'][$i];
                  $_FILES['file']['size']     = $_FILES['product_image']['size'][$i];

                  // File upload configuration
                  $config['upload_path'] = './uploads/product_images';
                  $config['allowed_types'] = 'gif|jpg|png|jpeg';
                  $config['file_name'] = uniqid();
                  $config['overwrite'] = TRUE;


                  // Load and initialize upload library
                  $this->load->library('upload');
                  $this->upload->initialize($config);

                  // Upload file to server
                  if ($this->upload->do_upload('file')) {
                     // Uploaded file data
                     $fileData = $this->upload->data();
                     array_push($product_image, $fileData['file_name']);
                     //$product_image = $fileData['file_name'];

                  } else {
                     $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));

                     $temp["response_code"] = "0";
                     $temp["message"] = $error['error'];
                     $temp["status"] = "failure";
                     echo json_encode($temp);
                  }
               }
            }
         }

         $data['vid'] = $this->input->post('vid');
         $data['cat_id'] = $this->input->post('cat_id');

         $data['product_name'] = $this->input->post('product_name');
         $data['product_description'] = $this->input->post('product_description');
         $data['product_price'] = $this->input->post('product_price');

         $data['product_image'] = implode('::::', $product_image);

         // $data['product_create_date'] = time();

         if ($this->db->insert('products', $data)) {

            $temp["response_code"] = "1";
            $temp["message"] = "success";
            $temp["status"] = "success";
            echo json_encode($temp);
         } else {

            $temp["response_code"] = "0";
            $temp["message"] = "Database Error";
            $temp["status"] = "failure";
            echo json_encode($temp);
         }
      } else {

         $temp["response_code"] = "0";
         $temp["message"] = "Enter Data";
         $temp["status"] = "failure";
         echo json_encode($temp);
      }
   }

   public function edit_vendor_product()
   {

      if ($_POST['product_name'] != "" || $_POST['product_id'] != "" || $_POST['cat_id'] != "") {

         $product_image = array();

         $product_id = $this->input->post('product_id');

         $products = $this->db->get_where('products', array('product_id' => $product_id), 1)->row();

         $list_image = array();
         if (isset($_FILES['product_image']['name']) && $_FILES['product_image']['name'][0] != "") {
            //echo "image detected";
            if (is_array($_FILES['product_image']['name'])) {
               $filesCount = count($_FILES['product_image']['name']);
               for ($i = 0; $i < $filesCount; $i++) {
                  $_FILES['file']['name']     = $_FILES['product_image']['name'][$i];
                  $_FILES['file']['type']     = $_FILES['product_image']['type'][$i];
                  $_FILES['file']['tmp_name'] = $_FILES['product_image']['tmp_name'][$i];
                  $_FILES['file']['error']     = $_FILES['product_image']['error'][$i];
                  $_FILES['file']['size']     = $_FILES['product_image']['size'][$i];

                  // File upload configuration
                  $config['upload_path'] = './uploads/product_images';
                  $config['allowed_types'] = 'gif|jpg|png|jpeg';
                  $config['file_name'] = uniqid();
                  $config['overwrite'] = TRUE;


                  // Load and initialize upload library
                  $this->load->library('upload');
                  $this->upload->initialize($config);

                  // Upload file to server
                  if ($this->upload->do_upload('file')) {
                     // Uploaded file data
                     $fileData = $this->upload->data();
                     array_push($product_image, $fileData['file_name']);
                     //$product_image = $fileData['file_name'];

                  } else {
                     $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));

                     $temp["response_code"] = "0";
                     $temp["message"] = $error['error'];
                     $temp["status"] = "failure";
                     echo json_encode($temp);
                  }
               }
               $image_list = $this->input->post('image_list');
               // 	$url = explode("http://localhost/eshield_multivendor/uploads/product_images/", $image_list);
            //   $url = explode("https://primocysapp.com/eshield_multivendor/uploads/product_images/", $image_list);
               
               $url = explode("https://eshield.theprimoapp.com/uploads/", $image_list);

               foreach ($url as $key => $list) {

                  $url_li = explode(",", $list);

                  foreach ($url_li as $key => $img_list) {
                     array_push($product_image, $img_list);
                  }
               }
               $result = array_merge($product_image, $list_image);

               foreach ($product_image as $key => $list) {
                  if (!empty($list)) {
                     array_push($list_image, $list);
                  }
               }
               $data['product_image'] = implode("::::", $list_image);
            }
         }

         $data['cat_id'] = $this->input->post('cat_id');
         $data['vid'] = $this->input->post('vid');

         $data['product_name'] = $this->input->post('product_name');
         $data['product_description'] = $this->input->post('product_description');

         $data['product_price'] = $this->input->post('product_price');

         $product_id = $this->input->post('product_id');

         $this->db->where('product_id', $product_id);
         if ($this->db->update('products', $data)) {

            $temp["response_code"] = "1";
            $temp["message"] = "success";
            $temp["status"] = "success";
            echo json_encode($temp);
         } else {

            $temp["response_code"] = "0";
            $temp["message"] = "Database Error";
            $temp["status"] = "failure";
            echo json_encode($temp);
         }
      } else {

         $temp["response_code"] = "0";
         $temp["message"] = "Enter Data";
         $temp["status"] = "failure";
         echo json_encode($temp);
      }
   }

   public function get_products_by_vendor_id()
   {
      header('Content-Type: application/json');

      $vendor_id = $this->input->post('vendor_id');

      if ($_POST['vendor_id'] == "") {

         $result["response_code"] = "0";
         $result["message"] = "Enter Data";
         $result["status"] = "failure";
         echo json_encode($result);
         return;
      }

      $this->db->select('*');
      $this->db->from('products');
      $this->db->where("vid", $vendor_id);
      $this->db->order_by("product_id", "desc");
      $query = $this->db->get();
      $products = $query->result();

      if (!empty($products)) {

         for ($i = 0; $i < count($products); $i++) {

            $images = explode("::::", $products[$i]->product_image);
            $imgs = array();
            $imgsa = array();
            foreach ($images as $key => $image) {
               $imgs = base_url('uploads/product_images/') . $image;

               array_push($imgsa, $imgs);
            }
            $products[$i]->product_image = $imgsa;
         }

         $result["response_code"] = "1";
         $result["message"] = "Products Found";
         $result['products'] = $products;
         $result["status"] = "success";
         echo json_encode($result);
         return;
      } else {

         $result["response_code"] = "0";
         $result["message"] = "Products Not Found";
         $result['products'] = $products;
         $result["status"] = "failure";
         echo json_encode($result);
         return;
      }
   }

   public function vendor_notification_listing()
   {
      header('Content-Type: application/json');
      $this->load->library('form_validation');
      $this->form_validation->set_rules('v_id', "Vendor ID", 'required');

      if ($this->form_validation->run() == TRUE) {


         $v_id = $this->input->post('v_id');

         $notifications = $this->db->order_by("not_id", "desc")->get_where("vendor_notification", array("v_id" => $v_id))->result();

         if ($notifications) {
            $temp["response_code"] = "1";

            $temp["message"] = "Found";

            $temp["status"] = "success";

            $temp['data'] = $notifications;

            echo json_encode($temp);
         } else {
            $temp["response_code"] = "0";

            $temp["message"] = "Not Found";

            $temp["status"] = "fail";

            echo json_encode($temp);
         }
      } else {
         $temp["response_code"] = "0";

         $temp["message"] = "Missing Field";

         $temp["status"] = "fail";

         echo json_encode($temp);
      }
   }
   
    public function update_vendor_notification_read_status()
    {
        header('Content-Type: application/json');
        
        $not_id = $this->input->post('not_id');
        
        if (!empty($_POST['not_id'])) {

         // $user_notification = $this->db->query("SELECT * FROM user_notification WHERE not_id IN ('" . implode("','", $not_id) . "') ")->result();

            foreach ($not_id as $list) {

                $data['read_status'] = "1";
                $this->db->where('not_id', $list);
                $update = $this->db->update('vendor_notification', $data);
            }

            if ($update) {

                $result["response_code"] = "1";
                $result["message"] = "success";
                $result["status"] = "success";
                $result["not_id"] = $not_id;
                echo json_encode($result);
            } else {

                $result["response_code"] = "0";
                $result["message"] = "Database Error";
                $result["status"] = "failure";
                $result["not_id"] = $not_id;
                echo json_encode($result);
            }
        } else {

            $result["response_code"] = "0";
            $result["message"] = "Enter Data";
            $result["status"] = "failure";
            $result["not_id"] = $not_id;
            echo json_encode($result);
        }
    }

    public function vendor_notification_read_count()
    {
        $result = array();
        header('Content-Type: application/json');
        $v_id = $this->input->post('v_id');
        
        if (!isset($_POST['v_id'])) {
            $result["response_code"] = "0";
            $result["message"] = "Enter Data";
            $result["status"] = "failure";
            echo json_encode($result);
            return;
        }

        $querycount = $this->db->query("SELECT * FROM vendor_notification WHERE read_status = '0' AND v_id = '$v_id' ORDER BY not_id DESC ");
        $unread_notification = $querycount->result();
        $mcount = $querycount->num_rows();
        
        if (!empty($mcount)) {
            $result['response_code'] = "1";
            $result['message'] = "Notification Found";
            $result['count'] = "$mcount";
            $result['unread_notification'] = $unread_notification;
            $result["status"] = "success";
            echo json_encode($result);
        } else {
            $result["response_code"] = "0";
            $result["message"] = "Notification Not Found";
            $result['count'] = "$mcount";
            $result['unread_notification'] = $unread_notification;
            $result["status"] = "failure";
            echo json_encode($result);
        }
    }

    public function add_service_by_store_id()
    {
        $result = array();
        $service_image = array();
        header('Content-Type: application/json');
        
        if (isset($_POST['cat_id']) && isset($_POST['store_id']) && isset($_POST['service_name'])) {
          
            if (isset($_FILES['service_image']['name']) && $_FILES['service_image']['name'] != "") {
                //echo "image detected";
                if (is_array($_FILES['service_image']['name'])) {
                    $filesCount = count($_FILES['service_image']['name']);
                    for ($i = 0; $i < $filesCount; $i++) {
                        $_FILES['file']['name']     = $_FILES['service_image']['name'][$i];
                        $_FILES['file']['type']     = $_FILES['service_image']['type'][$i];
                        $_FILES['file']['tmp_name'] = $_FILES['service_image']['tmp_name'][$i];
                        $_FILES['file']['error']     = $_FILES['service_image']['error'][$i];
                        $_FILES['file']['size']     = $_FILES['service_image']['size'][$i];
                        
                        // File upload configuration
                        $config['upload_path'] = './uploads/service_images/';
                        $config['allowed_types'] = 'gif|jpg|png|jpeg';
                        $config['file_name'] = uniqid();
                        $config['overwrite'] = TRUE;
                        
                        
                        // Load and initialize upload library
                        $this->load->library('upload');
                        $this->upload->initialize($config);
                        
                        // Upload file to server
                        if ($this->upload->do_upload('file')) {
                            // Uploaded file data
                            $fileData = $this->upload->data();
                            array_push($service_image, $fileData['file_name']);
                            //$service_image = $fileData['file_name'];
    
                        } else {
                            $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
                            
                            $temp["response_code"] = "0";
                            $temp["message"] = $error['error'];
                            $temp["status"] = "failure";
                            echo json_encode($temp);
                        }
                    }
                }
            }

            $data = array();
            $data['cat_id'] = $this->input->post('cat_id');
            $data['res_id'] = $this->input->post('store_id');
            $data['v_id'] = $this->input->post('v_id');
            $data['service_name'] = $this->input->post('service_name');
            $data['service_price'] = $this->input->post('service_price');
            $data['service_description'] = $this->input->post('service_description');
            $data['price_unit'] = $this->input->post('price_unit');
            $data['duration'] = $this->input->post('duration');
            $data['service_image'] = implode('::::', $service_image);
            // $data['created_date'] = time();
            
            if ($this->db->insert('services', $data)) {
                $temp["response_code"] = "1";
                $temp["message"] = "Service Added Success";
                $temp["status"] = "success";
                echo json_encode($temp);
            } else {
                $result['response_code'] = "0";
                $result['msg'] = "Database Error";
                $result["status"] = "failure";
                
                echo json_encode($result);
            }
        } else {
            $result['response_code'] = "0";
            $result['msg'] = "Missing Data";
            $result["status"] = "failure";
            
            echo json_encode($result);
        }
    }

    public function edit_service()
    {
        $data = array();
        $service_image = array();
        if ($_POST['service_id'] != "" || $_POST['store_id'] != "" || $_POST['cat_id'] != "" || $_POST['service_name']) {

            $service_id = $this->input->post('service_id');
            
            $services = $this->db->get_where('services', array('id' => $service_id), 1)->row();
            
            if (isset($_FILES['service_image']['name'][0]) && $_FILES['service_image']['name'][0] != "") {
                //echo "image detected";
                if (is_array($_FILES['service_image']['name'])) {
                    $filesCount = count($_FILES['service_image']['name']);
                    for ($i = 0; $i < $filesCount; $i++) {
                        $_FILES['file']['name']     = $_FILES['service_image']['name'][$i];
                        $_FILES['file']['type']     = $_FILES['service_image']['type'][$i];
                        $_FILES['file']['tmp_name'] = $_FILES['service_image']['tmp_name'][$i];
                        $_FILES['file']['error']     = $_FILES['service_image']['error'][$i];
                        $_FILES['file']['size']     = $_FILES['service_image']['size'][$i];
                        
                        // File upload configuration
                        $config['upload_path'] = './uploads/service_images/';
                        $config['allowed_types'] = 'gif|jpg|png|jpeg';
                        $config['file_name'] = uniqid();
                        $config['overwrite'] = TRUE;
                        
                        
                        // Load and initialize upload library
                        $this->load->library('upload');
                        $this->upload->initialize($config);
                        
                        // Upload file to server
                        if ($this->upload->do_upload('file')) {
                            // Uploaded file data
                            $fileData = $this->upload->data();
                            array_push($service_image, $fileData['file_name']);
                            //$service_image = $fileData['file_name'];
    
                        } else {
                            $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
                            
                            $temp["response_code"] = "0";
                            $temp["message"] = $error['error'];
                            $temp["status"] = "failure";
                            echo json_encode($temp);
                        }
                    }
                    $data['service_image'] = implode("::::", $service_image);
                }
            }

            $data['cat_id'] = $this->input->post('cat_id');
            $data['res_id'] = $this->input->post('store_id');
            $data['v_id'] = $this->input->post('v_id');
            $data['service_name'] = $this->input->post('service_name');
            $data['service_price'] = $this->input->post('service_price');
            $data['service_description'] = $this->input->post('service_description');
            $data['price_unit'] = $this->input->post('price_unit');
            $data['duration'] = $this->input->post('duration');
            
            $service_id = $this->input->post('service_id');
            
            $this->db->where('id', $service_id);
            if ($this->db->update('services', $data)) {

                $result["response_code"] = "1";
                $result["message"] = "success";
                $result["status"] = "success";
                echo json_encode($result);
            } else {

                $result["response_code"] = "0";
                $result["message"] = "Database Error";
                $result["status"] = "failure";
                echo json_encode($result);
            }
        } else {

            $result["response_code"] = "0";
            $result["message"] = "Enter Data";
            $result["status"] = "failure";
            echo json_encode($result);
        }
    }

   public function get_vendor_service()
   {
      header('Content-Type: application/json');
      $this->load->library('form_validation');
      $this->form_validation->set_rules('v_id', "Vendor ID", 'required');

      if ($this->form_validation->run() == TRUE) {

         $v_id = $this->input->post('v_id');

         $services = $this->db->order_by("id", "desc")->get_where("services", array("v_id" => $v_id))->result();

         $services_list = array();

         for ($i = 0; $i < count($services); $i++) {
             
            if (!empty($services[$i]->service_image)) {
               $images = explode('::::', $services[$i]->service_image);
               $services[$i]->service_image = base_url('uploads/service_images/') . $images[0];
            } else {
               $services[$i]->service_image = "";
            }

            $cat_id = $services[$i]->cat_id;
            $categories = $this->db->get_where("categories", array("id" => $cat_id))->row();
            if (!empty($categories)) {
               $services[$i]->categoty_name = $categories->c_name;
            } else {
               $services[$i]->categoty_name = "";
            }

            $res_id = $services[$i]->res_id;
            $restaurants = $this->db->get_where("restaurants", array("res_id" => $res_id))->row();
            if (!empty($restaurants)) {
               $services[$i]->store_name = $restaurants->res_name;
            } else {
               $services[$i]->store_name = "";
            }

            $service_id = $services[$i]->id;
            $query = $this->db->query("SELECT A.rev_id,A.rev_service,A.rev_user,A.rev_stars,A.rev_text,A.rev_date, B.username,B.profile_pic FROM services_reviews A, user B WHERE A.rev_service = '$service_id' AND A.rev_user = B.id ORDER BY A.rev_id DESC");
            $reviews = $query->row();

            if (!empty($reviews)) {
               // for ($i = 0; $i < count($reviews); $i++) {
               $id = $reviews->rev_service;
               $user_id = $reviews->rev_user;

               $query = $this->db->get_where('services', array('id' => $id), 1);
               $service_name = $query->row();
               $reviews->rev_service = $service_name->service_name;

               $user = $this->front_model->get_rev_by_id_user($user_id);
               $rev_user_data = $this->db->get_where("user", array("id" => $reviews->rev_user))->row();

               if (!empty($rev_user_data->profile_pic)) {
                  $url = explode(":", $rev_user_data->profile_pic);
                  if ($url[0] == "https" || $url[0] == "http") {
                     $rev_user_data->profile_pic = $rev_user_data->profile_pic;
                  } elseif (!empty($rev_user_data->profile_pic)) {
                     $rev_user_data->profile_pic = base_url() . "uploads/profile_pics/" . $rev_user_data->profile_pic;
                  } else {
                     $rev_user_data->profile_pic = $rev_user_data->profile_pic;
                  }
               } else {
                  $rev_user_data->profile_pic = "";
               }

               $reviews->rev_user_data = $rev_user_data;

               if (empty($user->username)) {
                  $username = "";
               } else {
                  $username = $user->username;
               }

               $reviews->rev_user = $username;

               array_push($services_list, $reviews);
               // }
            }

            $services[$i]->services_reviews = $services_list;
         }

         if ($services) {
            $result["response_code"] = "1";
            $result["message"] = "Services Found";
            $result['data'] = $services;
            $result["status"] = "success";
            echo json_encode($result);
         } else {
            $result["response_code"] = "0";
            $result["message"] = "Services Not Found";
            $result['data'] = $services;
            $result["status"] = "fail";
            echo json_encode($result);
         }
      } else {
         $result["response_code"] = "0";
         $result["message"] = "Missing Field";
         $result["status"] = "fail";
         echo json_encode($result);
      }
   }

   public function get_order_by_vendor_id()
   {

      header('Content-Type: application/json');

      $vendor_id = $this->input->post('vendor_id');

      if ($_POST['vendor_id'] == "") {

         $temp["response_code"] = "0";

         $temp["message"] = "Enter Data";

         $temp["status"] = "failure";

         echo json_encode($temp);
         return;
      }

      // ========= Vendor Wallet Upadate =========== //
      // $total_a = array();
      // $query = $this->db->query("SELECT A.product_id, B.id,B.order_id,B.product_id,B.qty,B.price,B.total, C.id,C.user_id,C.order_id,C.date,C.payment_mode,C.address,C.txn_id,C.p_status,C.order_status,C.p_date, B.id as order_items_id FROM products A, order_items B, orders C WHERE A.product_id = B.product_id AND A.v_id = '$vendor_id' AND B.order_id = C.id AND B.erning_status = '0' ORDER BY B.id DESC");
      // $product_t = $query->result();
      // foreach ($product_t as $key => $list_t) {
      //    array_push($total_a, $list_t->price);

      //    $id = $list_t->order_items_id;
      //    $erning_status['erning_status'] = '1';
      //    $this->db->where('id', $id);
      //    $this->db->update('order_items', $erning_status);
      // }

      // $total_b = 0;
      // $total_b = array_sum($total_a);
      // $user = $this->db->get_where('user', array('id' => $vendor_id), 1)->row();
      // $plans = $user->plans;

      // $query = $this->db->query("SELECT * FROM subscription_plans WHERE name = '$plans' ");
      // $subscription_plans = $query->row();
      // $discount_per = "0";
      // $discount_per = $total_b * $subscription_plans->commission;
      // $grand_t = $discount_per / 100;

      // $grand_total = (float)$total_b - $grand_t;

      // $user = $this->db->get_where("user", array("id" => $vendor_id))->row();
      // (float)$wallet = $user->wallet;

      // $datasave['wallet'] = $grand_total + $wallet;
      // $this->db->where('id', $vendor_id);
      // $this->db->update('user', $datasave);

      // ========= Vendor Wallet Upadate =========== //

      $vendor = $this->db->get_where("vendor", array("id" => $vendor_id))->row();
      $vendor_name = $vendor->uname;
      // $wallet = $user->wallet;

      $query = $this->db->query("SELECT * FROM products WHERE vid = '$vendor_id'");

      $query = $this->db->query("SELECT A.product_id, B.order_id,B.product_id,B.qty,B.price,B.total, C.id,C.user_id,C.order_id,C.date,C.payment_mode,C.address,C.txn_id,C.p_status,C.order_status,C.p_date FROM products A, order_items B, orders C WHERE A.product_id = B.product_id AND A.vid = '$vendor_id' AND B.order_id = C.id GROUP BY B.order_id ORDER BY B.id DESC"); // LIMIT 10

      $res = $query->result();

      $order_list = array();

      foreach ($res as $key => $order) {

         $query = $this->db->query("SELECT A.product_id,A.product_name,A.product_image, B.order_id,B.product_id,B.qty,B.price, C.user_id,C.order_id,C.date,C.payment_mode,C.address,C.order_status FROM products A, order_items B, orders C WHERE B.order_id = '$order->id' AND A.product_id = B.product_id AND A.vid = '$vendor_id' GROUP BY B.product_id ORDER BY B.id DESC ");
         $product_details = $query->result();

         $total_o = array();
         foreach ($product_details as $key => $price) {
            array_push($total_o, $price->price);
         }

         $order_list_item['id'] = $order->id;
         $order_list_item['order_id'] = $order->order_id;
         $order_list_item['total'] = (string)array_sum($total_o);
         $order_list_item['date'] = gmdate('d M Y', $order->date);
         $order_list_item['payment_mode'] = $order->payment_mode;
         $order_list_item['address'] = $order->address;
         $order_list_item['txn_id'] = $order->txn_id;
         $order_list_item['p_status'] = $order->p_status;
         $order_list_item['order_status'] = $order->order_status;
         $order_list_item['price'] = $order->price;
         $order_list_item['qty'] = $order->qty;

         $order_list_item['p_date'] = gmdate('d M Y', (int)$order->p_date);

         $username = $this->db->get_where("user", array("id" => $order->user_id))->row();
         $user_name = $username->username;
         $order_list_item['user_name'] = $user_name;

         $products_arr = array();

         foreach ($product_details as $key => $list) {
            $images = explode("::::", $list->product_image);

            if (!empty($images)) {
               $list->product_image = base_url('uploads/product_images/') . $images[0];
            }

            // if ($list->size == '') {
            //    $list->size = "";
            // } else {
            //    $list->size = "$list->size";
            // }

            // if ($list->colors == '') {
            //    $list->colors = "";
            // } else {
            //    $list->colors = "$list->colors";
            // }

            array_push($products_arr, $list);
         }

         $order_list_item['products'] = $products_arr;
         $order_list_item['grand_total'] = (string)array_sum($total_o);
         array_push($order_list, $order_list_item);
      }

      if (empty($res)) {
         $result['status'] = 0;
         $result['vendor_name'] = $vendor_name;
         // $result['earnings'] = $wallet;
         if (!empty($vendor->profile_image)) {
            $result['vendor_images'] = base_url('uploads/profile_pics/') . $vendor->profile_image;
         } else {
            $result['vendor_images'] = "";
         }
         $result["message"] = "No Orders Found";
         $result['orders'] = $order_list;

         echo json_encode($result);
      } else {
         $result['status'] = 1;
         $result['vendor_name'] = $vendor_name;
         // $result['earnings'] = $wallet;
         if (!empty($vendor->profile_image)) {
            $result['vendor_images'] = base_url('uploads/profile_pics/') . $vendor->profile_image;
         } else {
            $result['vendor_images'] = "";
         }
         $result['msg'] = "Orders Found";
         $result['orders'] = $order_list;

         echo json_encode($result);
      }
   }

   public function update_order_status()
   {
      $id = $this->input->post('order_id');

      $order = $this->db->get_where('orders', array('id' => $id), 1)->row();
      if (empty($order)) {
         $temp["response_code"] = "0";
         $temp["message"] = "Order Not Found";
         $temp["status"] = "failure";
         echo json_encode($temp);
         return;
      }

      $data['order_status'] = $this->input->post('status');

      if (!empty($id)) {

         $this->db->where('id', $id);
         $update = $this->db->update('orders', $data);

         $status = $this->input->post('status');

         if ($status == 0) {

            $title = "Processing";
            $message = "Your Order Processing";
         } elseif ($status == 1) {

            $title = "Order Dispatch";
            $message = "Your Order Dispatch";
         } else {

            $title = "Order Deliver";
            $message = "Your Order Successfully Deliver";
         }

         // $order = $this->db->get_where('orders', array('id' => $id), 1)->row();
         // $response = $this->firebase_model->send_user_notification($order->user_id, $title, $message, "Message");
         // $this->firebase_model->save_notification($order->user_id, $title, $message, "order", $order->id);

         if ($update) {
            $temp["response_code"] = "1";
            $temp["message"] = "Update Successfully";
            $temp["status"] = "success";
            echo json_encode($temp);
         } else {
            $temp["response_code"] = "0";
            $temp["message"] = "Database error";
            $temp["status"] = "failure";
            echo json_encode($temp);
         }
      } else {

         $temp["response_code"] = "0";
         $temp["message"] = "Enter Data";
         $temp["status"] = "failure";
         echo json_encode($temp);
      }
   }

   public function get_all_services()
   {
      $result = array();
      header('Content-Type: application/json');

      $query = $this->db->query("SELECT * FROM services ORDER BY id DESC");
      $services = $query->result();

      for ($i = 0; $i < count($services); $i++) {

        //  if (!empty($services[$i]->service_image)) {
        //     $services[$i]->service_image = base_url('uploads/service_images/') . $services[$i]->service_image;
        //  } else {
        //     $services[$i]->service_image = "";
        //  }
        
        if (!empty($services[$i]->service_image)) {
            $images = explode('::::', $services[$i]->service_image);
            $services[$i]->service_image = base_url('uploads/service_images/') . $images[0];
         } else {
            $services[$i]->service_image = "";
         }

         $cat_id = $services[$i]->cat_id;
         $categories = $this->db->get_where('categories', array('id' => $cat_id), 1)->row();
         if (!empty($categories)) {
            $services[$i]->category_name = $categories->c_name;
         } else {
            $services[$i]->category_name = "";
         }

         $res_id = $services[$i]->res_id;
         $store = $this->db->get_where('restaurants', array('res_id' => $res_id), 1)->row();
         if (!empty($store)) {
            $services[$i]->store_name = $store->res_name;
         } else {
            $services[$i]->store_name = "";
         }

         if (!empty($store)) {
            $services[$i]->store_address = $store->res_address;
         } else {
            $services[$i]->store_address = "";
         }

         if (!empty($store)) {
            $services[$i]->store_latitude = $store->lat;
         } else {
            $services[$i]->store_latitude = "";
         }

         if (!empty($store)) {
            $services[$i]->store_longitude = $store->lon;
         } else {
            $services[$i]->store_longitude = "";
         }

         $v_id = $services[$i]->v_id;
         $vendor = $this->db->get_where('vendor', array('id' => $v_id), 1)->row();
         if (!empty($vendor)) {
            $services[$i]->vendor_name = $vendor->uname;
         } else {
            $services[$i]->vendor_name = "";
         }

         $id = $services[$i]->id;
         $querycount = $this->db->query("SELECT A.rev_id,A.rev_service,A.rev_user,A.rev_stars,A.rev_text,A.rev_date, B.username,B.profile_pic FROM services_reviews A, user B WHERE A.rev_service = '$id' AND A.rev_user = B.id ORDER BY A.rev_id DESC");
         $mcount = $querycount->num_rows();
         $services[$i]->review_count = "$mcount";
      }

      if (empty($services)) {
         $result["response_code"] = "0";
         $result['message'] = "Services Not Found";
         $result['services'] = $services;
         $result["status"] = "failure";
         echo json_encode($result);
      } else {
         $result['response_code'] = "1";
         $result['message'] = "services Found";
         $result['services'] = $services;
         $result["status"] = "success";

         echo json_encode($result);
      }
   }
   
   public function get_all_services_pagination()
   {
      $result = array();
      header('Content-Type: application/json');

      // $query = $this->db->query("SELECT * FROM services ORDER BY id DESC");
      // $services = $query->result();

      $this->load->library('pagination');

      $limit = $_REQUEST['per_page'];
      $page_a = $_REQUEST['page'];
      // $user_id = $_REQUEST['user_id'];

      $page = $page_a - 1;

      $this->db->select('services.*');
      $this->db->from('services');
      $this->db->limit($limit, $page);
      $this->db->order_by("id", "desc");
      $query = $this->db->get();
      $services = $query->result();

      for ($i = 0; $i < count($services); $i++) {

        //  if (!empty($services[$i]->service_image)) {
        //     $services[$i]->service_image = base_url('uploads/service_images/') . $services[$i]->service_image;
        //  } else {
        //     $services[$i]->service_image = "";
        //  }
         
         if (!empty($services[$i]->service_image)) {
            $images = explode('::::', $services[$i]->service_image);
            $services[$i]->service_image = base_url('uploads/service_images/') . $images[0];
         } else {
            $services[$i]->service_image = "";
         }

         $cat_id = $services[$i]->cat_id;
         $categories = $this->db->get_where('categories', array('id' => $cat_id), 1)->row();
         if (!empty($categories)) {
            $services[$i]->category_name = $categories->c_name;
         } else {
            $services[$i]->category_name = "";
         }

         $res_id = $services[$i]->res_id;
         $store = $this->db->get_where('restaurants', array('res_id' => $res_id), 1)->row();
         if (!empty($store)) {
            $services[$i]->store_name = $store->res_name;
         } else {
            $services[$i]->store_name = "";
         }

         if (!empty($store)) {
            $services[$i]->store_address = $store->res_address;
         } else {
            $services[$i]->store_address = "";
         }

         if (!empty($store)) {
            $services[$i]->store_latitude = $store->lat;
         } else {
            $services[$i]->store_latitude = "";
         }

         if (!empty($store)) {
            $services[$i]->store_longitude = $store->lon;
         } else {
            $services[$i]->store_longitude = "";
         }

         $v_id = $services[$i]->v_id;
         $vendor = $this->db->get_where('vendor', array('id' => $v_id), 1)->row();
         if (!empty($vendor)) {
            $services[$i]->vendor_name = $vendor->uname;
         } else {
            $services[$i]->vendor_name = "";
         }

         $id = $services[$i]->id;
         $querycount = $this->db->query("SELECT A.rev_id,A.rev_service,A.rev_user,A.rev_stars,A.rev_text,A.rev_date, B.username,B.profile_pic FROM services_reviews A, user B WHERE A.rev_service = '$id' AND A.rev_user = B.id ORDER BY A.rev_id DESC");
         $mcount = $querycount->num_rows();
         $services[$i]->review_count = "$mcount";
      }

      if (empty($services)) {
         $result["response_code"] = "0";
         $result['message'] = "Services Not Found";
         $result['services'] = $services;
         $result["status"] = "failure";
         echo json_encode($result);
      } else {
         $result['response_code'] = "1";
         $result['message'] = "services Found";
         $result['services'] = $services;
         $result["status"] = "success";

         echo json_encode($result);
      }
   }

    public function get_service_details()
    {
        $result = array();
        header('Content-Type: application/json');

        if (isset($_POST['service_id'])) {

            $user_id = $this->input->post('user_id');
            $service_id = $this->input->post('service_id');
            $services = $this->db->get_where('services', array('id' => $service_id), 1);
            
            $services_row = $services->row();
            
            if ($services->num_rows() > 0) {

                $query = $this->db->query("SELECT A.rev_id,A.rev_service,A.rev_user,A.rev_stars,A.rev_text,A.rev_date, B.username,B.profile_pic FROM services_reviews A, user B WHERE A.rev_service = '$service_id' AND A.rev_user = B.id ORDER BY A.rev_id DESC");
                
                $reviews = $query->result();
                
                $mcount = $query->num_rows();
                $review_count = "$mcount";
                
                $res_id = $services_row->res_id;
                $store = $this->db->get_where('restaurants', array('res_id' => $res_id), 1)->row();
                if (!empty($store)) {
                    $store_name = $store->res_name;
                } else {
                    $store_name = "";
                }
                
                $images = explode('::::', $store->res_image);
            $imgs = array();
            $imgsa = array();
            foreach ($images as $key => $image) {
               $imgs = base_url('uploads/') . $image;

               array_push($imgsa, $imgs);
            }

            if (!empty($store)) {
               $store_image = $imgsa;
            } else {
               $store_image = [];
            }
            
                if (!empty($store)) {
                    $store_address = $store->res_address;
                } else {
                    $store_address = "";
                }

                if (!empty($store)) {
                    $store_latitude = $store->lat;
                } else {
                    $store_latitude = "";
                }

                if (!empty($store)) {
                    $store_longitude = $store->lon;
                } else {
                    $store_longitude = "";
                }

                for ($i = 0; $i < count($reviews); $i++) {

                    $user_id = $reviews[$i]->rev_user;
                    
                    $user = $this->front_model->get_rev_by_id_user($user_id);
                    
                    $rev_user_data = $this->db->get_where("user", array("id" => $reviews[$i]->rev_user))->row();
                    if ($rev_user_data) {
                    if ($rev_user_data->profile_pic) {
                        $rev_user_data->profile_pic = base_url("uploads/profile_pics/" . $rev_user_data->profile_pic);
                    }
                }
                $reviews[$i]->rev_user_data = $rev_user_data;

                if (empty($user->username)) {
                    $username = "";
                } else {
                    $username = $user->username;
                }

                    $reviews[$i]->rev_user = $username;
            }

            $cname_get = $services->row();
            $catnm = $this->db->get_where('categories', array('id' => $cname_get->cat_id), 1)->row();
            if (!empty($catnm)) {
               $c_name = $catnm->c_name;
            } else {
               $c_name = "";
            }

            $v_id = $services_row->v_id;
            $vendor = $this->db->get_where('vendor', array('id' => $v_id), 1)->row();
            if (!empty($vendor)) {
               $vendor_name = $vendor->uname;
            } else {
               $vendor_name = "";
            }

            if (!empty($services_row->service_image)) {
               $images = explode('::::', $services_row->service_image);
               $imgs = array();
               $service_image = array();
               foreach ($images as $key => $image) {
                  $imgs = base_url('uploads/service_images/') . $image;

                  array_push($service_image, $imgs);
               }
            } else {
               $service_image = [];
            }
            
            $booking = $this->db->order_by("id", "desc")->get_where('booking', array('user_id' => $user_id, 'service_id' => $service_id), 1)->row();
            if (!empty($booking)) {
                if ($booking->status == 'Completed') {
                    $is_rating = "true";
                }else{
                    $is_rating = "false";
                }
            }else{
                $is_rating = "false";
            }

            $result['status'] = 1;
            $result['msg'] = "Restaurnat Found";
            $result['restaurant'] = $services->row();
            $result['restaurant']->service_image = $service_image;
            $result['restaurant']->category_name = $c_name;
            $result['restaurant']->store_name = $store_name;
            $result['restaurant']->store_image = $store_image;
            $result['restaurant']->store_address = $store_address;
            $result['restaurant']->store_latitude = $store_latitude;
            $result['restaurant']->store_longitude = $store_longitude;
            $result['restaurant']->vendor_name = $vendor_name;
            $result['restaurant']->review_count = $review_count;
            $result['restaurant']->is_rating = $is_rating;

            $result['review'] = $reviews;

            echo json_encode($result);
         } else {
            $result['status'] = 0;
            $result['msg'] = "Restaurnat not Found";

            echo json_encode($result);
         }
      }
   }
   
    public function get_service_by_categorie()
    {
        header('Content-Type: application/json');
        if (isset($_POST['cat_id'])) {
             $cat_id = $this->input->post('cat_id');
             $likes = $this->db->select('services.*')
                ->from('services')
                ->where(array('cat_id' => $cat_id))
                ->order_by('service_ratings', 'DESC')
                ->get();

            if ($likes->num_rows() > 0) {

                foreach ($likes->result() as $service) {
                   //$service->res_image = base_url().'uploads/'.$service->res_image;
    
                    $catnm = $this->db->get_where('categories', array('id' => $service->cat_id), 1)->row();
                    if (!empty($catnm)) {
                        $service->c_name = $catnm->c_name;
                    } else {
                        $service->c_name = "";
                    }
    
                    // if (!empty($service->service_image)) {
                    //     $service->service_image = base_url('uploads/service_images/') . $service->service_image;
                    // } else {
                    //     $service->service_image = "";
                    // }
                    
                    if (!empty($service->service_image)) {
                        $images = explode('::::', $service->service_image);
                        $service->service_image = base_url('uploads/service_images/') . $images[0];
                    } else {
                        $service->service_image = "";
                    }
    
                    $resid = $service->res_id;
                    $querycount = $this->db->query("SELECT A.rev_id,A.rev_res,A.rev_user,A.rev_stars,A.rev_text,A.rev_date, B.username,B.profile_pic FROM reviews A, user B WHERE A.rev_res = '$resid' AND A.rev_user = B.id ORDER BY A.rev_id DESC");
                    $mcount = $querycount->num_rows();
                    
                    $service->review_count = "$mcount";
                    
                    $vid = $service->v_id;
                    $vendor = $this->db->get_where("vendor", array("id" => $vid))->row();
                    
                    if (!empty($vendor)) {
                        $service->vendor_name = $vendor->uname;
                    } else {
                        $service->vendor_name = "";
                    }
    
                    $res_id = $service->res_id;
                    $store = $this->db->get_where('restaurants', array('res_id' => $res_id), 1)->row();
                    if (!empty($store)) {
                        $service->store_name = $store->res_name;
                    } else {
                        $service->store_name = "";
                    }
    
                    if (!empty($store)) {
                        $service->store_address = $store->res_address;
                    } else {
                        $service->store_address = "";
                    }
    
                    if (!empty($store)) {
                        $service->store_latitude = $store->lat;
                    } else {
                        $service->store_latitude = "";
                    }
    
                    if (!empty($store)) {
                        $service->store_longitude = $store->lon;
                    } else {
                        $service->store_longitude = "";
                    }
    
                    $reviewsa = array();
                    
                    $query = $this->db->query("SELECT A.rev_id,A.rev_service,A.rev_user,A.rev_stars,A.rev_text,A.rev_date, B.username,B.profile_pic FROM services_reviews A, user B WHERE A.rev_service = '$service->id' AND A.rev_user = B.id ORDER BY A.rev_id DESC");
                    $reviewsp = $query->result();
                    
                    for ($iac = 0; $iac < count($reviewsp); $iac++) {
                        $service_id = $reviewsp[$iac]->rev_service;
                        $user_id = $reviewsp[$iac]->rev_user;
                        
                        $services = $this->db->get_where('services', array('id' => $service_id), 1)->row();
                        
                        // $restaurantac = $this->front_model->get_res_by_id($res_id);
                        
                        $userac = $this->front_model->get_rev_by_id_user($user_id);
                        
                        $reviewsp[$iac]->rev_service = $services->service_name;
                        $rev_user_data = $this->db->get_where("user", array("id" => $reviewsp[$iac]->rev_user))->row();
                        if ($rev_user_data) {
                            if ($rev_user_data->profile_pic) {
                                $rev_user_data->profile_pic = base_url("uploads/profile_pics/" . $rev_user_data->profile_pic);
                            }
                        }
                        $reviewsp[$iac]->rev_user_data = $rev_user_data;
                        
                        if (empty($userac->username)) {
                            $username = "";
                        } else {
                            $username = $userac->username;
                        }
    
                        $reviewsp[$iac]->rev_user = $username;
                    }
    
                    $service->reviews = $reviewsp;
                }

                $result['response_code'] = "1";
                $result['message'] = "Service Found";
                $result['service'] = $likes->result();
                $result["status"] = "success";
                echo json_encode($result);
            } else {
                $result['response_code'] = "0";
                $result['message'] = "No service Found";
                $result['service'] = [];
                $result["status"] = "failure";
                echo json_encode($result);
            }
        } else {
            $result['response_code'] = "0";
            $result['message'] = "Missing Fields";
            $result["status"] = "failure";
            echo json_encode($result);
        }
    }
    
    public function get_services_by_store_id()
    {
        $result = array();
        header('Content-Type: application/json');
        
        $store_id = $this->input->post('res_id');
        
        if (empty($store_id)) {
            $result["response_code"] = "0";
            $result["message"] = "Enter Data";
            $result["status"] = "failure";
            echo json_encode($result);
            return;
        }

        $query = $this->db->query("SELECT * FROM services WHERE res_id = '$store_id' ORDER BY id DESC");
        $services = $query->result();
        
        for ($i = 0; $i < count($services); $i++) {

         // if (!empty($services[$i]->service_image)) {
         //    $services[$i]->service_image = base_url('uploads/service_images/') . $services[$i]->service_image;
         // } else {
         //    $services[$i]->service_image = "";
         // }

            if (!empty($services[$i]->service_image)) {
                $images = explode('::::', $services[$i]->service_image);
                $services[$i]->service_image = base_url('uploads/service_images/') . $images[0];
            } else {
                $services[$i]->service_image = "";
            }

            $cat_id = $services[$i]->cat_id;
            $categories = $this->db->get_where('categories', array('id' => $cat_id), 1)->row();
            if (!empty($categories)) {
                $services[$i]->category_name = $categories->c_name;
            } else {
                $services[$i]->category_name = "";
            }

            $res_id = $services[$i]->res_id;
            $store = $this->db->get_where('restaurants', array('res_id' => $res_id), 1)->row();
            if (!empty($store)) {
                $services[$i]->store_name = $store->res_name;
            } else {
                $services[$i]->store_name = "";
            }

            if (!empty($store)) {
                $services[$i]->store_address = $store->res_address;
            } else {
                $services[$i]->store_address = "";
            }

            if (!empty($store)) {
                $services[$i]->store_latitude = $store->lat;
            } else {
                $services[$i]->store_latitude = "";
            }

            if (!empty($store)) {
                $services[$i]->store_longitude = $store->lon;
            } else {
                $services[$i]->store_longitude = "";
            }

            $v_id = $services[$i]->v_id;
            $vendor = $this->db->get_where('vendor', array('id' => $v_id), 1)->row();
            if (!empty($vendor)) {
                $services[$i]->vendor_name = $vendor->uname;
            } else {
                $services[$i]->vendor_name = "";
            }

            $id = $services[$i]->id;
            $querycount = $this->db->query("SELECT A.rev_id,A.rev_service,A.rev_user,A.rev_stars,A.rev_text,A.rev_date, B.username,B.profile_pic FROM services_reviews A, user B WHERE A.rev_service = '$id' AND A.rev_user = B.id ORDER BY A.rev_id DESC");
            $mcount = $querycount->num_rows();
            $services[$i]->review_count = "$mcount";
        }

        if (empty($services)) {
            $result["response_code"] = "0";
            $result['message'] = "Services Not Found";
            $result['services'] = $services;
            $result["status"] = "failure";
            echo json_encode($result);
        } else {
            $result['response_code'] = "1";
            $result['message'] = "services Found";
            $result['services'] = $services;
            $result["status"] = "success";
            
            echo json_encode($result);
        }
    }
    
    public function home()
   {
      $result = array();
      header('Content-Type: application/json');

      $get_banners = $this->front_model->get_banners();
      $imgs = array();
      $banners = array();
      foreach ($get_banners as $key => $list) {
         $imgs = base_url('uploads/' . $list->image);
         array_push($banners, $imgs);
      }

      $category =  $this->db->get('categories')->result();
      foreach ($category as $key => $c_list) {
         if (!empty($c_list->image)) {
            $category[$key]->image = base_url('uploads/') . $c_list->image;
         }
         if (!empty($c_list->icon)) {
            $category[$key]->icon = base_url('uploads/') . $c_list->icon;
         }
      }

      $store = $this->db->get('restaurants')->result();
      for ($i = 0; $i < count($store); $i++) {

         $images = explode("::::", $store[$i]->res_image);
         $imgs = array();
         $imgsa = array();
         foreach ($images as $key => $image) {
            $imgs = base_url('uploads/') . $image;

            array_push($imgsa, $imgs);
         }

         $store[$i]->all_image = $imgsa;

         $imgsb = array();
         $imgsab = array();
         foreach ($images as $key => $image) {
            $imgsb['res_imag' . $key] = base_url('uploads/') . $image;
         }

         $store[$i]->res_image = $imgsb;
         if ($store[$i]->res_video == "") {
            $store[$i]->res_video = "";
         } else {
            $store[$i]->res_video = base_url() . 'uploads/' . $store[$i]->res_video;
         }
         $store[$i]->logo = base_url() . 'uploads/' . $store[$i]->logo;
         $catnm = $this->db->get_where('categories', array('id' => $store[$i]->cat_id), 1)->row();
         if (!empty($catnm)) {
            $store[$i]->c_name = $catnm->c_name;
         } else {
            $store[$i]->c_name = "";
         }

         if ($store[$i]->mfo == "") {
            $store[$i]->mfo = "";
         } else {
            $store[$i]->mfo = $store[$i]->mfo;
         }

         $resid = $store[$i]->res_id;
         $querycount = $this->db->query("SELECT A.rev_id,A.rev_res,A.rev_user,A.rev_stars,A.rev_text,A.rev_date, B.username,B.profile_pic FROM reviews A, user B WHERE A.rev_res = '$resid' AND A.rev_user = B.id ORDER BY A.rev_id DESC");
         $mcount = $querycount->num_rows();

         $store[$i]->review_count = "$mcount";

         $vid = $store[$i]->vid;
         $vendor = $this->db->get_where("vendor", array("id" => $vid))->row();

         if (!empty($vendor)) {
            $store[$i]->vendor_name = $vendor->uname;
         } else {
            $store[$i]->vendor_name = "";
         }

         $reviewsa = array();
         //$reviews = array();
         // $reviewsp = $this->front_model->get_rev_by_id_res($store[$i]->res_id);

         $res_id = $store[$i]->res_id;

         $query = $this->db->query("SELECT A.rev_id,A.rev_res,A.rev_user,A.rev_stars,A.rev_text,A.rev_date, B.username,B.profile_pic FROM reviews A, user B WHERE A.rev_res = '$res_id' AND A.rev_user = B.id ORDER BY A.rev_id DESC");

         $reviewsp = $query->result();

         for ($iac = 0; $iac < count($reviewsp); $iac++) {
            $res_id = $reviewsp[$iac]->rev_res;
            $user_id = $reviewsp[$iac]->rev_user;

            $restaurantac = $this->front_model->get_res_by_id($res_id);
            $userac = $this->front_model->get_rev_by_id_user($user_id);

            $reviewsp[$iac]->rev_res = $restaurantac->res_name;
            $rev_user_data = $this->db->get_where("user", array("id" => $reviewsp[$iac]->rev_user))->row();
            if ($rev_user_data) {
               if ($rev_user_data->profile_pic) {
                  $rev_user_data->profile_pic = base_url("uploads/profile_pics/" . $rev_user_data->profile_pic);
               }
            }
            $reviewsp[$iac]->rev_user_data = $rev_user_data;

            if (empty($userac->username)) {
               $username = "";
            } else {
               $username = $userac->username;
            }

            $reviewsp[$iac]->rev_user = $username;

            // array_push($reviewsa,$reviewsp);
         }

         $store[$i]->reviews = $reviewsp;
      }


      $query = $this->db->query("SELECT * FROM services ORDER BY id DESC");
      $services = $query->result();
      for ($i = 0; $i < count($services); $i++) {

         if (!empty($services[$i]->service_image)) {
            $images = explode('::::', $services[$i]->service_image);
            $services[$i]->service_image = base_url('uploads/service_images/') . $images[0];
         } else {
            $services[$i]->service_image = "";
         }

         $cat_id = $services[$i]->cat_id;
         $categories = $this->db->get_where('categories', array('id' => $cat_id), 1)->row();
         if (!empty($categories)) {
            $services[$i]->category_name = $categories->c_name;
         } else {
            $services[$i]->category_name = "";
         }

         $res_id = $services[$i]->res_id;
         $s_store = $this->db->get_where('restaurants', array('res_id' => $res_id), 1)->row();
         if (!empty($s_store)) {
            $services[$i]->store_name = $s_store->res_name;
         } else {
            $services[$i]->store_name = "";
         }

         if (!empty($s_store)) {
            $services[$i]->store_address = $s_store->res_address;
         } else {
            $services[$i]->store_address = "";
         }

         if (!empty($s_store)) {
            $services[$i]->store_latitude = $s_store->lat;
         } else {
            $services[$i]->store_latitude = "";
         }

         if (!empty($s_store)) {
            $services[$i]->store_longitude = $s_store->lon;
         } else {
            $services[$i]->store_longitude = "";
         }

         $v_id = $services[$i]->v_id;
         $vendor = $this->db->get_where('vendor', array('id' => $v_id), 1)->row();
         if (!empty($vendor)) {
            $services[$i]->vendor_name = $vendor->uname;
         } else {
            $services[$i]->vendor_name = "";
         }

         $id = $services[$i]->id;
         $querycount = $this->db->query("SELECT A.rev_id,A.rev_service,A.rev_user,A.rev_stars,A.rev_text,A.rev_date, B.username,B.profile_pic FROM services_reviews A, user B WHERE A.rev_service = '$id' AND A.rev_user = B.id ORDER BY A.rev_id DESC");
         $mcount = $querycount->num_rows();
         $services[$i]->review_count = "$mcount";
      }


      $result['response_code'] = "1";
      $result['message'] = "Found";
      if (!empty($banners)) {
         $result['banners'] = $banners;
      }else{
         $result['banners'] = [];
      }

      if (!empty($category)) {
         $result['category'] = $category;
      }else{
         $result['category'] = [];
      }

      if (!empty($store)) {
         $result['store'] = $store;
      }else{
         $result['store'] = [];
      }

      if (!empty($services)) {
         $result['services'] = $services;
      }else{
         $result['services'] = [];
      }

      echo json_encode($result);
   }
   
    public function reviews_service()
    {

        $result = array();
        header('Content-Type: application/json');
        
        if (isset($_POST['user_id']) && isset($_POST['service_id']) && isset($_POST['ratings']) && isset($_POST['text'])) {
            $data = array();
            $data['rev_user'] = $this->input->post('user_id');
            $data['rev_service'] = $this->input->post('service_id');
            $data['rev_stars'] = $this->input->post('ratings');
            $data['rev_text'] = $this->input->post('text');
            $data['rev_date'] = time();
            
            $services_reviews = $this->db->get_where('services_reviews', array('rev_user' => $data['rev_user'], 'rev_service' => $data['rev_service']), 1)->row();
            
            if (!empty($services_reviews)) {

                $u_data['rev_stars'] = $this->input->post('ratings');
                $u_data['rev_text'] = $this->input->post('text');
                
                $this->db->where('rev_user', $data['rev_user']);
                $this->db->where('rev_service', $data['rev_service']);
                $this->db->update('services_reviews', $u_data);
                
                // Get all reviews
                $reviews = $this->front_model->get_service_reviews($data['rev_service']);
                
                // make array
                $ratings = array(
                   '0' => $this->db->get_where('services_reviews', array("rev_service" => $data['rev_service'], "rev_stars" => "0"))->num_rows(),
                   '1.0' => $this->db->get_where('services_reviews', array("rev_service" => $data['rev_service'], "rev_stars" => "1.0"))->num_rows(),
                   '1.5' => $this->db->get_where('services_reviews', array("rev_service" => $data['rev_service'], "rev_stars" => "1.5"))->num_rows(),
                   '2.0' => $this->db->get_where('services_reviews', array("rev_service" => $data['rev_service'], "rev_stars" => "2.0"))->num_rows(),
                   '2.5' => $this->db->get_where('services_reviews', array("rev_service" => $data['rev_service'], "rev_stars" => "2.5"))->num_rows(),
                   '3.0' => $this->db->get_where('services_reviews', array("rev_service" => $data['rev_service'], "rev_stars" => "3.0"))->num_rows(),
                   '3.5' => $this->db->get_where('services_reviews', array("rev_service" => $data['rev_service'], "rev_stars" => "3.5"))->num_rows(),
                   '4.0' => $this->db->get_where('services_reviews', array("rev_service" => $data['rev_service'], "rev_stars" => "4.0"))->num_rows(),
                   '4.5' => $this->db->get_where('services_reviews', array("rev_service" => $data['rev_service'], "rev_stars" => "4.5"))->num_rows(),
                   '5.0' => $this->db->get_where('services_reviews', array("rev_service" => $data['rev_service'], "rev_stars" => "5.0"))->num_rows(),
                );

                // calculate reviews
                $rat_data['service_ratings'] = $this->calcAverageRating($ratings);
    
                $this->db->where('id', $data['rev_service']);
                $this->db->update('services', $rat_data);
    
                $result['status'] = "1";
                $result['msg'] = "Review Given";
                $result['preview'] = $data['rev_stars'];
    
                echo json_encode($result);
            } else {

                if ($this->db->insert('services_reviews', $data)) {
    
                    // Get all reviews
                    $reviews = $this->front_model->get_service_reviews($data['rev_service']);
    
                    // make array
                    $ratings = array(
                        '0' => $this->db->get_where('services_reviews', array("rev_service" => $data['rev_service'], "rev_stars" => "0"))->num_rows(),
                        '1.0' => $this->db->get_where('services_reviews', array("rev_service" => $data['rev_service'], "rev_stars" => "1.0"))->num_rows(),
                        '1.5' => $this->db->get_where('services_reviews', array("rev_service" => $data['rev_service'], "rev_stars" => "1.5"))->num_rows(),
                        '2.0' => $this->db->get_where('services_reviews', array("rev_service" => $data['rev_service'], "rev_stars" => "2.0"))->num_rows(),
                        '2.5' => $this->db->get_where('services_reviews', array("rev_service" => $data['rev_service'], "rev_stars" => "2.5"))->num_rows(),
                        '3.0' => $this->db->get_where('services_reviews', array("rev_service" => $data['rev_service'], "rev_stars" => "3.0"))->num_rows(),
                        '3.5' => $this->db->get_where('services_reviews', array("rev_service" => $data['rev_service'], "rev_stars" => "3.5"))->num_rows(),
                        '4.0' => $this->db->get_where('services_reviews', array("rev_service" => $data['rev_service'], "rev_stars" => "4.0"))->num_rows(),
                        '4.5' => $this->db->get_where('services_reviews', array("rev_service" => $data['rev_service'], "rev_stars" => "4.5"))->num_rows(),
                        '5.0' => $this->db->get_where('services_reviews', array("rev_service" => $data['rev_service'], "rev_stars" => "5.0"))->num_rows(),
                    );
    
                   // calculate reviews
                   $rat_data['service_ratings'] = $this->calcAverageRating($ratings);
    
                   $this->db->where('id', $data['rev_service']);
                   $this->db->update('services', $rat_data);
    
                   $result['status'] = "1";
                   $result['msg'] = "Review Given";
                   $result['preview'] = $data['rev_stars'];
    
                   echo json_encode($result);
                } else {
                   $result['status'] = "0";
                   $result['msg'] = "Database Error";
    
                   echo json_encode($result);
                }
            }
        } else {
            $result['status'] = "0";
            $result['msg'] = "Missing Data";
            
            echo json_encode($result);
        }
    }
    
    public function home_product()
    {
        $result = array();
        header('Content-Type: application/json');
        
        $product_category = $this->db->get('product_category')->result();
        
        for ($i = 0; $i < count($product_category); $i++) {
            if (!empty($product_category[$i]->image)) {
                $product_category[$i]->image = base_url() . 'uploads/' . $product_category[$i]->image;
            }
        }

        $products = $this->db->get('products')->result();
        
        for ($i = 0; $i < count($products); $i++) {

            $images = explode("::::", $products[$i]->product_image);
            
            $imgs = array();
            $imgsa = array();
            foreach ($images as $key => $image) {

                $imgs = base_url() . 'uploads/product_images/' . $image;
    
                array_push($imgsa, $imgs);
            }

            if (!empty($products[$i]->product_image)) {
                $products[$i]->product_image = $imgsa;
            } else {
                $products[$i]->product_image = [];
            }
        }

        $result['response_code'] = "1";
        $result['message'] = "Found";
        
        if (!empty($product_category)) {
            $result['category'] = $product_category;
        } else {
            $result['category'] = [];
        }

        if (!empty($products)) {
            $result['products'] = $products;
        } else {
            $result['products'] = [];
        }

        echo json_encode($result);
    }
    
    public function booking_cancel_by_user()
   {
      header('Content-Type: application/json');

      $id = $this->input->post('id');
      $status = $this->input->post('status');

      if ($_POST['id'] == "") {

         $temp["response_code"] = "0";
         $temp["message"] = "Enter Data";
         $temp["status"] = "failure";
         echo json_encode($temp);
         return;
      }

      $data = array();

      $data['status'] = $status;
      $this->db->where('id', $id);
      $update = $this->db->update('booking', $data);

      $booking = $this->db->get_where('booking', array('id' => $id), 1)->row();

      $user_id = $booking->user_id;

      $vid = $booking->vid;

      $service_id = $booking->service_id;
      $service = $this->db->get_where('services', array('id' => $service_id), 1)->row();

      $user = $this->db->get_where('user', array('id' => $user_id), 1)->row();
      $v_title = "Booking Cancel";
      $v_message = "" . $user->username . " Booking Cancel for service " . $service->service_name . ".";

      $response = $this->firebase_model->send_vendor_notification($vid, $v_title, $v_message, "Message");
      $this->firebase_model->save_vendor_notification($vid, $v_title, $v_message,  "booking", $id);

      if ($update) {
         $temp["response_code"] = "1";
         $temp["message"] = "Update Successfully";
         $temp["status"] = "success";
         echo json_encode($temp);
      } else {
         $temp["response_code"] = "0";
         $temp["message"] = "Database error";
         $temp["status"] = "failure";
         echo json_encode($temp);
      }
   }
   
}
