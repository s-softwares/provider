<?php
  $type = $this->session->userdata['user_type'];
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

               <div class="info-box">

                  <span class="info-box-icon bg-info elevation-1"><i class="fa fa-users"></i></span>

                  <div class="info-box-content">

                     <span class="info-box-text">Total User</span>

                     <span class="info-box-number">

                        <?php echo $this->admin_model->get_total_users(); ?>

                     </span>

                  </div>

               </div>

            </div>
            <!-- /.col -->

            <div class="col-12 col-sm-6 col-md-3">

               <div class="info-box mb-3">

                  <span class="info-box-icon bg-success elevation-1"><i class="ion ion-bag"></i></span>

                  <div class="info-box-content">

                     <span class="info-box-text">Total Store</span>

                     <span class="info-box-number"><?php echo $this->admin_model->get_total_restaurants(); ?></span>

                  </div>

               </div>

            </div>

            <div class="clearfix hidden-md-up"></div>


            <div class="col-12 col-sm-6 col-md-3">

               <div class="info-box mb-3">

                  <span class="info-box-icon bg-danger elevation-1"><i class="fa fa-laptop"></i></span>


                  <div class="info-box-content">

                     <span class="info-box-text">Total Categories</span>

                     <span class="info-box-number"><?php echo $this->admin_model->get_total_category(); ?></span>

                  </div>

                  <!-- /.info-box-content -->

               </div>

               <!-- /.info-box -->

            </div>

            <!-- /.col -->

            <div class="col-12 col-sm-6 col-md-3">

               <div class="info-box mb-3">

                  <span class="info-box-icon bg-blue elevation-1"><i class="fa fa-product-hunt"></i></span>


                  <div class="info-box-content">

                     <span class="info-box-text">Total Products</span>

                     <span class="info-box-number"><?php echo $this->admin_model->get_total_products(); ?></span>

                  </div>

               </div>

            </div>

         </div>

      </div>

   </section>


   <section class="content">

      <div class="container-fluid">

         <!-- Info boxes -->

         <div class="row">

            <div class="col-md-12 col-sm-12 col-12">

               <div class="card card-primary">

                  <div class="card-header">

                     <h5 class="card-title">Month Wise Profit Data</h5>



                     <div class="card-tools">


                        <button type="button" class="btn btn-tool" data-card-widget="remove">

                           <i class="fas fa-times"></i>

                        </button>

                     </div>


                  </div>

                  <div class="row">

                     <div class="col-lg-12 col-md-12 col-sm-12 col-12">

                        <div class="panel panel-default">

                           <div class="panel-heading">

                              <div class="row">

                                 <div class="col-md-9">

                                    <!-- <h3 class="panel-title">Month Wise Profit Data</h3> -->

                                 </div>

                                 <div class="col-md-3">

                                    <select name="year" class="form-control" id="year">

                                       <option value="">Select Year</option>

                                       <?php

                                       $sql = "SELECT year FROM chart_data GROUP BY year DESC";

                                       $query = $this->db->query($sql);

                                       $result = $query->result_array();

                                       foreach ($result as $row) {

                                          echo '<option value="' . $row["year"] . '">' . $row["year"] . '</option>';
                                       }

                                       ?>

                                    </select>

                                 </div>

                              </div>

                           </div>

                           <div class="panel-body">

                              <div id="chart_area" style="height: 400px;"></div>

                           </div>

                        </div>

                     </div>

                  </div>



               </div>

               <!-- /.card -->

            </div>



         </div>

         <!-- /.row -->

      </div>

      <!--/. container-fluid -->

   </section>


   <?php

   $new_booking = $this->admin_model->get_new_booking();
   $users = $this->admin_model->get_new_users();

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

                     <h3 class="card-title">Latest 5 New User</h3>

                  </div>

                  <!-- /.card-header -->

                  <div class="card-body">

                     <table id="user_table" class="table table-bordered table-striped">

                        <thead>

                           <tr>

                              <th>Profile Image</th>
                              <th>Sr no</th>
                              <th>User Name</th>
                              <th>Email</th>

                           </tr>

                        </thead>



                        <tbody>



                           <?php if (isset($users)) {

                              $cnt = 1; ?>

                              <?php foreach ($users as $row) { ?>

                                 <tr>

                                    <td><?php echo $cnt++; ?></td>

                                    <td>
                                       <?php

                                       $profile = explode(":", $row->profile_pic);

                                       if ($profile[0] == "https" || $profile[0] == "http") { ?>

                                          <img src="<?php echo $row->profile_pic ?>" class="image brand-image img-circle elevation-3" height="40" width="40">

                                       <?php } else { ?>

                                          <?php if (empty($row->profile_pic)) { ?>

                                             <img src="<?php echo base_url('uploads/profile_pics/user.png') ?>" class="image brand-image img-circle elevation-3" height="40" width="40">

                                          <?php } else { ?>

                                             <img src="<?php echo base_url('uploads/profile_pics/') . $row->profile_pic ?>" class="image brand-image img-circle elevation-3" height="40" width="40"> <?php } ?>

                                       <?php } ?>
                                    </td>

                                    <td><?php echo $row->username; ?></td>

                                    <td><?php echo $row->email; ?></td>

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

   $store_reviews = $this->admin_model->get_allreview();
   $service_review = $this->admin_model->get_new_service_review();

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

<!-- /.content-wrapper -->

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<script type="text/javascript">
   google.charts.load('current', {

      packages: ['corechart', 'bar']

   });

   google.charts.setOnLoadCallback();



   function load_monthwise_data(year, title) {

      var temp_title = title + ' ' + year + '';

      $.ajax({

         url: "<?php echo base_url('admin/total-sales-get'); ?>",

         method: "POST",

         data: {

            year: year

         },

         dataType: "JSON",

         success: function(data) {

            drawMonthwiseChart(data, temp_title);

         }

      });

   }


   function drawMonthwiseChart(chart_data, chart_main_title) {

      var jsonData = chart_data;

      var data = new google.visualization.DataTable();

      data.addColumn('string', 'Month');

      data.addColumn('number', 'Profit');

      $.each(jsonData, function(i, jsonData) {

         var month = jsonData.month;

         var profit = parseFloat($.trim(jsonData.profit));

         data.addRows([

            [month, profit]

         ]);

      });

      var options = {

         title: chart_main_title,

         hAxis: {

            title: "Months"

         },

         vAxis: {

            title: 'Profit'

         }

      };

      var chart = new google.visualization.ColumnChart(document.getElementById('chart_area'));

      chart.draw(data, options);

   }
</script>



<script>
   $(document).ready(function() {

      var yearnew = new Date().getFullYear();

      load_monthwise_data(yearnew, 'Month Wise Profit Data For');

      // console.log(yearnew);

      $('#year').change(function() {

         var year = $(this).val();

         if (year != '') {

            load_monthwise_data(year, 'Month Wise Profit Data For');

         }

      });

   });
</script>