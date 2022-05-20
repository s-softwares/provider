<?php
$booking = $this->admin_model->get_booking();
$url = $this->uri->segment(2);
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
   <!-- Content Header (Page header) -->
   <section class="content-header">
      <div class="container-fluid">
         <div class="row mb-2">
            <div class="col-sm-6">
               <h1><i class="nav-icon fa fa-cog"> Notification Setting</i></h1>
            </div>
            <div class="col-sm-6">
               <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="<?php echo base_url('admin/dashboard'); ?>">Home</a></li>
                  <li class="breadcrumb-item active">Notification Setting</li>
               </ol>
            </div>
         </div>
      </div><!-- /.container-fluid -->
   </section>

   <section class="content">
      <div class="col-md-10">
         <?php if (!empty($this->session->flashdata('success'))) : ?>
            <div class="alert alert-success">
               <a href="javascript:void()" class="close" data-dismiss="alert" aria-label="close">&times;</a>
               <span> <?php echo $this->session->flashdata('success'); ?> </span>
            </div>
         <?php endif ?>
         <?php if ($this->session->flashdata('error')) : ?>
            <div class="alert alert-danger">
               <a href="javascript:void()" class="close" data-dismiss="alert" aria-label="close">&times;</a>
               <span><?php echo $this->session->flashdata('error') ?></span>
            </div>
         <?php endif ?>
         <!-- Notification form elements -->
         <div class="card card-primary">
            <div class="card-header">
               <h3 class="card-title">Notification Setting</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form method="POST" enctype="multipart/form-data" action="<?php echo base_url('admin/update-general-setting'); ?>">
               <div class="card-body">

               	<input type="hidden" name="url" value="<?php echo $url; ?>">

                  <div class="form-group">
                     <label for="exampleInputfnm">Notification: Server Key</label>
                     <input type="text" class="form-control" id="exampleInputfnm" placeholder="Notification Server Key" name="n_server_key" value="<?php echo $general_setting->n_server_key; ?>" disabled>
                  </div>

                  <div class="card-footer">
                     <button type="submit" class="btn btn-primary" disabled>Submit</button>
                  </div>
            </form>
         </div>


      </div>
   </section>

</div>