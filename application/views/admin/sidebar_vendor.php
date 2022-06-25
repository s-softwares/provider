<?php

$id = $this->session->userdata('aid');

$profile = $this->admin_model->get_admin($id);

$uri = $this->uri->segment(2);

?>

<!-- Main Sidebar Container -->

<aside class="main-sidebar sidebar-dark-primary elevation-4">

   <!-- Brand Logo -->

   <a href="<?php echo base_url('admin/dashboard'); ?>" class="brand-link">

      <img src="<?php echo base_url(); ?>uploads/Transparent_white.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">

      <span class="brand-text font-weight-light">Eshield</span>

   </a>

   <!-- Sidebar -->

   <div class="sidebar">

      <nav class="mt-2">

         <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

            <li class="nav-item menu-open">

               <a href="<?php echo base_url('admin/dashboard'); ?>" class="nav-link active">

                  <i class="nav-icon fas fa-tachometer-alt"></i>

                  <p> Dashboard </p>

               </a>

            </li>

            <li class="nav-item">

               <a href="#" class="nav-link">

                  <i class="nav-icon fa fa-scribd"></i>

                  <p>

                     Store

                     <i class="fas fa-angle-left right"></i>

                  </p>

               </a>

               <ul class="nav nav-treeview">

                  <li class="nav-item">

                     <a href="<?php echo base_url('admin/create-restaurants'); ?>" class="nav-link">

                        <i class="fa fa-plus nav-icon"></i>

                        <p>Add Store</p>

                     </a>

                  </li>

                  <li class="nav-item">

                     <a href="<?php echo base_url('admin/restaurants-list'); ?>" class="nav-link">

                        <i class="fa fa-list-ul nav-icon"></i>

                        <p>Store List</p>

                     </a>

                  </li>

               </ul>

            </li>


            <li class="nav-item">

               <a href="#" class="nav-link">

                  <i class="nav-icon fa fa-scribd"></i>

                  <p>

                     Service

                     <i class="fas fa-angle-left right"></i>

                  </p>

               </a>

               <ul class="nav nav-treeview">

                  <li class="nav-item">

                     <a class="nav-link" href="<?php echo base_url('admin/create-service'); ?>">

                        <i class="nav-icon fa fa-plus"></i>
                        <p>Add Service</p>

                     </a>

                  </li>

                  <li class="nav-item">

                     <a href="<?php echo base_url('admin/all-service'); ?>" class="nav-link">

                        <i class="fa fa-list-ul nav-icon"></i>

                        <p>Service List</p>

                     </a>

                  </li>

               </ul>

            </li>



            <li class="nav-item">

               <a class="nav-link" href="<?php echo base_url('admin/likes-list'); ?>">

                  <i class="nav-icon fa fa-heart"></i>

                  <p>

                     List Of Service Likes

                  </p>

               </a>

            </li>



            <li class="nav-item">

               <a class="nav-link" href="<?php echo base_url('admin/reviews-list'); ?>">

                  <i class="nav-icon fa fa-star"></i>

                  <p>

                     List Of Service Reviews

                  </p>

               </a>

            </li>


            <li class="nav-item">

               <a href="#" class="nav-link">

                  <i class="nav-icon fa fa-bold"></i>

                  <p>

                     Booking Orders

                     <i class="fas fa-angle-left right"></i>

                  </p>

               </a>

               <ul class="nav nav-treeview">

                  <li class="nav-item">

                     <a class="nav-link" href="<?php echo base_url('admin/booking-list'); ?>">

                        <i class="nav-icon fa fa-bold"></i>

                        <p>

                           Booking History

                        </p>

                     </a>

                  </li>

               </ul>

            </li>


            <li class="nav-item">

               <a href="#" class="nav-link">

                  <i class="nav-icon fa fa-cart-plus"></i>

                  <p>

                     Product Orders

                     <i class="fas fa-angle-left right"></i>

                  </p>

               </a>

               <ul class="nav nav-treeview">

                  <li class="nav-item">

                     <a href="<?php echo base_url('admin/orders'); ?>" class="nav-link">

                        <i class="fa fa-angle-double-right nav-icon"></i>

                        <p>Orders List</p>

                     </a>

                  </li>

               </ul>

            </li>



            <li class="nav-item">

               <a href="#" class="nav-link">

                  <i class="nav-icon fa fa-product-hunt"></i>

                  <p>

                     Product

                     <i class="fas fa-angle-left right"></i>

                  </p>

               </a>

               <ul class="nav nav-treeview">

                  <li class="nav-item">

                     <a href="<?php echo base_url('admin/create-product'); ?>" class="nav-link">

                        <i class="fa fa-plus nav-icon"></i>

                        <p>Add Product</p>

                     </a>

                  </li>

                  <li class="nav-item">

                     <a href="<?php echo base_url('admin/product-list'); ?>" class="nav-link">

                        <i class="fa fa-list-ul nav-icon"></i>

                        <p>Product List</p>

                     </a>

                  </li>

               </ul>

            </li>

         </ul>

      </nav>

      <!-- /.sidebar-menu -->

   </div>

   <!-- /.sidebar -->

</aside>