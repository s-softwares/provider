<?php
  $type = $this->session->userdata['user_type'];
  $aid = $this->session->userdata['aid'];
?>
<!-- Content Wrapper. Contains page content -->

<script type="text/javascript" src="<?php echo base_url() ?>scripts/libraries/Chart.min.js"></script>

<script src="<?php echo base_url() ?>jquery.min.js"></script>

<script src="<?php echo base_url() ?>Chart.min.js"></script>


<div class="content-wrapper">

   <!-- Content Header (Page header) -->

   <div class="content-header">

      <div class="container-fluid">

         <div class="row mb-2">

            <div class="col-sm-6">

               <h1 class="m-0">Dashboard</h1>

            </div><!-- /.col -->

         </div><!-- /.row -->

      </div>

   </div>

   <section class="content">

      <div class="container-fluid">

         <!-- Info boxes -->

         <div class="row">

            <div class="col-12 col-sm-6 col-md-3">

               <div class="info-box mb-3">

                  <span class="info-box-icon bg-success elevation-1"><i class="ion ion-bag"></i></span>

                  <div class="info-box-content">

                     <span class="info-box-text">Total Store</span>

                     <span class="info-box-number"><?php $store = $this->db->get_where('restaurants', array('vid' => $aid))->num_rows(); echo $store ?></span>

                  </div>

               </div>

            </div>

            <div class="col-12 col-sm-6 col-md-3">

               <div class="info-box">

                  <span class="info-box-icon bg-info elevation-1"><i class="fa fa-scribd"></i></span>

                  <div class="info-box-content">

                     <span class="info-box-text">Total Service</span>

                     <span class="info-box-number">

                        <?php $service = $this->db->get_where('services', array('v_id' => $aid))->num_rows(); echo $service ?>

                     </span>

                  </div>

               </div>

            </div>
            <!-- /.col -->

            <div class="clearfix hidden-md-up"></div>

            <div class="col-12 col-sm-6 col-md-3">

               <div class="info-box mb-3">

                  <span class="info-box-icon bg-blue elevation-1"><i class="fa fa-product-hunt"></i></span>

                  <div class="info-box-content">

                     <span class="info-box-text">Total Products</span>

                     <span class="info-box-number"><?php $products = $this->db->get_where('products', array('vid' => $aid))->num_rows(); echo $products ?></span>

                  </div>

               </div>

            </div>

            <div class="col-12 col-sm-6 col-md-3">

               <div class="info-box mb-3">

                  <span class="info-box-icon bg-danger elevation-1"><i class="fa fa-bold"></i></span>

                  <div class="info-box-content">

                     <span class="info-box-text">Total Booking</span>

                     <span class="info-box-number"><?php $booking = $this->db->get_where('booking', array('vid' => $aid))->num_rows(); echo $booking ?></span>

                  </div>

               </div>

            </div>

         </div>

      </div>

   </section>

   <?php

      $this->db->select('booking.*');
      $this->db->from('booking');
      $this->db->where('vid', $aid);
      $this->db->order_by("id", "desc");
      $this->db->limit(5);
      $query = $this->db->get();
      $new_booking = $query->result();

      $query = $this->db->query("SELECT A.product_id, B.id,B.order_id,B.product_id,B.qty,B.price,B.total, C.id,C.user_id,C.order_id,C.date,C.payment_mode,C.address,C.txn_id,C.p_status,C.order_status,C.p_date FROM products A, order_items B, orders C WHERE A.product_id = B.product_id AND A.vid = '$aid' AND B.order_id = C.id GROUP BY B.order_id ORDER BY B.id DESC LIMIT 5");

      $order = $query->result();
   ?>

   <section class="content">

      <div class="container-fluid">

         <div class="row">

            <div class="col-lg-6 col-md-12 col-sm-12 col-12">

               <div class="card card-primary">

                  <div class="card-header">

                     <h3 class="card-title">Latest 5 Service Booking</h3>

                  </div>

                  <!-- /.card-header -->

                  <div class="card-body">

                     <table id="reviews_table" class="table table-bordered table-striped">

                        <thead>

                           <tr>

                              <th>Sr no</th>

                              <th>User Name</th>
                              <th>Amount</th>
                              <th>Payment Mode</th>
                              <th>Date</th>
                              <th>Status</th>
                              <th>Action</th>

                           </tr>

                        </thead>


                        <tbody>

                           <?php if (isset($new_booking)) {

                              $cnt = 1; ?>

                              <?php foreach ($new_booking as $listing) { ?>

                                 <tr>

                                    <td><?php echo $cnt++; ?></td>

                                    <td>
                                    	<?php $user_id = $listing->user_id; 
                                    	$user = $this->db->get_where('user', array('id' => $user_id))->row();
                                    	if (!empty($user)) {
                                    		echo $user->username;
                                    	}
                                    	?>
                                    </td>

                                    <td><?php echo $listing->amount; ?></td>
                                    <td><?php echo $listing->payment_mode; ?></td>
                                    <td><?php echo $listing->date; ?></td>

                                    <td><?php if ($listing->status == "Confirm") {
														?>
														<span class="badge badge-info">Confirm</span>
													<?php
														} elseif ($listing->status == "On Way") {
													?>
														<span class="badge badge-warning">On Way</span>
													<?php
														} elseif ($listing->status == "Completed") {
													?>
														<span class="badge badge-success">Completed</span>
													<?php
														} else { ?>
														<span class="badge badge-danger">Cancel</span>
													<?php } ?>
												</td>

												<td>
													<a class="btn btn-sm btn-warning" href="<?php echo base_url('admin/view-booking/' . $listing->id); ?>"><i class="fa fa-eye"></i></a>
												</td>

                                 </tr>

                              <?php } ?>

                           <?php } ?>

                        </tbody>

                     </table>

                  </div>

               </div>

            </div>



            <div class="col-lg-6 col-md-12 col-sm-12 col-12">

               <div class="card card-primary">

                  <div class="card-header">

                     <h3 class="card-title">Latest 5 Products Orders</h3>

                  </div>

                  <!-- /.card-header -->

                  <div class="card-body">

                     <table id="user_table" class="table table-bordered table-striped">

                        <thead>

                           <tr>
                              <th>User Name</th>
                              <th>Total</th>
                              <th>Payment Mode</th>
                              <th>Status</th>
                              <th>Action</th>

                           </tr>

                        </thead>



                        <tbody>

                           <?php if (isset($order)) {

                              $cnt = 1; ?>

                              <?php foreach ($order as $listing) { ?>

                                 <tr>

                                    <td>
                                       <?php $user_id = $listing->user_id; 
                                       $user = $this->db->get_where('user', array('id' => $user_id))->row();
                                       if (!empty($user)) {
                                          echo $user->username;
                                       }
                                       ?>
                                    </td>

                                    <td><?php echo $listing->total; ?></td>
                                    <td><?php echo $listing->payment_mode; ?></td>

                                    <td><?php if ($listing->order_status == 0) {
                                          ?>
                                          <span class="badge badge-info">Processing</span>
                                       <?php
                                          } elseif ($listing->order_status == 1) {
                                       ?>
                                          <span class="badge badge-warning">Dispatch</span>
                                       <?php
                                          } elseif ($listing->order_status == 2) {
                                       ?>
                                          <span class="badge badge-success">Deliver</span>
                                       <?php
                                          } else { ?>
                                          <span class="badge badge-danger">Cancel</span>
                                          <?php } ?>
                                    </td>

                                    <td>
                                       <a class="btn btn-sm btn-warning" href="<?php echo base_url('admin/view-order/' . $listing->id) ?>"><i class="fa fa-eye"></i></a>
                                    </td>

                                 </tr>

                              <?php } ?>

                           <?php } ?>

                        </tbody>

                     </table>

                  </div>

                  <!-- /.card-body -->

               </div>

            </div>



         </div>

      </div>

   </section>

<?php

   $query = $this->db->query("SELECT A.*, B.res_id,B.vid,B.res_name FROM reviews A, restaurants B WHERE A.rev_res = B.res_id AND B.vid = '$aid'  ORDER BY A.rev_id DESC LIMIT 5");
   $store_reviews = $query->result();

   $query = $this->db->query("SELECT A.*, B.id,B.v_id,B.service_name FROM services_reviews A, services B WHERE A.rev_service = B.id AND B.v_id = '$aid'  ORDER BY A.rev_id DESC LIMIT 5");
   $service_review = $query->result();

?>

   <section class="content">

      <div class="container-fluid">

         <div class="row">

            <div class="col-lg-6 col-md-12 col-sm-12 col-12">

               <div class="card card-primary">

                  <div class="card-header">

                     <h3 class="card-title">Latest 5 Store Reviews</h3>

                  </div>

                  <!-- /.card-header -->

                  <div class="card-body">

                     <table id="reviews_table" class="table table-bordered table-striped">

                        <thead>

                           <tr>

                              <th>Sr no</th>

                              <th>User Name</th>

                              <th>Store Name</th>

                              <!-- <th>Star</th> -->

                              <th>Text</th>

                              <th>Date</th>

                              <!-- <th>Action</th> -->

                           </tr>

                        </thead>



                        <tbody>



                           <?php if (isset($store_reviews)) {

                              $cnt = 1; ?>

                              <?php foreach ($store_reviews as $listing) { ?>

                                 <tr>

                                    <td><?php echo $cnt++; ?></td>

                                    <td><?php

                                          $lid = $listing->rev_user;



                                          $query = $this->db->select('*')

                                             ->from('user')

                                             ->where('id', $lid)

                                             ->get();

                                          $fetch = $query->row();



                                          if (!empty($fetch->username)) {

                                             echo $fetch->username;
                                          } else {

                                             echo "";
                                          }

                                          ?></td>

                                    <td><?php

                                          $lida = $listing->rev_res;



                                          $querya = $this->db->select('*')

                                             ->from('restaurants')

                                             ->where('res_id', $lida)

                                             ->get();

                                          $fetcha = $querya->row();

                                          if (!empty($fetcha->res_name)) {

                                             echo $fetcha->res_name;
                                          } else {

                                             echo "";
                                          }

                                          ?></td>

                                    <td><?php echo $listing->rev_text; ?></td>

                                    <td><?php echo gmdate('d M Y', $listing->rev_date); ?></td>

                                 </tr>

                              <?php } ?>

                           <?php } ?>

                        </tbody>

                     </table>

                  </div>

               </div>

            </div>



            <div class="col-lg-6 col-md-12 col-sm-12 col-12">

               <div class="card card-primary">

                  <div class="card-header">

                     <h3 class="card-title">Latest 5 Service Reviews</h3>

                  </div>

                  <!-- /.card-header -->

                  <div class="card-body">

                     <table id="reviews_table" class="table table-bordered table-striped">

                        <thead>

                           <tr>

                              <th>Sr no</th>

                              <th>User Name</th>

                              <th>Store Name</th>

                              <!-- <th>Star</th> -->

                              <th>Text</th>

                              <th>Date</th>

                              <!-- <th>Action</th> -->

                           </tr>

                        </thead>



                        <tbody>



                           <?php if (isset($service_review)) {

                              $cnt = 1; ?>

                              <?php foreach ($service_review as $listing) { ?>

                                 <tr>

                                    <td><?php echo $cnt++; ?></td>

                                    <td><?php

                                          $lid = $listing->rev_user;

                                          $query = $this->db->select('*')

                                             ->from('user')

                                             ->where('id', $lid)

                                             ->get();

                                          $fetch = $query->row();



                                          if (!empty($fetch->username)) {

                                             echo $fetch->username;
                                          } else {

                                             echo "";
                                          }

                                          ?></td>

                                    <td><?php

                                          $lida = $listing->rev_service;

                                          $querya = $this->db->select('*')

                                             ->from('services')

                                             ->where('id', $lida)

                                             ->get();

                                          $fetcha = $querya->row();

                                          if (!empty($fetcha->service_name)) {

                                             echo $fetcha->service_name;
                                          } else {

                                             echo "";
                                          }

                                          ?></td>

                                    <td><?php echo $listing->rev_text; ?></td>

                                    <td><?php echo gmdate('d M Y', $listing->rev_date); ?></td>

                                 </tr>

                              <?php } ?>

                           <?php } ?>

                        </tbody>

                     </table>

                  </div>

                  <!-- /.card-body -->

               </div>

            </div>



         </div>



      </div>

   </section>



</div>