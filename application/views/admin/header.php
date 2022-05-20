<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <link rel="icon" href="<?php echo base_url(); ?>uploads/ez_logo.png" type="image/png" sizes="16x16">
   <title>Swaft | App</title>

   <!-- Google Font: Source Sans Pro -->
   <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
   <!-- Font Awesome -->
   <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/assets/plugins/fontawesome-free/css/all.min.css">
   <link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

   <!-- Ionicons -->
   <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
   <!-- Tempusdominus Bootstrap 4 -->

   <link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
   <!-- iCheck -->
   <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
   <!-- JQVMap -->
   <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/assets/plugins/jqvmap/jqvmap.min.css">
   <!-- Theme style -->
   <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/assets/dist/css/adminlte.min.css">
   <!-- overlayScrollbars -->
   <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
   <!-- Daterange picker -->
   <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/assets/plugins/daterangepicker/daterangepicker.css">
   <!-- summernote -->
   <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/assets/plugins/summernote/summernote-bs4.min.css">

   <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
   <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
   <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">

</head>
<?php

$id = $this->session->userdata('aid');

$profile = $this->admin_model->get_admin($id);

$uri = $this->uri->segment(2);

?>

<body class="hold-transition sidebar-mini layout-fixed">
   <div class="wrapper">

      <!-- Navbar -->
      <nav class="main-header navbar navbar-expand navbar-white navbar-light">
         <!-- Left navbar links -->
         <ul class="navbar-nav">
            <li class="nav-item">
               <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
         </ul>

         <div class="dropdown">
            <button onclick="myFunction()" class="dropbtn">
               <img src="<?php echo base_url(); ?>uploads/profile_pics/<?php echo $profile['profile_image'] ?>" alt="Image">
               <?php echo $profile['uname'] ?>
            </button>
            <div id="myDropdown" class="dropdown-content">
               <a href="<?php echo base_url('admin/profile'); ?>">Profile</a>
               <a href="<?php echo base_url('admin/logout'); ?>">Logout</a>
            </div>
         </div>

         <!-- Right navbar links -->
         <ul class="navbar-nav">

            <li class="nav-item">
               <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                  <i class="fas fa-expand-arrows-alt"></i>
               </a>
            </li>
         </ul>
      </nav>
      <!-- /.navbar -->


      <script>
         /* When the user clicks on the button, 
toggle between hiding and showing the dropdown content */
         function myFunction() {
            document.getElementById("myDropdown").classList.toggle("show");
         }

         // Close the dropdown if the user clicks outside of it
         window.onclick = function(event) {
            if (!event.target.matches('.dropbtn')) {
               var dropdowns = document.getElementsByClassName("dropdown-content");
               var i;
               for (i = 0; i < dropdowns.length; i++) {
                  var openDropdown = dropdowns[i];
                  if (openDropdown.classList.contains('show')) {
                     openDropdown.classList.remove('show');
                  }
               }
            }
         }
      </script>