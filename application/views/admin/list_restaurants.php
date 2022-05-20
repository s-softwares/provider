<?php
   $type = $this->session->userdata['user_type'];
   $aid = $this->session->userdata['aid'];

   if ($type == 'admin') {
      $restaurants = $this->admin_model->get_restaurants();
   }else{
      $restaurants = $this->admin_model->get_vendor_store($aid);
   }
   
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
   <!-- Content Header (Page header) -->
   <section class="content-header">
      <div class="container-fluid">
         <div class="row mb-2">
            <div class="col-sm-6">
               <h1><i class="nav-icon fa fa-scribd"></i> Store </h1>
            </div>
            <div class="col-sm-6">
               <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="<?php echo base_url('admin/dashboard'); ?>">Home</a></li>
                  <li class="breadcrumb-item active">Store </li>
               </ol>
            </div>
         </div>
      </div><!-- /.container-fluid -->
   </section>

   <section class="content">
      <div class="container-fluid">
         <div class="row">
            <div class="col-12">

               <div class="card card-primary">
                  <div class="card-header">
                     <h3 class="card-title">Store List</h3>
                  </div>
                  <!-- /.card-header -->
                  <div class="card-body">
                     <table id="list_table" class="table table-bordered table-striped">
                        <thead>
                           <tr>
                              <th>Sr no</th>
                              <th>Store Image</th>
                              <th>Vendor</th>
                              <th>Store Name</th>
                              <th>Category</th>
                              <th>Description</th>
                              <th>Date</th>
                              <th>Action</th>
                           </tr>
                        </thead>

                        <tbody>

                           <?php if (isset($restaurants)) {
                              $cnt = 1; ?>
                              <?php foreach ($restaurants as $listing) { ?>
                                 <tr>
                                    <td><?php echo $cnt++; ?></td>

                                    <?php if ($listing['res_image'] != " ") { ?>
                                       <?php $image = explode('::::', $listing['res_image']); ?>
                                       <td><img src="<?php echo base_url(); ?>uploads/<?php echo $image[0]; ?>" height="60" width="60"></td>
                                    <?php } else { ?>
                                       <td><?php echo "None"; ?></td>
                                    <?php } ?>

                                    <td>
                                       <?php 
                                          $vid = $listing['vid'];
                                          $vendor = $this->db->get_where('vendor', array('id' => $vid))->row();

                                          if (!empty($vendor)) {
                                             echo $vendor->uname;
                                          }
                                       ?>
                                    </td>

                                    <?php
                                    $str = $listing['res_name'];

                                    $res_name = strip_tags($str);
                                    if (strlen($res_name) > 25) {

                                       $stringCut = substr($res_name, 0, 25);
                                       $endPoint = strrpos($stringCut, ' ');

                                       $res_name = $endPoint ? substr($stringCut, 0, $endPoint) : substr($stringCut, 0);
                                       $res_name .= '...';
                                    }
                                    ?>
                                    <td><?php echo $res_name; ?></td>

                                    <?php
                                    $cat_name = $this->db->get_where('categories', array('id' => $listing['cat_id']))->row();
                                    ?>
                                    <td>
                                       <?php
                                       if (!empty($cat_name->c_name)) {
                                          echo $cat_name->c_name;
                                       } else {
                                       }
                                       ?>
                                    </td>

                                    <!-- <td><?php echo $listing['res_name']; ?></td> -->

                                    <?php
                                    $str = $listing['res_desc'];

                                    $string = strip_tags($str);
                                    if (strlen($string) > 15) {

                                       $stringCut = substr($string, 0, 15);
                                       $endPoint = strrpos($stringCut, ' ');

                                       $string = $endPoint ? substr($stringCut, 0, $endPoint) : substr($stringCut, 0);
                                       $string .= '...';
                                    }
                                    ?>
                                    <td><?php echo $string; ?></td>

                                    <td><?php echo gmdate('d M Y', $listing['res_create_date']); ?></td>

                                    <td style="display: inline-flex;">

                                       <a class="btn btn-warning btn-sm" href="<?php echo base_url('admin/service-list/' . $listing['res_id']); ?>" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="View Service"><i class="fa fa-list"></i></a>

                                       <a class="btn btn-sm btn-info" href="<?php echo base_url('admin/edit-restaurants/' . $listing['res_id']); ?>"><i class="fa fa-edit"></i></a>
                                       <button data-i="<?php echo $listing['res_id']; ?>" class="btn btn-sm btn-danger delete"><i class="fa fa-trash"></i></button>
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
         <form method="post" action="<?php echo base_url('admin/trash-restaurants'); ?>" id="frmDel">
            <div class="modal-body">
               <p>Are you sure you want to delete?</p>
            </div>
            <div class="modal-footer">
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