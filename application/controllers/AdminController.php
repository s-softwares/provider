<?php defined('BASEPATH') or exit('No direct script access allowed');
class AdminController extends CI_Controller
{
   public function __construct()
   {
      parent::__construct();
      $this->load->helper('url');
      $this->load->model('admin_model');
      $this->load->model('firebase_model');
      $this->load->library('session');

      $this->load->helper('form');
      $this->load->library('form_validation');
   }

   public function login()
   {
      $this->load->view('admin/login');
   }

   public function login_admin()
   {
      $login = array(
         'email' => $this->input->post('email'),
         'password' => md5($this->input->post('password'))
      );

      $data = $this->admin_model->login_user($login['email'], $login['password']);
      if ($data) {
         $this->session->set_userdata('aid', $data['id']);
         $this->session->set_userdata('aemail', $data['email']);
         $this->session->set_userdata('aname', $data['uname']);
         $this->session->set_userdata('aimg', $data['profile_image']);
         $this->session->set_userdata('user_type', $data['user_type']);

         redirect(base_url('admin/dashboard'));
      } else {
         $this->session->set_flashdata('error', 'Email Id And Password Wrong..');
         redirect(base_url('admin/login'));
      }
   }

   public function logout()
   {
      $this->session->sess_destroy();
      redirect(base_url() . 'admin/login', 'refresh');
   }

   public function admin_profile()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }
      $data['users'] = $this->admin_model->get_users();

      // $data['users'] = $this->admin_model->get_users(); 
      // $this->load->view("Admin/user.php",$data);

      $data['page'] = 'profile';
      $this->load->view('admin/template', $data);
   }

   public function admin_edit()
   {

      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      // $id = $this->uri->segment(3);
      $id = $this->session->userdata('aid');

      if (empty($id)) {
         show_404();
      }

      $this->load->helper('form');
      $this->load->library('form_validation');
      $this->form_validation->set_error_delimiters('<span class="error" style="color:red;">', '</span>');

      $profile = $this->admin_model->get_admin($id);

      $this->form_validation->set_rules('email', 'Email', 'required');
      $this->form_validation->set_rules('uname', 'Name', 'required');

      if ($this->form_validation->run() === FALSE) {
         $data['page'] = 'profile';
         $this->load->view('admin/template', $data);
      } else {
         if ($_FILES['profile_image']['name'] == "") {
            $this->admin_model->set_user($id, $res_image = "");
            $this->session->set_flashdata('success', 'successfully Updated..');
            redirect(base_url('admin/profile'));
         } else {
            $image_exts = array("tif", "jpg", "jpeg", "gif", "png");

            $configVideo['upload_path'] = './uploads/profile_pics/'; # check path is correct
            $configVideo['max_size'] = '102400';
            $configVideo['allowed_types'] = $image_exts; # add video extenstion on here
            $configVideo['overwrite'] = FALSE;
            $configVideo['remove_spaces'] = TRUE;
            $configVideo['file_name'] = uniqid();

            $this->load->library('upload', $configVideo);
            $this->upload->initialize($configVideo);

            if (!$this->upload->do_upload('profile_image')) # form input field attribute
            {
               $this->session->set_flashdata('error', 'Image Type Error...');
               $data['page'] = 'profile';
               $this->load->view('admin/template', $data);
            } else {
               # Upload Successfull
               $upload_data = $this->upload->data();
               $res_image = $upload_data['file_name'];

               $this->admin_model->set_user($id, $res_image);
               $this->session->set_flashdata('success', 'successfully Updated..');
               redirect(base_url('admin/profile'));
            }
         }
      }
   }

   public function change_password()
   {

      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $id = $this->session->userdata('aid');

      if (empty($id)) {
         show_404();
      }

      $this->load->helper('form');
      $this->load->library('form_validation');

      $this->form_validation->set_rules('password', 'Password', 'required');
      $this->form_validation->set_rules('npassword', 'New Password', 'required');
      $this->form_validation->set_rules('cpassword', 'Confirm Password', 'required');
      $this->form_validation->set_error_delimiters('<span class="error" style="color:red;">', '</span>');

      if ($this->form_validation->run() === FALSE) {
         $data['page'] = 'profile';
         $this->load->view('admin/template', $data);
      } else {
         $password = md5($this->input->post('password'));
         $npassword = md5($this->input->post('npassword'));
         $cpassword = md5($this->input->post('cpassword'));

         if ($npassword == $cpassword) {
            $password_check = $this->admin_model->password_check($password, $id);

            if ($password_check) {
               $this->admin_model->change_pass($npassword, $id);
               $this->session->set_flashdata('success', 'Successfully Changed..');
               // redirect(base_url().'admin/profile/');
               redirect(base_url('admin/profile'));
            } else {
               $this->session->set_flashdata('error', 'Old Password Wrong..');
               redirect(base_url() . 'admin/profile/');
            }
         } else {
            $this->session->set_flashdata('error', 'New Password And Confirm Password Not Match..');
            redirect(base_url() . 'admin/profile/');
         }
      }
   }

   public function list_user()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      if ($this->session->userdata('user_type') != "admin") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'list_user';
      $this->load->view('admin/template', $data);
   }

   public function edit_user()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $id = $this->uri->segment(3);
      $data['page'] = 'edit_user';
      $data['user'] = $this->admin_model->get_user_by_id($id);
      $this->load->view('admin/template', $data);
   }

   public function update_user()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $id = $_REQUEST['id'];

      $this->form_validation->set_rules('username', 'UserName', 'required');
      $this->form_validation->set_rules('email', 'Email Id', 'required');

      $this->form_validation->set_error_delimiters('<span class="error" style="color:red;">', '</span>');
      if ($this->form_validation->run() == false) {
         //Error
      } else {
         $data = array(
            'username' => $_REQUEST['username'],
            'email' => $_REQUEST['email'],
         );

         if (!empty($_FILES['profile_pic']['name'])) {
            $config['upload_path'] = './uploads/profile_pics';
            $config['allowed_types'] = 'jpg|png|jpeg';
            $config['file_name'] = uniqid();
            $config['overwrite'] = TRUE;

            // Load and initialize upload library
            $this->load->library('upload');
            $this->upload->initialize($config);

            // Upload file to server
            if ($this->upload->do_upload('profile_pic')) {
               // Uploaded file data
               $fileData = $this->upload->data();
               $profile_pic = $fileData['file_name'];
            } else {
               $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
            }

            $data['profile_pic'] = $profile_pic;
         }

         $check = $this->admin_model->update_user_by_id($id, $data);
         if ($check) {
            $this->session->set_flashdata('success', 'User has been successfully Updated.');
            redirect('admin/user-list', $data);
         }
      }

      $data['page'] = 'edit_user';
      $data['user'] = $this->admin_model->get_user_by_id($id);
      $this->load->view('admin/template', $data);
   }

   public function trash_user()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      if (!empty($_REQUEST['id'])) {
         $id = $_REQUEST['id'];

         $this->db->where('rev_res', $id);
         $this->db->delete("reviews");

         $this->db->where('id', $id);
         $this->db->delete("user");
         $this->session->set_flashdata('del_success', 'User has been Successfully Deleted.');
         redirect('admin/user-list');
      }
   }

   public function list_vendor()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'list_vendor';
      $this->load->view('admin/template', $data);
   }

   public function create_vendor()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'add_vendor';
      $this->load->view('admin/template', $data);
   }

   public function add_vendor()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $this->form_validation->set_rules('uname', 'User Name', 'required');
      $this->form_validation->set_rules('email', 'Email', 'required');
      $this->form_validation->set_rules('password', 'Password', 'required');
      $this->form_validation->set_rules('cpassword', 'Confirm Password', 'required');

      $this->form_validation->set_error_delimiters('<span class="error" style="color:red;">', '</span>');
      if ($this->form_validation->run() == false) {
         //Error
      } else {
         $data = array(
            'uname' => $_REQUEST['uname'],
            'email' => $_REQUEST['email'],
            'mobile' => $_REQUEST['mobile'],
            'date' => time(),
         );

         if (!empty($_FILES['profile_image']['name'])) {
            $config['upload_path'] = './uploads/profile_pics';
            $config['allowed_types'] = 'jpg|png|jpeg';
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
            }

            $data['profile_image'] = $profile_image;
         }

         $data['password'] = md5($this->input->post('password'));
         $cpassword = md5($this->input->post('cpassword'));
         if ($data['password']  == $cpassword) {

            $check = $this->admin_model->add_vendor($data);
            if ($check) {
               $this->session->set_flashdata('add_success', 'Category has been added Successfully.');
               redirect('admin/vendor-list');
            }
         }

         $this->session->set_flashdata('error', 'Password And Confirm Password Not Match..');
         redirect('admin/add-vendor');
      }
      $data['page'] = 'add_vendor';
      $this->load->view('admin/template', $data);
   }

   public function edit_vendor()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $id = $this->uri->segment(3);
      $data['page'] = 'edit_vendor';
      $data['vendor'] = $this->admin_model->get_vendor_by_id($id);
      $this->load->view('admin/template', $data);
   }

   public function update_vendor()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $id = $_REQUEST['id'];

      $this->form_validation->set_rules('uname', 'User Name', 'required');
      $this->form_validation->set_rules('email', 'Email', 'required');

      $this->form_validation->set_error_delimiters('<span class="error" style="color:red;">', '</span>');
      if ($this->form_validation->run() == false) {
         //Error
      } else {
         $data = array(

            'uname' => $_REQUEST['uname'],
            'email' => $_REQUEST['email'],
            'mobile' => $_REQUEST['mobile'],
         );

         if (!empty($_FILES['profile_image']['name'])) {
            $config['upload_path'] = './uploads/profile_pics';
            $config['allowed_types'] = 'jpg|png|jpeg';
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
            }

            $data['profile_image'] = $profile_image;
         }

         if ($this->input->post('password') != "") {
            $data['password'] = md5($this->input->post('password'));
         }

         $check = $this->admin_model->update_vendor_by_id($id, $data);
         if ($check) {
            $this->session->set_flashdata('success', 'Category has been successfully Updated.');
            redirect('admin/vendor-list', $data);
         }
      }

      $data['page'] = 'edit_vendor';
      $data['vendor'] = $this->admin_model->get_vendor_by_id($id);
      $this->load->view('admin/template', $data);
   }

   public function trash_vendor()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      if (!empty($_REQUEST['id'])) {
         $id = $_REQUEST['id'];

         $this->db->where('vid', $id);
         $this->db->delete("restaurants");

         $this->db->where('id', $id);
         $this->db->delete("vendor");
         $this->session->set_flashdata('del_success', 'User has been Successfully Deleted.');
         redirect('admin/vendor-list');
      }
   }

   public function list_category()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'list_category';
      $this->load->view('admin/template', $data);
   }

   public function create_category()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'add_category';
      $this->load->view('admin/template', $data);
   }

   public function add_category()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $this->form_validation->set_rules('c_name', 'Category Name', 'required|is_unique[categories.c_name]');

      $this->form_validation->set_error_delimiters('<span class="error" style="color:red;">', '</span>');
      if ($this->form_validation->run() == false) {
         //Error
      } else {
         $data = array(
            'c_name' => $_REQUEST['c_name'],
            // 'type' => $_REQUEST['type'],
         );

         if (!empty($_FILES['img']['name'])) {
            $config['upload_path'] = './uploads';
            $config['allowed_types'] = 'jpg|png|jpeg';
            $config['file_name'] = uniqid();
            $config['overwrite'] = TRUE;

            // Load and initialize upload library
            $this->load->library('upload');
            $this->upload->initialize($config);

            // Upload file to server
            if ($this->upload->do_upload('img')) {
               // Uploaded file data
               $fileData = $this->upload->data();
               $img = $fileData['file_name'];
            } else {
               $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
            }

            $data['img'] = $img;
         }

         $icon = "";
         if (!empty($_FILES['icon']['name'])) {
            $config['upload_path'] = './uploads';
            $config['allowed_types'] = 'jpg|png|jpeg';
            $config['file_name'] = uniqid();
            $config['overwrite'] = TRUE;

            // Load and initialize upload library
            $this->load->library('upload');
            $this->upload->initialize($config);

            // Upload file to server
            if ($this->upload->do_upload('icon')) {
               // Uploaded file data
               $fileData = $this->upload->data();
               $icon = $fileData['file_name'];
            } else {
               $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
            }

            $data['icon'] = $icon;
         }

         $check = $this->admin_model->add_category($data);
         if ($check) {
            $this->session->set_flashdata('add_success', 'Category has been added Successfully.');
            redirect('admin/category-list');
         }
      }
      $data['page'] = 'add_category';
      $this->load->view('admin/template', $data);
   }

   public function edit_category()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $id = $this->uri->segment(3);
      $data['page'] = 'edit_category';
      $data['category'] = $this->admin_model->get_category_by_id($id);
      $this->load->view('admin/template', $data);
   }

   public function update_category()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $cat_id = $_REQUEST['id'];

      $original_value = $this->db->query("SELECT c_name FROM categories WHERE id = " . $cat_id)->row()->c_name;
      if ($_REQUEST['c_name'] != $original_value) {
         $is_unique =  '|is_unique[categories.c_name]';
      } else {
         $is_unique =  '';
      }

      $this->form_validation->set_rules('c_name', 'Category Name', 'required|trim' . $is_unique);

      $this->form_validation->set_error_delimiters('<span class="error" style="color:red;">', '</span>');
      if ($this->form_validation->run() == false) {
         //Error
      } else {
         $data = array(
            'c_name' => $_REQUEST['c_name'],
         );

         if (!empty($_FILES['img']['name'])) {
            $config['upload_path'] = './uploads';
            $config['allowed_types'] = 'jpg|png|jpeg';
            $config['file_name'] = uniqid();
            $config['overwrite'] = TRUE;

            // Load and initialize upload library
            $this->load->library('upload');
            $this->upload->initialize($config);

            // Upload file to server
            if ($this->upload->do_upload('img')) {
               // Uploaded file data
               $fileData = $this->upload->data();
               $img = $fileData['file_name'];
            } else {
               $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
            }

            $data['img'] = $img;
         }

         if (!empty($_FILES['icon']['name'])) {
            $config['upload_path'] = './uploads';
            $config['allowed_types'] = 'jpg|png|jpeg';
            $config['file_name'] = uniqid();
            $config['overwrite'] = TRUE;

            // Load and initialize upload library
            $this->load->library('upload');
            $this->upload->initialize($config);

            // Upload file to server
            if ($this->upload->do_upload('icon')) {
               // Uploaded file data
               $fileData = $this->upload->data();
               $icon = $fileData['file_name'];
            } else {
               $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
            }

            $data['icon'] = $icon;
         }

         $check = $this->admin_model->update_category_by_id($cat_id, $data);
         if ($check) {
            $this->session->set_flashdata('update_success', 'Category has been successfully Updated.');
            redirect('admin/category-list', $data);
         }
      }

      $data['page'] = 'edit_category';
      $data['category'] = $this->admin_model->get_category_by_id($cat_id);
      $this->load->view('admin/template', $data);
   }

   public function trash_category()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      if (!empty($_REQUEST['id'])) {
         $id = $_REQUEST['id'];

         $this->db->where('cat_id', $id);
         $this->db->delete("sub_categories");

         $this->db->where('id', $id);
         $this->db->delete("categories");
         $this->session->set_flashdata('del_success', 'Category has been Successfully Deleted.');
         redirect('admin/category-list');
      }
   }

   public function list_sub_category()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'list_sub_category';
      $this->load->view('admin/template', $data);
   }

   public function create_sub_category()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'add_sub_category';
      $this->load->view('admin/template', $data);
   }

   public function add_sub_category()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $this->form_validation->set_rules('c_name', 'Sub Category Name', 'required');
      $this->form_validation->set_rules('cat_id', 'Category Name', 'required');

      $this->form_validation->set_error_delimiters('<span class="error" style="color:red;">', '</span>');
      if ($this->form_validation->run() == false) {
         //Error
      } else {
         $data = array(
            'cat_id' => $_REQUEST['cat_id'],
            'c_name' => $_REQUEST['c_name'],
            'type' => $_REQUEST['type'],
         );

         if (!empty($_FILES['img']['name'])) {
            $config['upload_path'] = './uploads';
            $config['allowed_types'] = 'jpg|png|jpeg';
            $config['file_name'] = uniqid();
            $config['overwrite'] = TRUE;

            // Load and initialize upload library
            $this->load->library('upload');
            $this->upload->initialize($config);

            // Upload file to server
            if ($this->upload->do_upload('img')) {
               // Uploaded file data
               $fileData = $this->upload->data();
               $img = $fileData['file_name'];
            } else {
               $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
            }

            $data['img'] = $img;
         }

         if (!empty($_FILES['icon']['name'])) {
            $config['upload_path'] = './uploads';
            $config['allowed_types'] = 'jpg|png|jpeg';
            $config['file_name'] = uniqid();
            $config['overwrite'] = TRUE;

            // Load and initialize upload library
            $this->load->library('upload');
            $this->upload->initialize($config);

            // Upload file to server
            if ($this->upload->do_upload('icon')) {
               // Uploaded file data
               $fileData = $this->upload->data();
               $icon = $fileData['file_name'];
            } else {
               $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
            }

            $data['icon'] = $icon;
         }

         $check = $this->admin_model->add_sub_category($data);
         if ($check) {
            $this->session->set_flashdata('add_success', 'Category has been added Successfully.');
            redirect('admin/sub-category-list');
         }
      }
      $data['page'] = 'add_sub_category';
      $this->load->view('admin/template', $data);
   }

   public function edit_sub_category()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $id = $this->uri->segment(3);
      $data['page'] = 'edit_sub_category';
      $data['subcategory'] = $this->admin_model->get_sub_category_by_id($id);
      $this->load->view('admin/template', $data);
   }

   public function update_sub_category()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $cat_id = $_REQUEST['id'];

      $this->form_validation->set_rules('c_name', 'Sub Category Name', 'required');
      $this->form_validation->set_rules('cat_id', 'Category Name', 'required');

      $this->form_validation->set_error_delimiters('<span class="error" style="color:red;">', '</span>');
      if ($this->form_validation->run() == false) {
         //Error
      } else {
         $data = array(
            'cat_id' => $_REQUEST['cat_id'],
            'c_name' => $_REQUEST['c_name'],
            'type' => $_REQUEST['type'],

         );

         if (!empty($_FILES['img']['name'])) {
            $config['upload_path'] = './uploads';
            $config['allowed_types'] = 'jpg|png|jpeg';
            $config['file_name'] = uniqid();
            $config['overwrite'] = TRUE;

            // Load and initialize upload library
            $this->load->library('upload');
            $this->upload->initialize($config);

            // Upload file to server
            if ($this->upload->do_upload('img')) {
               // Uploaded file data
               $fileData = $this->upload->data();
               $img = $fileData['file_name'];
            } else {
               $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
            }

            $data['img'] = $img;
         }

         if (!empty($_FILES['icon']['name'])) {
            $config['upload_path'] = './uploads';
            $config['allowed_types'] = 'jpg|png|jpeg';
            $config['file_name'] = uniqid();
            $config['overwrite'] = TRUE;

            // Load and initialize upload library
            $this->load->library('upload');
            $this->upload->initialize($config);

            // Upload file to server
            if ($this->upload->do_upload('icon')) {
               // Uploaded file data
               $fileData = $this->upload->data();
               $icon = $fileData['file_name'];
            } else {
               $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
            }

            $data['icon'] = $icon;
         }

         $check = $this->admin_model->update_sub_category_by_id($cat_id, $data);
         if ($check) {
            $this->session->set_flashdata('update_success', 'Category has been successfully Updated.');
            redirect('admin/sub-category-list', $data);
         }
      }

      $data['page'] = 'edit_sub_category';
      $data['subcategory'] = $this->admin_model->get_sub_category_by_id($cat_id);
      $this->load->view('admin/template', $data);
   }

   public function trash_sub_category()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      if (!empty($_REQUEST['id'])) {
         $id = $_REQUEST['id'];

         $this->db->where('id', $id);
         $this->db->delete("sub_categories");
         $this->session->set_flashdata('del_success', 'Category has been Successfully Deleted.');
         redirect('admin/sub-category-list');
      }
   }

   public function list_likes()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'list_likes';
      $this->load->view('admin/template', $data);
   }

   public function likeview()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'likeview';
      $this->load->view('admin/template', $data);
   }

   public function list_reviews()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'list_reviews';
      $this->load->view('admin/template', $data);
   }

   public function reviews_view()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'reviews_view';
      $this->load->view('admin/template', $data);
   }

   public function trash_reviews()
   {

      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      if (!empty($_REQUEST['id'])) {
         $id = $_REQUEST['id'];
         $review_id = $_REQUEST['review_id'];

         $this->db->where('rev_id', $id);
         $this->db->delete("reviews");
         $this->session->set_flashdata('success', 'Category has been Successfully Deleted.');

         redirect('admin/reviews-view/' . $review_id);
      }
   }

   public function list_restaurants()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'list_restaurants';
      $this->load->view('admin/template', $data);
   }

   public function create_restaurants()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'add_restaurants';
      $this->load->view('admin/template', $data);
   }

   public function add_restaurants()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $this->form_validation->set_rules('cat_id', 'Category Name', 'required');
      $this->form_validation->set_rules('res_name', 'Store Name', 'required');
      $this->form_validation->set_rules('res_desc', 'Description', 'required');
      $this->form_validation->set_rules('res_phone', 'Phone Number', 'required');

      $this->form_validation->set_error_delimiters('<span class="error" style="color:red;">', '</span>');
      if ($this->form_validation->run() == false) {
         //Error
      } else {
         $res_image = array();
         $res_video = "";
         $logo = "";

         if ($_FILES['res_image']['name']) {
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
                     $this->session->set_flashdata('error', $error['error']);
                  }
               }
            }
         }

         if ($_FILES['logo']['name']) {
            // File upload configuration
            $config['upload_path'] = './uploads';
            $config['allowed_types'] = 'gif|jpg|png|jpeg';
            $config['file_name'] = uniqid();
            $config['overwrite'] = TRUE;


            // Load and initialize upload library
            $this->load->library('upload');
            $this->upload->initialize($config);

            // Upload file to server
            if ($this->upload->do_upload('logo')) {
               // Uploaded file data
               $fileData = $this->upload->data();
               $logo = $fileData['file_name'];
            } else {
               $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
               $this->session->set_flashdata('error', $error['error']);
            }
         }

         // if ($_FILES['res_video']['name'] != "") {
         // 	// File upload configuration
         // 	$config['upload_path'] = './uploads';
         // 	$config['allowed_types'] = 'mp4|mkv';
         // 	$config['file_name'] = uniqid();
         // 	$config['overwrite'] = TRUE;


         // 	// Load and initialize upload library
         // 	$this->load->library('upload');
         // 	$this->upload->initialize($config);

         // 	// Upload file to server
         // 	if ($this->upload->do_upload('res_video')) {
         // 		// Uploaded file data
         // 		$fileData = $this->upload->data();
         // 		$res_video = $fileData['file_name'];
         // 	} else {
         // 		$error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
         // 		$this->session->set_flashdata('error', $error['error']);
         // 	}
         // }

         // $data = array(
         // 	'res_name' => $_REQUEST['res_name'],
         // 	'cat_id' => $_REQUEST['cat_id'],
         // );

         $data['vid'] = $this->input->post('vid');
         $data['cat_id'] = $this->input->post('cat_id');
         $data['res_name'] = $this->input->post('res_name');
         $data['res_desc'] = $this->input->post('res_desc');

         if ($this->input->post('res_website')) {
            $data['res_website'] = $this->input->post('res_website');
         }
         if ($this->input->post('res_phone')) {
            $data['res_phone'] = $this->input->post('res_phone');
         }

         $data['res_image'] = implode('::::', $res_image);
         $data['logo'] = $logo;

         if ($this->input->post('monday_from')) {
            $data['monday_from'] = $this->input->post('monday_from');
         }
         if ($this->input->post('monday_to')) {
            $data['monday_to'] = $this->input->post('monday_to');
         }
         if ($this->input->post('tuesday_from')) {
            $data['tuesday_from'] = $this->input->post('tuesday_from');
         }
         if ($this->input->post('tuesday_to')) {
            $data['tuesday_to'] = $this->input->post('tuesday_to');
         }
         if ($this->input->post('wednesday_from')) {
            $data['wednesday_from'] = $this->input->post('wednesday_from');
         }
         if ($this->input->post('wednesday_to')) {
            $data['wednesday_to'] = $this->input->post('wednesday_to');
         }
         if ($this->input->post('thursday_from')) {
            $data['thursday_from'] = $this->input->post('thursday_from');
         }
         if ($this->input->post('thursday_to')) {
            $data['thursday_to'] = $this->input->post('thursday_to');
         }
         if ($this->input->post('friday_from')) {
            $data['friday_from'] = $this->input->post('friday_from');
         }
         if ($this->input->post('friday_to')) {
            $data['friday_to'] = $this->input->post('friday_to');
         }
         if ($this->input->post('saturday_from')) {
            $data['saturday_from'] = $this->input->post('saturday_from');
         }
         if ($this->input->post('saturday_to')) {
            $data['saturday_to'] = $this->input->post('saturday_to');
         }
         if ($this->input->post('sunday_from')) {
            $data['sunday_from'] = $this->input->post('sunday_from');
         }
         if ($this->input->post('sunday_to')) {
            $data['sunday_to'] = $this->input->post('sunday_to');
         }
         
         $address = $_REQUEST['store_address'];
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

         $data['res_address'] = $this->input->post('store_address');
         $data['lat'] = $lat;
         $data['lon'] = $long;

         $data['res_create_date'] = time();

         $check = $this->admin_model->add_restaurants($data);
         if ($check) {
            $this->session->set_flashdata('add_success', 'Restaurants has been added Successfully.');
            redirect('admin/restaurants-list');
         }
      }
      $data['page'] = 'add_restaurants';
      $this->load->view('admin/template', $data);
   }

   public function edit_restaurants()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $id = $this->uri->segment(3);
      $data['page'] = 'edit_restaurants';
      $data['restaurant'] = $this->admin_model->get_restaurants_by_id($id);
      $this->load->view('admin/template', $data);
   }

   public function update_restaurants()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $id = $_REQUEST['id'];

      $this->form_validation->set_rules('cat_id', 'Category Name', 'required');
      $this->form_validation->set_rules('res_name', 'Store Name', 'required');
      $this->form_validation->set_rules('res_desc', 'Description', 'required');
      $this->form_validation->set_rules('res_phone', 'Phone Number', 'required');

      $this->form_validation->set_error_delimiters('<span class="error" style="color:red;">', '</span>');
      if ($this->form_validation->run() == false) {
         //Error
      } else {

         $restaurant = $this->admin_model->get_restaurants_by_id($id);
         $data = array();

         $data['res_image'] = $restaurant->res_image;
         if ($_FILES['res_image']['name'][0] != "") {
            $res_image = array();
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
                  $config['upload_path'] = './uploads/';
                  $config['allowed_types'] = 'gif|jpg|png|jpeg';
                  $config['file_name'] = uniqid();
                  $config['overwrite'] = TRUE;


                  // Load and initialize upload library
                  $this->load->library('upload');
                  $this->upload->initialize($config);

                  // Upload file to server
                  if ($this->upload->do_upload('file')) {
                     // Uploaded file data

                     $image_name = $this->admin_model->get_restaurants_by_id($id);
                     $image = explode('::::', $image_name->res_image);
                     foreach ($image as $key => $images) {
                        if (!empty($images)) {
                           unlink('./uploads/' . $images);
                        }
                     }

                     $fileData = $this->upload->data();
                     array_push($res_image, $fileData['file_name']);
                     // $res_image = $fileData['file_name'];

                  } else {
                     $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
                     $this->session->set_flashdata('error', $error['error']);
                  }
               }

               $data['res_image'] = implode("::::", $res_image);
            }
         }

         //print_r($res_image);
         $data['logo'] = $restaurant->logo;
         if ($_FILES['logo']['name'] != "") {
            // File upload configuration
            $config['upload_path'] = './uploads/';
            $config['allowed_types'] = 'gif|jpg|png|jpeg';
            $config['file_name'] = uniqid();
            $config['overwrite'] = TRUE;


            // Load and initialize upload library
            $this->load->library('upload');
            $this->upload->initialize($config);

            // Upload file to server
            if ($this->upload->do_upload('logo')) {
               // Uploaded file data
               $fileData = $this->upload->data();
               $data['logo'] = $fileData['file_name'];
            } else {
               $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
               $this->session->set_flashdata('error', $error['error']);
            }
         }

         // $res_video = $restaurant->res_video;
         // if ($_FILES['res_video']['name'] != "") {
         // 	// File upload configuration
         // 	$config['upload_path'] = './uploads';
         // 	$config['allowed_types'] = 'mp4|mkv';
         // 	$config['file_name'] = uniqid();
         // 	$config['overwrite'] = TRUE;


         // 	// Load and initialize upload library
         // 	$this->load->library('upload');
         // 	$this->upload->initialize($config);

         // 	// Upload file to server
         // 	if ($this->upload->do_upload('res_video')) {
         // 		// Uploaded file data
         // 		$fileData = $this->upload->data();
         // 		$res_video = $fileData['file_name'];
         // 	} else {
         // 		$error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
         // 		$this->session->set_flashdata('error', $error['error']);
         // 	}
         // }

         // $data = array(
         // 	'c_name' => $_REQUEST['c_name'],

         // );

         $data['cat_id'] = $this->input->post('cat_id');
         $data['res_name'] = $this->input->post('res_name');
         $data['res_desc'] = $this->input->post('res_desc');

         if ($this->input->post('res_website')) {
            $data['res_website'] = $this->input->post('res_website');
         }
         if ($this->input->post('res_phone')) {
            $data['res_phone'] = $this->input->post('res_phone');
         }

         if ($this->input->post('monday_from')) {
            $data['monday_from'] = $this->input->post('monday_from');
         }
         if ($this->input->post('monday_to')) {
            $data['monday_to'] = $this->input->post('monday_to');
         }
         if ($this->input->post('tuesday_from')) {
            $data['tuesday_from'] = $this->input->post('tuesday_from');
         }
         if ($this->input->post('tuesday_to')) {
            $data['tuesday_to'] = $this->input->post('tuesday_to');
         }
         if ($this->input->post('wednesday_from')) {
            $data['wednesday_from'] = $this->input->post('wednesday_from');
         }
         if ($this->input->post('wednesday_to')) {
            $data['wednesday_to'] = $this->input->post('wednesday_to');
         }
         if ($this->input->post('thursday_from')) {
            $data['thursday_from'] = $this->input->post('thursday_from');
         }
         if ($this->input->post('thursday_to')) {
            $data['thursday_to'] = $this->input->post('thursday_to');
         }
         if ($this->input->post('friday_from')) {
            $data['friday_from'] = $this->input->post('friday_from');
         }
         if ($this->input->post('friday_to')) {
            $data['friday_to'] = $this->input->post('friday_to');
         }
         if ($this->input->post('saturday_from')) {
            $data['saturday_from'] = $this->input->post('saturday_from');
         }
         if ($this->input->post('saturday_to')) {
            $data['saturday_to'] = $this->input->post('saturday_to');
         }
         if ($this->input->post('sunday_from')) {
            $data['sunday_from'] = $this->input->post('sunday_from');
         }
         if ($this->input->post('sunday_to')) {
            $data['sunday_to'] = $this->input->post('sunday_to');
         }

         $address = $_REQUEST['store_address'];
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

         $data['res_address'] = $this->input->post('store_address');
         $data['lat'] = $lat;
         $data['lon'] = $long;

         $check = $this->admin_model->update_restaurants_by_id($id, $data);
         if ($check) {
            $this->session->set_flashdata('success', 'Restaurants has been successfully Updated.');
            redirect('admin/restaurants-list', $data);
         }
      }

      $data['page'] = 'edit_restaurants';
      $data['restaurant'] = $this->admin_model->get_restaurants_by_id($id);
      $this->load->view('admin/template', $data);
   }

   public function trash_restaurants()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      if (!empty($_REQUEST['id'])) {
         $id = $_REQUEST['id'];

         $image_name = $this->admin_model->get_restaurants_by_id($id);
         $image = explode('::::', $image_name->res_image);
         foreach ($image as $key => $images) {
            if (!empty($images)) {
               unlink('./uploads/' . $images);
            }
         }

         $this->db->where('res_id', $id);
         $this->db->delete("restaurants");
         $this->session->set_flashdata('success', 'Restaurant has been Successfully Deleted.');
         redirect('admin/restaurants-list');
      }
   }

   public function list_banners()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'list_banners';
      $this->load->view('admin/template', $data);
   }

   public function create_banners()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'add_banners';
      $this->load->view('admin/template', $data);
   }

   public function add_banners()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $this->form_validation->set_rules('banners_name', 'Banners Name', 'required');

      $this->form_validation->set_error_delimiters('<span class="error" style="color:red;">', '</span>');
      if ($this->form_validation->run() == false) {
         //Error
      } else {
         $data = array(
            'banners_name' => $_REQUEST['banners_name'],
         );

         if (!empty($_FILES['image']['name'])) {
            $config['upload_path'] = './uploads';
            $config['allowed_types'] = 'jpg|png|jpeg';
            $config['file_name'] = uniqid();
            $config['overwrite'] = TRUE;

            // Load and initialize upload library
            $this->load->library('upload');
            $this->upload->initialize($config);

            // Upload file to server
            if ($this->upload->do_upload('image')) {
               // Uploaded file data
               $fileData = $this->upload->data();
               $image = $fileData['file_name'];
            } else {
               $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
            }

            $data['image'] = $image;
         }

         $check = $this->admin_model->add_banners($data);
         if ($check) {
            $this->session->set_flashdata('success', 'Banners has been added Successfully.');
            redirect('admin/banners-list');
         }
      }
      $data['page'] = 'add_banners';
      $this->load->view('admin/template', $data);
   }

   public function edit_banners()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $id = $this->uri->segment(3);
      $data['page'] = 'edit_banners';
      $data['banners'] = $this->admin_model->get_banners_by_id($id);
      $this->load->view('admin/template', $data);
   }

   public function update_banners()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $id = $_REQUEST['id'];

      $this->form_validation->set_rules('banners_name', 'Banners Name', 'required');

      $this->form_validation->set_error_delimiters('<span class="error" style="color:red;">', '</span>');
      if ($this->form_validation->run() == false) {
         //Error
      } else {
         $data = array(
            'banners_name' => $_REQUEST['banners_name'],

         );

         if (!empty($_FILES['image']['name'])) {
            $config['upload_path'] = './uploads';
            $config['allowed_types'] = 'jpg|png|jpeg';
            $config['file_name'] = uniqid();
            $config['overwrite'] = TRUE;

            // Load and initialize upload library
            $this->load->library('upload');
            $this->upload->initialize($config);

            // Upload file to server
            if ($this->upload->do_upload('image')) {
               // Uploaded file data
               $fileData = $this->upload->data();
               $image = $fileData['file_name'];
            } else {
               $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
            }

            $data['image'] = $image;
         }

         $check = $this->admin_model->update_banners_by_id($id, $data);
         if ($check) {
            $this->session->set_flashdata('success', 'Banners has been successfully Updated.');
            redirect('admin/banners-list', $data);
         }
      }

      $data['page'] = 'edit_banners';
      $data['banners'] = $this->admin_model->get_banners_by_id($id);
      $this->load->view('admin/template', $data);
   }

   public function trash_banners()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      if (!empty($_REQUEST['id'])) {
         $id = $_REQUEST['id'];

         $this->db->where('id', $id);
         $this->db->delete("banners");
         $this->session->set_flashdata('success', 'Banners has been Successfully Deleted.');
         redirect('admin/banners-list');
      }
   }

   public function list_type()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'list_type';
      $this->load->view('admin/template', $data);
   }

   public function create_type()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'add_type';
      $this->load->view('admin/template', $data);
   }

   public function add_type()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $this->form_validation->set_rules('c_name', 'Type Name', 'required');

      $this->form_validation->set_error_delimiters('<span class="error" style="color:red;">', '</span>');
      if ($this->form_validation->run() == false) {
         //Error
      } else {
         $data = array(
            'c_name' => $_REQUEST['c_name'],
         );

         $check = $this->admin_model->add_type($data);
         if ($check) {
            $this->session->set_flashdata('success', 'Type has been added Successfully.');
            redirect('admin/type-list');
         }
      }
      $data['page'] = 'add_type';
      $this->load->view('admin/template', $data);
   }

   public function edit_type()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $id = $this->uri->segment(3);
      $data['page'] = 'edit_type';
      $data['type'] = $this->admin_model->get_type_by_id($id);
      $this->load->view('admin/template', $data);
   }

   public function update_type()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $id = $_REQUEST['id'];

      $this->form_validation->set_rules('c_name', 'Type Name', 'required');

      $this->form_validation->set_error_delimiters('<span class="error" style="color:red;">', '</span>');
      if ($this->form_validation->run() == false) {
         //Error
      } else {
         $data = array(
            'c_name' => $_REQUEST['c_name'],

         );

         $check = $this->admin_model->update_type_by_id($id, $data);
         if ($check) {
            $this->session->set_flashdata('success', 'Type has been successfully Updated.');
            redirect('admin/type-list', $data);
         }
      }

      $data['page'] = 'edit_type';
      $data['type'] = $this->admin_model->get_type_by_id($id);
      $this->load->view('admin/template', $data);
   }

   public function trash_type()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      if (!empty($_REQUEST['id'])) {
         $id = $_REQUEST['id'];

         $this->db->where('id', $id);
         $this->db->delete("type");
         $this->session->set_flashdata('success', 'Type has been Successfully Deleted.');
         redirect('admin/type-list');
      }
   }

   public function list_booking()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'list_booking';
      $this->load->view('admin/template', $data);
   }

   public function view_booking()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'view_booking';
      $this->load->view('admin/template', $data);
   }

   public function trash_booking()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      if (!empty($_REQUEST['id'])) {
         $id = $_REQUEST['id'];

         $this->db->where('id', $id);
         $this->db->delete("booking");
         $this->session->set_flashdata('success', 'Type has been Successfully Deleted.');
         redirect('admin/booking-list');
      }
   }

   public function list_product_category()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'list_product_category';
      $this->load->view('admin/template', $data);
   }

   public function create_product_category()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'add_product_category';
      $this->load->view('admin/template', $data);
   }

   public function add_product_category()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $this->form_validation->set_rules('c_name', 'Category Name', 'required');

      $this->form_validation->set_error_delimiters('<span class="error" style="color:red;">', '</span>');
      if ($this->form_validation->run() == false) {
         //Error
      } else {
         $data = array(
            'c_name' => $_REQUEST['c_name'],
         );

         if (!empty($_FILES['image']['name'])) {
            $config['upload_path'] = './uploads';
            $config['allowed_types'] = 'jpg|png|jpeg';
            $config['file_name'] = uniqid();
            $config['overwrite'] = TRUE;

            // Load and initialize upload library
            $this->load->library('upload');
            $this->upload->initialize($config);

            // Upload file to server
            if ($this->upload->do_upload('image')) {
               // Uploaded file data
               $fileData = $this->upload->data();
               $image = $fileData['file_name'];
            } else {
               $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
            }

            $data['image'] = $image;
         }

         $check = $this->admin_model->add_product_category($data);
         if ($check) {
            $this->session->set_flashdata('add_success', 'Category has been added Successfully.');
            redirect('admin/product-category-list');
         }
      }
      $data['page'] = 'add_product_category';
      $this->load->view('admin/template', $data);
   }

   public function edit_product_category()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $id = $this->uri->segment(3);
      $data['page'] = 'edit_product_category';
      $data['product_category'] = $this->admin_model->get_product_category_by_id($id);
      $this->load->view('admin/template', $data);
   }

   public function update_product_category()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $id = $_REQUEST['id'];

      $this->form_validation->set_rules('c_name', 'Category Name', 'required');

      $this->form_validation->set_error_delimiters('<span class="error" style="color:red;">', '</span>');
      if ($this->form_validation->run() == false) {
         //Error
      } else {
         $data = array(
            'c_name' => $_REQUEST['c_name'],
         );

         if (!empty($_FILES['image']['name'])) {
            $config['upload_path'] = './uploads';
            $config['allowed_types'] = 'jpg|png|jpeg';
            $config['file_name'] = uniqid();
            $config['overwrite'] = TRUE;

            // Load and initialize upload library
            $this->load->library('upload');
            $this->upload->initialize($config);

            // Upload file to server
            if ($this->upload->do_upload('image')) {
               // Uploaded file data
               $fileData = $this->upload->data();
               $image = $fileData['file_name'];
            } else {
               $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
            }

            $data['image'] = $image;
         }

         $check = $this->admin_model->update_product_category_by_id($id, $data);
         if ($check) {
            $this->session->set_flashdata('update_success', 'Category has been successfully Updated.');
            redirect('admin/product-category-list', $data);
         }
      }

      $data['page'] = 'edit_product_category';
      $data['product_category'] = $this->admin_model->get_product_category_by_id($id);
      $this->load->view('admin/template', $data);
   }

   public function trash_product_category()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      if (!empty($_REQUEST['id'])) {
         $id = $_REQUEST['id'];

         $this->db->where('cat_id', $id);
         $this->db->delete("products");

         $this->db->where('id', $id);
         $this->db->delete("product_category");
         $this->session->set_flashdata('del_success', 'Category has been Successfully Deleted.');
         redirect('admin/product-category-list');
      }
   }

   public function list_product()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'list_product';
      $this->load->view('admin/template', $data);
   }

   public function create_product()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'add_product';
      $this->load->view('admin/template', $data);
   }

   public function add_product()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $this->form_validation->set_rules('cat_id', 'Category', 'required');
      $this->form_validation->set_rules('product_name', 'Product Name', 'required');
      $this->form_validation->set_rules('product_description', 'Description', 'required');
      $this->form_validation->set_rules('product_price', 'Price', 'required');

      $this->form_validation->set_error_delimiters('<span class="error" style="color:red;">', '</span>');
      if ($this->form_validation->run() == false) {
         //Error
      } else {
         $data = array(
            'vid' => $_REQUEST['vid'],
            'cat_id' => $_REQUEST['cat_id'],
            'product_name' => $_REQUEST['product_name'],
            'product_description' => $_REQUEST['product_description'],
            'product_price' => $_REQUEST['product_price'],
         );

         $product_image = array();

         if ($_FILES['product_image']['name']) {
            //echo "product_image detected";
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
                     //$res_product_image = $fileData['file_name'];

                  } else {
                     $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
                     $this->session->set_flashdata('error', $error['error']);
                  }
               }
            }
         }
         // $data['logo'] = $logo;
         $data['product_image'] = implode('::::', $product_image);

         $check = $this->admin_model->add_product($data);
         if ($check) {
            $this->session->set_flashdata('success', 'Product has been added Successfully.');
            redirect('admin/product-list');
         }
      }
      $data['page'] = 'add_product';
      $this->load->view('admin/template', $data);
   }

   public function edit_product()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $id = $this->uri->segment(3);
      $data['page'] = 'edit_product';
      $data['product'] = $this->admin_model->get_products_by_id($id);
      $this->load->view('admin/template', $data);
   }

   public function update_product()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $id = $_REQUEST['id'];

      $this->form_validation->set_rules('cat_id', 'Category', 'required');
      $this->form_validation->set_rules('product_name', 'Product Name', 'required');
      $this->form_validation->set_rules('product_description', 'Description', 'required');
      $this->form_validation->set_rules('product_price', 'Price', 'required');

      $this->form_validation->set_error_delimiters('<span class="error" style="color:red;">', '</span>');
      if ($this->form_validation->run() == false) {
         //Error
      } else {
         $data = array(
            'cat_id' => $_REQUEST['cat_id'],
            'product_name' => $_REQUEST['product_name'],
            'product_description' => $_REQUEST['product_description'],
            'product_price' => $_REQUEST['product_price'],
         );

         $product = $this->admin_model->get_products_by_id($id);

         $data['product_image'] = $product->product_image;

         if ($_FILES['product_image']['name'][0] != "") {
            $product_image = array();
            //echo "product_image detected";
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
                     // $product_image = $fileData['file_name'];

                  } else {
                     $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
                     $this->session->set_flashdata('error', $error['error']);
                  }
               }

               $data['product_image'] = implode("::::", $product_image);
            }
         }

         $check = $this->admin_model->update_product_by_id($id, $data);
         if ($check) {
            $this->session->set_flashdata('update_success', 'Category has been successfully Updated.');
            redirect('admin/product-list', $data);
         }
      }

      $data['page'] = 'edit_product';
      $data['product'] = $this->admin_model->get_products_by_id($id);
      $this->load->view('admin/template', $data);
   }

   public function trash_product()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      if (!empty($_REQUEST['id'])) {
         $id = $_REQUEST['id'];

         $this->db->where('product_id', $id);
         $this->db->delete("products");

         $this->db->where('product_id', $id);
         $this->db->delete("cart_items");

         $this->session->set_flashdata('success', 'Product has been Successfully Deleted.');
         redirect('admin/product-list');
      }
   }

   public function testimonial_category_list()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'list_testimonial_category';
      $this->load->view('admin/template', $data);
   }

   public function create_testimonial_category()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'add_testimonial_category';
      $this->load->view('admin/template', $data);
   }

   public function add_testimonial_category()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $this->form_validation->set_rules('c_name', 'Type Name', 'required');

      $this->form_validation->set_error_delimiters('<span class="error" style="color:red;">', '</span>');
      if ($this->form_validation->run() == false) {
         //Error
      } else {
         $data = array(
            'c_name' => $_REQUEST['c_name'],
         );

         $check = $this->admin_model->add_testimonial_category($data);
         if ($check) {
            $this->session->set_flashdata('success', 'Added Successfully.');
            redirect('admin/testimonial-category-list');
         }
      }
      $data['page'] = 'add_testimonial_category';
      $this->load->view('admin/template', $data);
   }

   public function edit_testimonial_category()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $id = $this->uri->segment(3);
      $data['page'] = 'edit_testimonial_category';
      $data['testimonial_category'] = $this->admin_model->get_testimonial_category_by_id($id);
      $this->load->view('admin/template', $data);
   }

   public function update_testimonial_category()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $id = $_REQUEST['id'];

      $this->form_validation->set_rules('c_name', 'Type Name', 'required');

      $this->form_validation->set_error_delimiters('<span class="error" style="color:red;">', '</span>');
      if ($this->form_validation->run() == false) {
         //Error
      } else {
         $data = array(
            'c_name' => $_REQUEST['c_name'],

         );

         $check = $this->admin_model->update_testimonial_category_by_id($id, $data);
         if ($check) {
            $this->session->set_flashdata('success', 'Type has been successfully Updated.');
            redirect('admin/testimonial-category-list', $data);
         }
      }

      $data['page'] = 'add_testimonial_category';
      $data['testimonial_category'] = $this->admin_model->get_testimonial_category_by_id($id);
      $this->load->view('admin/template', $data);
   }

   public function trash_testimonial_category()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      if (!empty($_REQUEST['id'])) {
         $id = $_REQUEST['id'];

         $this->db->where('id', $id);
         $this->db->delete("testimonial_category");
         $this->session->set_flashdata('success', 'Successfully Deleted.');
         redirect('admin/testimonial-category-list');
      }
   }

   public function testimonial_list()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'list_testimonial';
      $this->load->view('admin/template', $data);
   }

   public function create_testimonial()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'add_testimonial';
      $this->load->view('admin/template', $data);
   }

   public function add_testimonial()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $this->form_validation->set_rules('cat_id', 'Type Name', 'required');
      $this->form_validation->set_rules('name', 'Name', 'required');
      $this->form_validation->set_rules('group_name', 'Group Name', 'required');
      $this->form_validation->set_rules('review_text', 'Review Text', 'required');

      $this->form_validation->set_error_delimiters('<span class="error" style="color:red;">', '</span>');
      if ($this->form_validation->run() == false) {
         //Error
      } else {
         $data = array(
            'cat_id' => $_REQUEST['cat_id'],
            'name' => $_REQUEST['name'],
            'group_name' => $_REQUEST['group_name'],
            'review_text' => $_REQUEST['review_text'],
         );
         $data['created_date'] = time();

         $check = $this->admin_model->add_testimonial($data);
         if ($check) {
            $this->session->set_flashdata('success', 'Added Successfully.');
            redirect('admin/testimonial-list');
         }
      }
      $data['page'] = 'add_testimonial';
      $this->load->view('admin/template', $data);
   }

   public function edit_testimonial()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $id = $this->uri->segment(3);
      $data['page'] = 'edit_testimonial';
      $data['testimonial'] = $this->admin_model->get_testimonial_by_id($id);
      $this->load->view('admin/template', $data);
   }

   public function update_testimonial()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $id = $_REQUEST['id'];

      $this->form_validation->set_rules('cat_id', 'Type Name', 'required');
      $this->form_validation->set_rules('name', 'Name', 'required');
      $this->form_validation->set_rules('group_name', 'Group Name', 'required');
      $this->form_validation->set_rules('review_text', 'Review Text', 'required');

      $this->form_validation->set_error_delimiters('<span class="error" style="color:red;">', '</span>');
      if ($this->form_validation->run() == false) {
         //Error
      } else {
         $data = array(
            'cat_id' => $_REQUEST['cat_id'],
            'name' => $_REQUEST['name'],
            'group_name' => $_REQUEST['group_name'],
            'review_text' => $_REQUEST['review_text'],
         );

         $check = $this->admin_model->update_testimonial_by_id($id, $data);
         if ($check) {
            $this->session->set_flashdata('success', 'Type has been successfully Updated.');
            redirect('admin/testimonial-list', $data);
         }
      }

      $data['page'] = 'add_testimonial';
      $data['testimonial'] = $this->admin_model->get_testimonial_by_id($id);
      $this->load->view('admin/template', $data);
   }

   public function trash_testimonial()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      if (!empty($_REQUEST['id'])) {
         $id = $_REQUEST['id'];

         $this->db->where('id', $id);
         $this->db->delete("testimonial");
         $this->session->set_flashdata('success', 'Successfully Deleted.');
         redirect('admin/testimonial-list');
      }
   }

   public function list_orders()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'list_orders';
      $this->load->view('admin/template', $data);
   }

   public function view_order()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'view_order';
      $this->load->view('admin/template', $data);
   }

   public function change_status()
   {
      $id = $this->input->post('order_id');

      $data = array();

      $data['order_status'] = $this->input->post('status');

      $this->db->where('id', $id);

      $this->db->update('orders', $data);

      $status = $this->input->post('status');

      if ($status == 0) {

         $title = "Processing";
         $message = "Your Order Processing";
      } elseif ($status == 1) {

         $title = "Order Dispatch";
         $message = "Your Order Dispatch";
      } elseif ($status == 2) {

         $title = "Order Deliver";
         $message = "Your Order Successfully Deliver";
      } else {

         $title = "Order Cancel";
         $message = "Your Order Cancel";
      }


      $order = $this->db->get_where('orders', array('id' => $id), 1)->row();

      $response = $this->firebase_model->send_user_notification($order->user_id, $title, $message, "Message");

      $this->firebase_model->save_user_notification($order->user_id, $title, $message, "order", $order->id);

      $this->session->set_flashdata('success', 'successfully Changed..');
      redirect(base_url() . 'admin/orders', 'refresh');
   }

   public function trash_orders()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      if (!empty($_REQUEST['id'])) {
         $id = $_REQUEST['id'];

         $this->db->where('id', $id);
         $this->db->delete("orders");
         $this->session->set_flashdata('success', 'Orders has been Successfully Deleted.');
         redirect('admin/orders');
      }
   }

   public function payment_setting()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['general_setting'] = $this->db->get_where('general_setting', array('id' => "1"), 1)->row();
      $data['page'] = 'payment_setting';
      $this->load->view('admin/template', $data);
   }

   public function notification_setting()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['general_setting'] = $this->db->get_where('general_setting', array('id' => "1"), 1)->row();
      $data['page'] = 'notification_setting';
      $this->load->view('admin/template', $data);
   }

   public function update_general_setting()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data = array();

      $n_server_key = $this->input->post("n_server_key");
      if (!empty($n_server_key)) {
         $data['n_server_key'] = $this->input->post("n_server_key");
      }

      $s_secret_key = $this->input->post("s_secret_key");
      if (!empty($s_secret_key)) {
         $data['s_secret_key'] = $this->input->post("s_secret_key");
      }

      $s_public_key = $this->input->post("s_public_key");
      if (!empty($s_public_key)) {
         $data['s_public_key'] = $this->input->post("s_public_key");
      }

      $r_secret_key = $this->input->post("r_secret_key");
      if (!empty($r_secret_key)) {
         $data['r_secret_key'] = $this->input->post("r_secret_key");
      }

      $r_public_key = $this->input->post("r_public_key");
      if (!empty($r_public_key)) {
         $data['r_public_key'] = $this->input->post("r_public_key");
      }

      $this->admin_model->updateSettings($data);

      $url = $this->input->post("url");

      $this->session->set_flashdata('success', 'successfully Changed..');
      redirect(base_url() . 'admin/' . $url);
   }

   public function change_booking_status()
   {

      $id = $this->input->post('id');

      $data = array();

      $data['status'] = $this->input->post('status');
      $this->db->where('id', $id);
      $this->db->update('booking', $data);

      $status = $this->input->post('status');

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

      $this->session->set_flashdata('success', 'successfully Changed..');
      redirect(base_url() . 'admin/booking-list', 'refresh');
   }

   public function total_sales_get()
   {

      if ($this->input->post("year") != "") {
         $year = $this->input->post("year");
      } else {
         $year = date("Y");;
      }

      $sql = "SELECT * FROM chart_data WHERE year = '" . $year . "' ORDER BY id ASC";
      $query = $this->db->query($sql);
      $result = $query->result_array();

      $data = array();
      foreach ($result as $row) {
         $output[] = array(
            'month'   => $row["month"],
            'profit'  => floatval($row["profit"])
         );
      }

      echo json_encode($output);
   }

   public function list_service()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'list_service';
      $this->load->view('admin/template', $data);
   }

   public function create_service()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'add_service';
      $this->load->view('admin/template', $data);
   }

   public function add_service()
   {

      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $this->form_validation->set_rules('res_id', 'Store Name', 'required');
      $this->form_validation->set_rules('service_name', 'Service Name', 'required');
      $this->form_validation->set_rules('cat_id', 'Category Name', 'required');
      $this->form_validation->set_rules('service_price', 'Service Price', 'required');
      $this->form_validation->set_rules('service_description', 'Service Description', 'required');
      $this->form_validation->set_rules('price_unit', 'Price Unit', 'required');
      $this->form_validation->set_rules('duration', 'Duration', 'required');

      $this->form_validation->set_error_delimiters('<span class="error" style="color:red;">', '</span>');
      if ($this->form_validation->run() == false) {
         //Error
      } else {
         $data = array(
            'res_id' => $_REQUEST['res_id'],
            'v_id' => $_REQUEST['v_id'],
            'service_name' => $_REQUEST['service_name'],
            'cat_id' => $_REQUEST['cat_id'],
            'service_price' => $_REQUEST['service_price'],
            'service_description' => $_REQUEST['service_description'],
            'price_unit' => $_REQUEST['price_unit'],
            'duration' => $_REQUEST['duration'],
         );

         $service_image = array();

         if ($_FILES['service_image']['name']) {

            if (is_array($_FILES['service_image']['name'])) {
               $filesCount = count($_FILES['service_image']['name']);
               for ($i = 0; $i < $filesCount; $i++) {
                  $_FILES['file']['name']     = $_FILES['service_image']['name'][$i];
                  $_FILES['file']['type']     = $_FILES['service_image']['type'][$i];
                  $_FILES['file']['tmp_name'] = $_FILES['service_image']['tmp_name'][$i];
                  $_FILES['file']['error']     = $_FILES['service_image']['error'][$i];
                  $_FILES['file']['size']     = $_FILES['service_image']['size'][$i];

                  // File upload configuration
                  $config['upload_path'] = './uploads/service_images';
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
                  } else {
                     $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
                     $this->session->set_flashdata('error', $error['error']);
                  }
               }
            }
         }
         $data['service_image'] = implode('::::', $service_image);

         $res_id = $_REQUEST['res_id'];

         $check = $this->admin_model->add_service($data);
         if ($check) {
            $this->session->set_flashdata('success', 'Added Successfully.');
            redirect('admin/service-list/' . $res_id);
         }
      }
      $data['page'] = 'add_service';
      $this->load->view('admin/template', $data);
   }

   public function edit_service()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $id = $this->uri->segment(4);
      $data['page'] = 'edit_service';
      $data['service'] = $this->admin_model->get_service_by_id($id);
      $this->load->view('admin/template', $data);
   }

   public function update_service()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $id = $_REQUEST['id'];
      $service_id = $_REQUEST['res_id'];

      $this->form_validation->set_rules('service_name', 'Service Name', 'required');
      $this->form_validation->set_rules('cat_id', 'Category Name', 'required');
      $this->form_validation->set_rules('service_price', 'Service Price', 'required');
      $this->form_validation->set_rules('service_description', 'Service Description', 'required');
      $this->form_validation->set_rules('price_unit', 'Price Unit', 'required');
      $this->form_validation->set_rules('duration', 'Duration', 'required');

      $this->form_validation->set_error_delimiters('<span class="error" style="color:red;">', '</span>');
      if ($this->form_validation->run() == false) {
         //Error
      } else {
         $data = array(
            'res_id' => $_REQUEST['res_id'],
            'service_name' => $_REQUEST['service_name'],
            'cat_id' => $_REQUEST['cat_id'],
            'service_price' => $_REQUEST['service_price'],
            'service_description' => $_REQUEST['service_description'],
            'price_unit' => $_REQUEST['price_unit'],
            'duration' => $_REQUEST['duration'],
         );

         $service = $this->admin_model->get_service_by_id($id);

         $data['service_image'] = $service->service_image;

         if ($_FILES['service_image']['name'][0] != "") {
            $service_image = array();
            //echo "service_image detected";
            if (is_array($_FILES['service_image']['name'])) {
               $filesCount = count($_FILES['service_image']['name']);
               for ($i = 0; $i < $filesCount; $i++) {
                  $_FILES['file']['name']     = $_FILES['service_image']['name'][$i];
                  $_FILES['file']['type']     = $_FILES['service_image']['type'][$i];
                  $_FILES['file']['tmp_name'] = $_FILES['service_image']['tmp_name'][$i];
                  $_FILES['file']['error']     = $_FILES['service_image']['error'][$i];
                  $_FILES['file']['size']     = $_FILES['service_image']['size'][$i];

                  // File upload configuration
                  $config['upload_path'] = './uploads/service_images';
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
                     // $service_image = $fileData['file_name'];

                  } else {
                     $error = array('error' => $this->upload->display_errors('<div class="alert alert-danger">', '</div>'));
                     $this->session->set_flashdata('error', $error['error']);
                  }
               }

               $data['service_image'] = implode("::::", $service_image);
            }
         }

         $check = $this->admin_model->update_service_by_id($id, $data);
         if ($check) {
            $this->session->set_flashdata('success', 'Successfully Updated.');
            redirect('admin/service-list/' . $service_id, $data);
         }
      }

      $data['page'] = 'edit_service';
      $data['service'] = $this->admin_model->get_service_by_id($id);
      $this->load->view('admin/template', $data);
   }

   public function trash_service()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      if (!empty($_REQUEST['id'])) {
         $id = $_REQUEST['id'];
         $res_id = $_REQUEST['res_id'];

         $this->db->where('id', $id);
         $this->db->delete("services");

         $this->session->set_flashdata('success', 'Successfully Deleted.');
         redirect('admin/service-list/' . $res_id);
      }
   }

   public function list_all_service()
   {
      if ($this->session->userdata('aid') == "") {
         redirect(base_url('admin/login'));
      }

      $data['page'] = 'list_all_service';
      $this->load->view('admin/template', $data);
   }

}
