<?php
$id = $this->uri->segment(3);

$service = $this->admin_model->get_all_service($id);
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
   <!-- Content Header (Page header) -->
   <section class="content-header">
      <div class="container-fluid">
         <div class="row mb-2">
            <div class="col-sm-6">
               <h1>Service</h1>
            </div>
            <div class="col-sm-6">
               <ol class="breadcrumb float-sm-right">

                  <a class="btn btn-sm btn-success margin-5" style="margin-right: 34px;" href="<?php echo base_url('admin/create-service'); ?>"><i class="fa fa-plus-square"></i> Add Service</a>

                  <li class="breadcrumb-item"><a href="<?php echo base_url('admin/dashboard'); ?>">Home</a></li>
                  <li class="breadcrumb-item"><a href="<?php echo base_url('admin/restaurants-list'); ?>">Store-List</a></li>
                  <li class="breadcrumb-item active">Service</li>
               </ol>
            </div>
         </div>
      </div><!-- /.container-fluid -->
   </section>

   <section class="content">
      <div class="container-fluid">
         <div class="row">
            <div class="col-12">

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

               <div class="card card-primary">
                  <div class="card-header">
                     <h3 class="card-title">Service List</h3>
                  </div>
                  <!-- /.card-header -->
                  <div class="card-body">
                     <table id="list_table" class="table table-bordered table-striped">
                        <thead>
                           <tr>
                              <th>Id</th>
                              <th>Service Image</th>
                              <th>Service Name</th>
                              <th>Category</th>
                              <th>Service Description</th>
                              <th>Service Price</th>
                              <th>Price Unit</th>
                              <th>Duration</th>
                              <!-- <th>Ratings</th> -->
                              <th>Actions</th>
                           </tr>
                        </thead>

                        <tbody>

                           <?php if (isset($service)) {
                              $cnt = 1; ?>
                              <?php foreach ($service as $row) { ?>
                                 <tr>
                                    <td><?php echo $cnt++; ?></td>

                                    <?php if ($row->service_image != " ") { ?>
                                       <?php $image = explode('::::', $row->service_image); ?>
                                       <td><img src="<?php echo base_url(); ?>uploads/service_images/<?php echo $image[0]; ?>" height="60" width="60"></td>
                                    <?php } else { ?>
                                       <td><?php echo "None"; ?></td>
                                    <?php } ?>

                                    <td><?php echo $row->service_name; ?></td>

                                    <?php
                                    $category = $this->db->get_where('categories', array('id' => $row->cat_id), 1)->row(); ?>
                                    <?php if (empty($category)) { ?>
                                       <td></td>
                                    <?php } else { ?>
                                       <td><?php echo $category->c_name; ?></td> <?php } ?>

                                    <?php
                                    $str = $row->service_description;
                                    $string = strip_tags($str);
                                    if (strlen($string) > 25) {

                                       $stringCut = substr($string, 0, 25);
                                       $endPoint = strrpos($stringCut, ' ');

                                       $string = $endPoint ? substr($stringCut, 0, $endPoint) : substr($stringCut, 0);
                                       $string .= '...';
                                    }
                                    ?>
                                    <td><?php echo $string; ?></td>

                                    <td><?php echo $row->service_price; ?></td>
                                    <td><?php echo $row->price_unit; ?></td>
                                    <td><?php echo $row->duration; ?></td>
                                    <!-- <td><?php // echo $row->service_ratings; 
                                             ?></td> -->

                                    <td style="display: inline-flex;">
                                       <a class="btn btn-sm btn-info margin-5" href="<?php echo base_url('admin/edit-service/' . $id . '/' . $row->id); ?>"><i class="fa fa-edit"></i></a>
                                       <button data-i="<?php echo $row->id; ?>" class="btn btn-sm btn-danger delete margin-5">
                                          <i class="fa fa-trash"></i></button>
                                    </td>
                                 </tr>
                              <?php } ?>
                           <?php } ?>
                        </tbody>
                     </table>
                  </div>
                  <!-- /.card-body -->
               </div>
               <!-- /.card -->
            </div>
            <!-- /.col -->
         </div>
         <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
   </section>

</div>
<div class="modal fade in" id="modalDel">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <h4 class="modal-title">Delete Confirmation</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">×</span></button>
         </div>
         <form method="post" action="<?php echo base_url('admin/trash-service'); ?>" id="frmDel">
            <div class="modal-body">
               <p>Are you sure you want to delete?</p>
            </div>
            <div class="modal-footer">
               <input type="hidden" name="res_id" value="<?php echo $id; ?>">
               <input type="hidden" name="id" value="">
               <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
               <input type="submit" class="btn btn-primary btnclass" value="Yes Delete!">
            </div>
         </form>
      </div>
   </div>
</div>
<script type="text/javascript" src="<?php echo base_url() ?>assets/plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script type="text/javascript" src="<?php echo base_url() ?>assets/plugins/jquery-ui/jquery-ui.min.js"></script>
<script type="text/javascript">
   $(document).ready(function() {
      $(document).on('click', '.delete', function() {
         var i = $(this).data('i');
         $("#frmDel input[name='id']").val(i);
         $("#modalDel").modal('show');
      });
   });
</script>