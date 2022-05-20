<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
{
   public function __construct()
   {
      parent::__construct();
      $this->load->model('HomeModel', 'homemodel');

      $this->load->library('session');
      $this->load->helper('form');
      $this->load->library('form_validation');

      $this->load->library('facebook');
   }

   public function index()
   {
      // $this->load->view('frontend/home');
      $data['page'] = 'home';
      $this->load->view('admin/template', $data);
   }

   public function sign_in()
   {
      // ============= Google Sign In ============= //
      require 'vendor/autoload.php';
      $google_client = new Google_Client();
      $google_client->setClientId('201185755505-brcc9bo42nt3qq6m5ktesk9fc4j6ddnr.apps.googleusercontent.com'); //Define your ClientID
      $google_client->setClientSecret('5QshueyBBQkgYYglWhXSNNtl'); //Define your Client Secret Key
      $google_client->setRedirectUri('http://localhost/E&M-Garage-Door/sign-in'); //Define your Redirect Uri
      $google_client->addScope('email');
      $google_client->addScope('profile');

      if (isset($_GET["code"])) {
         $token = $google_client->fetchAccessTokenWithAuthCode($_GET["code"]);

         if (!isset($token["error"])) {
            $google_client->setAccessToken($token['access_token']);
            $this->session->set_userdata('access_token', $token['access_token']);
            $google_service = new Google_Service_Oauth2($google_client);
            $data = $google_service->userinfo->get();

            $current_datetime = time();

            if ($this->homemodel->Is_already_register($data['email'])) {
               //update data
               $user_data = array(
                  'username' => $data['given_name'],
                  'email' => $data['email'],
                  'profile_pic' => $data['picture'],
                  'type' => "google",
                  'date' => $current_datetime
               );

               $user = $this->db->get_where('user', array('email' => $data['email']))->row_array();

               $this->session->set_userdata('user_id', $user['id']);
               $this->session->set_userdata('email', $user['email']);
               $this->session->set_userdata('username', $user['name']);
               $this->session->set_userdata('profile_pic', $user['img']);
               $this->session->set_userdata('type', $user['user_type']);

               // $this->homemodel->Update_user_data($user_data, $data['email']);
               // $this->session->set_userdata('user_data', $user_data);

               redirect(base_url('home'));
            } else {
               //insert data
               $user_data = array(
                  'username'  => $data['given_name'],
                  'email' => $data['email'],
                  'profile_pic' => $data['picture'],
                  'type' => "google",
                  'date' => $current_datetime
               );
               $this->homemodel->Insert_user_data($user_data);
               $user_data['user_id'] = $this->db->insert_id();

               $this->session->set_userdata('user_data', $user_data);
               redirect(base_url('home'));
            }
         }
      }
      $login_button = '';

      $login_button = '<a class="btn default-btn" href="' . $google_client->createAuthUrl() . '"><i class="fa fa-google" aria-hidden="true"></i> Login with Google</a>';
      $data['google_login'] = $login_button;
      // ============= Google Sign In ============= //

      $userData = array();
      // Authenticate user with facebook
      if ($this->facebook->is_authenticated()) {
         // Get user info from facebook 
         $fbUser = $this->facebook->request('get', '/me?fields=id,first_name,last_name,email,link,gender,picture');
         // Preparing data for database insertion 
         $userData['oauth_provider'] = 'facebook';
         $userData['oauth_uid']    = !empty($fbUser['id']) ? $fbUser['id'] : '';;
         $userData['first_name']    = !empty($fbUser['first_name']) ? $fbUser['first_name'] : '';
         $userData['last_name']    = !empty($fbUser['last_name']) ? $fbUser['last_name'] : '';
         $userData['email']        = !empty($fbUser['email']) ? $fbUser['email'] : '';
         $userData['gender']        = !empty($fbUser['gender']) ? $fbUser['gender'] : '';
         $userData['picture']    = !empty($fbUser['picture']['data']['url']) ? $fbUser['picture']['data']['url'] : '';
         $userData['link']        = !empty($fbUser['link']) ? $fbUser['link'] : 'https://www.facebook.com/';

         print_r($userData);
         die();
         // Insert or update user data to the database 
         $userID = $this->homemodel->checkUser($userData);

         // Check user data insert or update status 
         if (!empty($userID)) {
            $data['userData'] = $userData;

            // Store the user profile info into session 
            $this->session->set_userdata('userData', $userData);
         } else {
            $data['userData'] = array();
         }

         // Facebook logout URL 
         // $data['logoutURL'] = $this->facebook->logout_url();
         redirect(base_url('home'));
      } else {
         // Facebook authentication url 
         $data['facebook_login'] =  $this->facebook->login_url();
      }


      $data['page'] = 'sign_in';
      $this->load->view('admin/template', $data);
   }

   public function user_login()
   {

      $login = array(
         'email' => $this->input->post('email'),
         'password' => md5($this->input->post('password'))
      );

      $data = $this->homemodel->login_user($login['email'], $login['password']);
      if ($data) {
         $this->session->set_userdata('user_id', $data['id']);
         $this->session->set_userdata('email', $data['email']);
         $this->session->set_userdata('username', $data['username']);
         $this->session->set_userdata('profile_pic', $data['profile_pic']);

         redirect(base_url('home'));
      } else {
         $this->session->set_flashdata('error', 'Phone Number or Email Address And Password Wrong...');
         redirect(base_url('sign-in'));
      }
   }

   public function sign_up()
   {
      $data['page'] = 'sign_up';
      $this->load->view('admin/template', $data);
   }

   public function register()
   {
      $this->form_validation->set_rules('username', 'User name', 'required');
      $this->form_validation->set_rules('email', 'Email address', 'required');
      $this->form_validation->set_rules('password', 'Password', 'required');
      $this->form_validation->set_rules('cpassword', 'Confirm Password', 'required');

      $this->form_validation->set_error_delimiters('<span class="error" style="color:red;">', '</span>');
      if ($this->form_validation->run() == false) {
         redirect(base_url('sign-up'));
      } else {
         $data = array(
            'username' => $_REQUEST['username'],
            'email' => $_REQUEST['email'],
            'date' => time(),
         );

         $data['password'] = md5($this->input->post('password'));
         $cpassword = md5($this->input->post('cpassword'));
         if ($data['password']  == $cpassword) {
            $email_check = $this->homemodel->email_phone_check($data['email']);
            if ($email_check) {
               $check = $this->homemodel->add_user($data);
               if ($check) {
                  $this->session->set_flashdata('success', 'Added Successfully.');
                  redirect(base_url('sign-in'));
               }
            } else {
               $this->session->set_flashdata('error', 'Email address Already Registered');
               redirect(base_url('sign-up'));
            }
         }
         $this->session->set_flashdata('error', 'Password And Confirm Password Not Match...');
         redirect(base_url('sign-up'));
      }
      redirect(base_url('sign-in'));
   }

   function google_login()
   {
      require 'vendor/autoload.php';
      $google_client = new Google_Client();
      $google_client->setClientId('201185755505-brcc9bo42nt3qq6m5ktesk9fc4j6ddnr.apps.googleusercontent.com'); //Define your ClientID
      $google_client->setClientSecret('5QshueyBBQkgYYglWhXSNNtl'); //Define your Client Secret Key
      $google_client->setRedirectUri('http://localhost/E&M-Garage-Door/sign-in'); //Define your Redirect Uri
      $google_client->addScope('email');
      $google_client->addScope('profile');

      if (isset($_GET["code"])) {
         $token = $google_client->fetchAccessTokenWithAuthCode($_GET["code"]);

         if (!isset($token["error"])) {
            $google_client->setAccessToken($token['access_token']);
            $this->session->set_userdata('access_token', $token['access_token']);
            $google_service = new Google_Service_Oauth2($google_client);
            $data = $google_service->userinfo->get();

            $current_datetime = date('Y-m-d H:i:s');

            if ($this->google_login_model->Is_already_register($data['id'])) {
               //update data
               $user_data = array(
                  'first_name' => $data['given_name'],
                  'last_name'  => $data['family_name'],
                  'email_address' => $data['email'],
                  'profile_picture' => $data['picture'],
                  'updated_at' => $current_datetime
               );

               $this->google_login_model->Update_user_data($user_data, $data['id']);
            } else {
               //insert data
               $user_data = array(
                  'login_oauth_uid' => $data['id'],
                  'first_name'  => $data['given_name'],
                  'last_name'   => $data['family_name'],
                  'email_address'  => $data['email'],
                  'profile_picture' => $data['picture'],
                  'created_at'  => $current_datetime
               );

               $this->google_login_model->Insert_user_data($user_data);
            }
            $this->session->set_userdata('user_data', $user_data);
         }
      }
      $login_button = '';
      if (!$this->session->userdata('access_token')) {
         $login_button = '<a href="' . $google_client->createAuthUrl() . '"><img src="' . base_url() . 'asset/sign-in-with-google.png" /></a>';
         $data['login_button'] = $login_button;
         $this->load->view('google_login', $data);
      } else {
         $this->load->view('google_login', $data);
      }
   }

   public function logout()
   {
      $this->session->sess_destroy();
      $this->session->unset_userdata('access_token');
      $this->session->unset_userdata('user_data');
      redirect(base_url('sign-in'));
   }

   public function facebook_login()
   {
      $userData = array();

      // Authenticate user with facebook 
      if ($this->facebook->is_authenticated()) {
         // Get user info from facebook 
         $fbUser = $this->facebook->request('get', '/me?fields=id,first_name,last_name,email,link,gender,picture');

         // Preparing data for database insertion 
         $userData['oauth_provider'] = 'facebook';
         $userData['oauth_uid']    = !empty($fbUser['id']) ? $fbUser['id'] : '';;
         $userData['first_name']    = !empty($fbUser['first_name']) ? $fbUser['first_name'] : '';
         $userData['last_name']    = !empty($fbUser['last_name']) ? $fbUser['last_name'] : '';
         $userData['email']        = !empty($fbUser['email']) ? $fbUser['email'] : '';
         $userData['gender']        = !empty($fbUser['gender']) ? $fbUser['gender'] : '';
         $userData['picture']    = !empty($fbUser['picture']['data']['url']) ? $fbUser['picture']['data']['url'] : '';
         $userData['link']        = !empty($fbUser['link']) ? $fbUser['link'] : 'https://www.facebook.com/';

         print_r($userData);
         die();

         // Insert or update user data to the database 
         $userID = $this->user->checkUser($userData);

         // Check user data insert or update status 
         if (!empty($userID)) {
            $data['userData'] = $userData;

            // Store the user profile info into session 
            $this->session->set_userdata('userData', $userData);
         } else {
            $data['userData'] = array();
         }

         // Facebook logout URL 
         $data['logoutURL'] = $this->facebook->logout_url();
      } else {
         // Facebook authentication url 
         $data['authURL'] =  $this->facebook->login_url();
      }

      // Load login/profile view 
      $this->load->view('user_authentication/index', $data);
   }

   public function services()
   {
      // $this->load->view('frontend/services');
      $data['page'] = 'services';
      $this->load->view('admin/template', $data);
   }

   public function category_services()
   {
      // $this->load->view('frontend/category_services_new');
      $data['page'] = 'category_services';
      $this->load->view('admin/template', $data);
   }

   public function services_details()
   {
      $data['page'] = 'services_details';
      $this->load->view('admin/template', $data);
   }

   public function book_service()
   {
      $data['page'] = 'book_service';
      $this->load->view('admin/template', $data);
   }

   public function confirm_booking()
   {
      if ($this->session->userdata('user_id') == "") {
         redirect(base_url('sign-in'));
      }

      $data = array(
         'res_id' => $this->input->post('res_id'),
         'user_id' => $this->input->post('user_id'),
         'date' => $this->input->post('date'),
         'slot' => $this->input->post('slot'),
         'address' => $this->input->post('address')
      );

      $data['page'] = 'booking_checkout';
      $this->load->view('admin/template', $data);

      // $like['res_id'] = $this->input->post('res_id');
      // $like['user_id'] = $this->input->post('user_id');
      // $like['date'] = $this->input->post('date');
      // $like['slot'] = $this->input->post('slot');
      // $like['address'] = $this->input->post('address');
      // $like['status'] = "Pending";
      // $like['p_status'] = "Pending";
      // $like['create_date'] = date("d M, H:i A");

      // if ($this->db->insert('booking', $like)) {
      //    $booking_id = $this->db->insert_id();
      //    // $this->session->set_flashdata('success', 'Successfully.');
      //    redirect('booking-checkout/' . $booking_id);
      // } else {
      //    $this->session->set_flashdata('error', 'Database Error.');
      //    $data['page'] = 'book_service';
      //    $this->load->view('admin/template', $data);
      // }
      // redirect(base_url('booking-checkout/' . $booking_id));
   }

   public function booking_checkout()
   {
      if ($this->session->userdata('user_id') == "") {
         redirect(base_url('sign-in'));
      }

      $booking_id = $this->uri->segment(2);
      $data['booking'] = $this->db->get_where('booking', array('id' => $booking_id), 1)->row();

      $data['page'] = 'booking_checkout';
      $this->load->view('admin/template', $data);
   }

   public function place_order()
   {
      if ($this->session->userdata('user_id') == "") {
         redirect(base_url('sign-in'));
      }

      $res_id = $this->input->post('res_id');
      $user_id = $this->input->post('user_id');
      $date = $this->input->post('date');
      $slot = $this->input->post('slot');
      $address = $this->input->post('address');
      $p_status = "Success";
      $create_date = date("d M, H:i A");

      // $booking_id = $this->input->post('booking_id');
      $grand_total = $this->input->post('grand_total');

      $stripeToken = $this->input->post('stripeToken');
      if (!empty($stripeToken)) {
         require_once('application/libraries/stripe/init.php');

         \Stripe\Stripe::setApiKey($this->config->item('stripe_secret'));

         // \Stripe\Charge::create([
         //    "amount" => $grand_total * 100,
         //    "currency" => "USD",
         //    "source" => $this->input->post('stripeToken'),
         //    "description" => "Test payment from booking"
         // ]);

         try {
            // Charge a credit or a debit card 
            $charge = \Stripe\Charge::create(array(
               'amount'   => $grand_total * 100,
               'currency' => "USD",
               "source" => $this->input->post('stripeToken'),
               "description" => "Test payment from booking",
            ));


            // Retrieve charge details 
            $chargeJson = $charge->jsonSerialize();
            $this->session->set_flashdata('success', 'Payment made successfully.');

            $txn_id = $charge->id;
            $booking_data = array(
               'status' => 'Confirm',
               'payment_mode' => 'Stripe',
               'p_date' => date("d M, H:i A"),
               'txn_id' => $txn_id,
               'date' => $date,
               'res_id' => $res_id,
               'user_id' => $user_id,
               'slot' => $slot,
               'amount' => $grand_total,
               'address' => $address,
               'p_status' => $p_status,
               'create_date' => $create_date,
            );
            // $this->db->where('id', $booking_id);
            $this->db->insert('booking', $booking_data);

            redirect('home');
            return $chargeJson;
         } catch (Exception $e) {
            $this->api_error = $e->getMessage();
            redirect(base_url('services'));
            return false;
         }

         // $this->session->set_flashdata('success', 'Payment made successfully.');

         redirect(base_url('services'));
      }
      redirect(base_url('services'));
   }

   public function booking_cod()
   {
      if ($this->session->userdata('user_id') == "") {
         redirect(base_url('sign-in'));
      }

      $res_id = $this->input->post('res_id');
      $user_id = $this->input->post('user_id');
      $date = $this->input->post('date');
      $slot = $this->input->post('slot');
      $address = $this->input->post('address');
      $p_status = "Pending";
      $create_date = date("d M, H:i A");

      $grand_total = $this->input->post('grand_total');

      $booking_data = array(
         'status' => 'Confirm',
         'payment_mode' => 'Cash/Cheque On Delivery',
         'p_date' => date("d M, H:i A"),

         'date' => $date,
         'res_id' => $res_id,
         'user_id' => $user_id,
         'slot' => $slot,
         'amount' => $grand_total,
         'address' => $address,
         'p_status' => $p_status,
         'create_date' => $create_date,
      );

      if ($this->db->insert('booking', $booking_data)) {
         $this->session->set_flashdata('success', 'successfully Updated.');
         redirect('home');
      } else {
         $this->session->set_flashdata('error', 'Database error.');
         redirect(base_url('services'));
      }
      redirect(base_url('services'));
   }

   public function booking_razorpay_payment()
   {
      if ($this->session->userdata('user_id') == "") {
         redirect(base_url('sign-in'));
      }

      $res_id = $_REQUEST['res_id'];
      $user_id = $_REQUEST['user_id'];
      $price = $_REQUEST['amount'];
      $txn_id = $_REQUEST['razorpay_payment_id'];


      $date = $_REQUEST['date'];
      $slot = $_REQUEST['slot'];
      $address = $_REQUEST['address'];

      $p_status = "Success";
      $create_date = date("d M, H:i A");

      $booking_data = array(
         'status' => 'Confirm',
         'payment_mode' => 'Razorpay',
         'txn_id' => $txn_id,
         'p_date' => date("d M, H:i A"),
         'date' => $date,
         'res_id' => $res_id,
         'user_id' => $user_id,
         'slot' => $slot,
         'amount' => $price,
         'address' => $address,
         'p_status' => $p_status,
         'create_date' => $create_date,
      );
      // $this->db->where('id', $booking_id);
      // if ($this->db->update('booking', $booking_data)) {
      if ($this->db->insert('booking', $booking_data)) {
         $this->session->set_flashdata('success', 'successfully Updated.');
         redirect('home');
      } else {
         $this->session->set_flashdata('error', 'Database error.');
         redirect(base_url('services'));
      }
      redirect(base_url('services'));
   }

   public function booking_history()
   {
      $data['page'] = 'booking_history';
      $this->load->view('admin/template', $data);
   }
}
