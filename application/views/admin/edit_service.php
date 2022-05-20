<?php
$res_id = $this->uri->segment(3);
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
   <!-- Content Header (Page header) -->
   <section class="content-header">
      <div class="container-fluid">
         <div class="row mb-2">
            <div class="col-sm-6">
               <h1>Edit Service</h1>
            </div>
            <div class="col-sm-6">
               <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="<?php echo base_url('admin/dashboard'); ?>">Home</a></li>
                  <li class="breadcrumb-item"><a href="<?php echo base_url('admin/service-list/' . $res_id); ?>">Service-List</a></li>
                  <li class="breadcrumb-item active">Edit Service</li>
               </ol>
            </div>
         </div>
      </div><!-- /.container-fluid -->
   </section>
   <!-- <?php if (!empty($this->session->flashdata('success'))) : ?>
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
        <?php endif ?> -->
   <!-- Main content -->

   <section class="content">
      <div class="container-fluid">
         <div class="row">
            <!-- left column -->
            <div class="col-lg-12">
               <!-- general form elements -->
               <div class="card card-primary">
                  <div class="card-header">
                     <h3 class="card-title">Edit Service</h3>
                  </div>
                  <!-- /.card-header -->
                  <!-- form start -->
                  <form method="POST" enctype="multipart/form-data" action="<?php echo base_url('admin/update-service'); ?>">
                     <div class="col-md-12">
                        <div class="box-body">

                           <input type="hidden" name="res_id" value="<?php echo $res_id; ?>">
                           <input type="hidden" name="id" value="<?php echo $service->id; ?>">

                           <div class="row">
                              <div class="col-sm-6">
                                 <div class="form-group">
                                    <label for="exampleInputfnm">Service Name</label>
                                    <input type="text" class="form-control" id="exampleInputfnm" placeholder="Enter Service Name" name="service_name" autocomplete="off" value="<?php echo $service->service_name; ?>">
                                    <?php echo form_error('service_name'); ?>
                                 </div>
                              </div>

                              <div class="col-sm-6">
                                 <div class="form-group">
                                    <label>Category</label>
                                    <select class="form-control" name="cat_id">
                                       <option value="">Select Category</option>
                                       <?php $category = $this->db->get('categories')->result(); ?>
                                       <?php foreach ($category as $listing) : ?>
                                          <option value="<?php echo $listing->id; ?>" <?php if ($listing->id == $service->cat_id) echo "selected='selected'"; ?>><?php echo $listing->c_name; ?></option>
                                       <?php endforeach; ?>
                                    </select>
                                    <?php echo form_error('cat_id'); ?>
                                 </div>
                              </div>
                           </div>

                           <div class="row">
                              <div class="col-sm-6">
                                 <div class="form-group">
                                    <label for="exampleInputfnm">Service Price</label>
                                    <input type="number" class="form-control" id="exampleInputfnm" placeholder="Enter Service Price" name="service_price" autocomplete="off" value="<?php echo $service->service_price; ?>">
                                    <?php echo form_error('service_price'); ?>
                                 </div>
                              </div>

                              <div class="col-sm-6">
                                 <div class="form-group">
                                    <label>Service Description</label>
                                    <textarea class="form-control" name="service_description" rows="3" placeholder="Enter Service Description"><?php echo $service->service_description; ?></textarea>
                                    <?php echo form_error('service_description'); ?>
                                 </div>
                              </div>
                           </div>

                           <div class="row">
                              <div class="col-sm-6">

                                 <label>Price Unit</label>
                                 <select class="form-control" name="price_unit">
                                    <option value="">Select Price Unit</option>
                                    <option value="Fixed" <?php if ("Fixed" == $service->price_unit) echo "selected='selected'"; ?>>Fixed</option>
                                    <option value="Hourly" <?php if ("Hourly" == $service->price_unit) echo "selected='selected'"; ?>>Hourly</option>
                                 </select>
                                 <?php echo form_error('price_unit'); ?>

                              </div>

                              <div class="col-sm-6">
                                 <div class="form-group">
                                    <label>Service Duration</label>
                                    <input type="number" class="form-control" name="duration" rows="3" placeholder="Enter Service Duration" autocomplete="off" value="<?php echo $service->duration; ?>">
                                    <?php echo form_error('duration'); ?>
                                 </div>
                              </div>
                           </div>

                           <div class="form-group">
                              <label for="exampleInputFile">Service Images</label>
                              <div class="input-group">
                                 <div class="custom-file">
                                    <input type="file" name="service_image[]" class="custom-file-input" id="exampleInputFile" multiple>
                                    <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                 </div>
                              </div>
                              <p class="help-block"></p>
                              <?php $images = explode("::::", $service->service_image); ?>
                              <?php foreach ($images as $key => $image) { ?>
                                 <img src="<?php echo base_url('uploads/service_images/') . $image ?>" class="service_image" height="70" width="70">
                              <?php } ?>
                           </div>

                        </div>
                        <div class="card-footer">
                           <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                        </div>
                     </div>
                  </form>
               </div>
            </div>
         </div>
         <!-- /.row -->
      </div><!-- /.container-fluid -->
   </section>
   <!-- /.content -->
</div>