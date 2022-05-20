<?php
// $id = $this->uri->segment(3);
$id = $this->session->userdata('aid');
$profile = $this->admin_model->get_admin($id);
$type = $this->admin_model->get_type();
?>

<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
<style type="text/css">
   #map {
      height: 100%;
   }

   /* Optional: Makes the sample page fill the window. */
   html,
   body {
      height: 100%;
      margin: 0;
      padding: 0;
   }

   #description {
      font-family: Roboto;
      font-size: 15px;
      font-weight: 300;
   }

   #infowindow-content .title {
      font-weight: bold;
   }

   #infowindow-content {
      display: none;
   }

   #map #infowindow-content {
      display: inline;
   }

   .pac-card {
      background-color: #fff;
      border: 0;
      border-radius: 2px;
      box-shadow: 0 1px 4px -1px rgba(0, 0, 0, 0.3);
      margin: 10px;
      padding: 0 0.5em;
      font: 400 18px Roboto, Arial, sans-serif;
      overflow: hidden;
      font-family: Roboto;
      padding: 0;
   }

   #pac-container {
      padding-bottom: 12px;
      margin-right: 12px;
   }

   .pac-controls {
      display: inline-block;
      padding: 5px 11px;
   }

   .pac-controls label {
      font-family: Roboto;
      font-size: 13px;
      font-weight: 300;
   }

   #pac-input {
      /*background-color: #fff;*/
      font-family: Roboto;
      font-size: 15px;
      /*padding: 0 11px 0 13px;*/
      text-overflow: ellipsis;
      /*width: 400px;*/
   }

   #pac-input:focus {
      border-color: #4d90fe;
   }

   #title {
      color: #fff;
      background-color: #4d90fe;
      font-size: 25px;
      font-weight: 500;
      padding: 6px 12px;
   }

   #target {
      width: 345px;
   }

   #map{
      height: 300px;
   }
   .map-section {
      width: 100%;
   }

   .map-section input{
      border: none;
      background-color: #efefef;
      border-radius: 10px;
      padding: 15px;
      width: 100%;
   }
</style>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
   <!-- Content Header (Page header) -->
   <section class="content-header">
      <div class="container-fluid">
         <div class="row mb-2">
            <div class="col-sm-6">
               <h1>Add Store</h1>
               <!-- <h1>Add Store</h1> -->
            </div>
            <div class="col-sm-6">
               <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="<?php echo base_url('admin/dashboard'); ?>">Home</a></li>
                  <li class="breadcrumb-item active">Add Store</li>
               </ol>
            </div>
         </div>
      </div><!-- /.container-fluid -->
   </section>
   <!-- Main content -->

   <section class="content">
      <div class="container-fluid">

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

         <div class="row">
            <!-- left column -->
            <div class="col-lg-12">
               <!-- general form elements -->
               <div class="card card-primary">
                  <div class="card-header">
                     <h3 class="card-title">Add Store</h3>
                  </div>
                  <!-- /.card-header -->
                  <!-- form start -->
                  <form method="POST" enctype="multipart/form-data" action="<?php echo base_url('admin/add-restaurants'); ?>">
                     <div class="col-md-12">
                        <div class="box-body">

                           <input type="hidden" name="vid" value="<?php echo $id; ?>">

                           <div class="form-group">
                              <label for="exampleInputEmail1">Store Name</label>
                              <input type="text" name="res_name" class="form-control" id="exampleInputEmail1" placeholder="Enter Store Name" required>
                           </div>

                           <div class="row">
                              <div class="col-sm-12">
                                 <div class="form-group">
                                    <label>Category</label>
                                    <select class="form-control" name="cat_id" required>
                                       <?php $category = $this->admin_model->get_category(); ?>
                                       <option value="">Select Category</option>
                                       <?php foreach ($category as $listing) : ?>
                                          <option value="<?php echo $listing['id']; ?>"><?php echo $listing['c_name']; ?></option>
                                       <?php endforeach; ?>
                                    </select>
                                 </div>
                              </div>
                           </div>

                           <div class="form-group">
                              <label>Description</label>
                              <textarea class="form-control" name="res_desc" rows="3" placeholder="Enter ..." required></textarea>
                           </div>

                           <div class="row">
                              <div class="col-md-6">
                                 <label for="exampleInputfnm">Store Phone Number</label>
                                 <div class="input-group">
                                    <input type="number" class="form-control" id="exampleInputfnm" placeholder="Enter Phone Number" name="res_phone">
                                    <div class="input-group-append">
                                       <div class="input-group-text">
                                          <span class="fa fa-mobile"></span>
                                       </div>
                                    </div>
                                 </div>
                                 <?php echo form_error('res_phone'); ?><br>
                              </div>
                              <div class="col-md-6">
                                 <label for="exampleInputfnm">Store Website</label>
                                 <div class="input-group">
                                    <input type="text" class="form-control" id="exampleInputfnm" placeholder="Enter Website" name="res_website">
                                    <div class="input-group-append">
                                       <div class="input-group-text">
                                          <span class="fa fa-globe"></span>
                                       </div>
                                    </div>
                                 </div>
                                 <?php echo form_error('res_website'); ?><br>
                              </div>
                           </div>

                           <div class="form-group">
                              <label for="exampleInputFile">Store Logo</label>
                              <div class="input-group">
                                 <div class="custom-file">
                                    <input type="file" name="logo" class="custom-file-input" id="exampleInputFile" required>
                                    <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                 </div>
                              </div>
                              <?php echo form_error('logo'); ?>
                           </div>

                           <div class="form-group">
                              <label for="exampleInputFile">Store Images</label>
                              <div class="input-group">
                                 <div class="custom-file">
                                    <input type="file" name="res_image[]" class="custom-file-input" id="exampleInputFile" required multiple>
                                    <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                 </div>
                              </div>
                              <?php echo form_error('res_image'); ?>
                           </div>

                           <label for="exampleInputfnm">ADD BUSINESS HOURS</label>
                           <div class="row">
                              <div class="col-md-2">
                                 <div class="form-group">
                                    <label for="exampleInputfnm">Monday From </label>
                                    <input type="text" class="form-control" id="exampleInputfnm" placeholder="00:00" name="monday_from">
                                 </div>
                              </div>

                              <div class="col-md-2">
                                 <div class="form-group">
                                    <label for="exampleInputfnm">Monday To</label>
                                    <input type="text" class="form-control" id="exampleInputfnm" placeholder="00:00" name="monday_to">
                                 </div>
                              </div>

                              <div class="col-md-2">
                                 <div class="form-group">
                                    <label for="exampleInputfnm">Tuesday From</label>
                                    <input type="text" class="form-control" id="exampleInputfnm" placeholder="00:00" name="tuesday_from">
                                 </div>
                              </div>

                              <div class="col-md-2">
                                 <div class="form-group">
                                    <label for="exampleInputfnm">Tuesday To</label>
                                    <input type="text" class="form-control" id="exampleInputfnm" placeholder="00:00" name="tuesday_to">
                                 </div>
                              </div>

                              <div class="col-md-2">
                                 <div class="form-group">
                                    <label for="exampleInputfnm">Wednesday From</label>
                                    <input type="text" class="form-control" id="exampleInputfnm" placeholder="00:00" name="wednesday_from">
                                 </div>
                              </div>

                              <div class="col-md-2">
                                 <div class="form-group">
                                    <label for="exampleInputfnm">Wednesday To</label>
                                    <input type="text" class="form-control" id="exampleInputfnm" placeholder="00:00" name="wednesday_to">
                                 </div>
                              </div>
                           </div>

                           <div class="row">
                              <div class="col-md-2">
                                 <div class="form-group">
                                    <label for="exampleInputfnm">Thursday From </label>
                                    <input type="text" class="form-control" id="exampleInputfnm" placeholder="00:00" name="thursday_from">
                                 </div>
                              </div>

                              <div class="col-md-2">
                                 <div class="form-group">
                                    <label for="exampleInputfnm">Thursday To</label>
                                    <input type="text" class="form-control" id="exampleInputfnm" placeholder="00:00" name="thursday_to">
                                 </div>
                              </div>

                              <div class="col-md-2">
                                 <div class="form-group">
                                    <label for="exampleInputfnm">Friday From</label>
                                    <input type="text" class="form-control" id="exampleInputfnm" placeholder="00:00" name="friday_from">
                                 </div>
                              </div>

                              <div class="col-md-2">
                                 <div class="form-group">
                                    <label for="exampleInputfnm">Friday To</label>
                                    <input type="text" class="form-control" id="exampleInputfnm" placeholder="00:00" name="friday_to">
                                 </div>
                              </div>

                              <div class="col-md-2">
                                 <div class="form-group">
                                    <label for="exampleInputfnm">Saturday From</label>
                                    <input type="text" class="form-control" id="exampleInputfnm" placeholder="00:00" name="saturday_from">
                                 </div>
                              </div>

                              <div class="col-md-2">
                                 <div class="form-group">
                                    <label for="exampleInputfnm">Saturday To</label>
                                    <input type="text" class="form-control" id="exampleInputfnm" placeholder="00:00" name="saturday_to">
                                 </div>
                              </div>

                              <div class="col-md-2">
                                 <div class="form-group">
                                    <label for="exampleInputfnm">Sunday From</label>
                                    <input type="text" class="form-control" id="exampleInputfnm" placeholder="00:00" name="sunday_from">
                                 </div>
                              </div>

                              <div class="col-md-2">
                                 <div class="form-group">
                                    <label for="exampleInputfnm">Sunday To</label>
                                    <input type="text" class="form-control" id="exampleInputfnm" placeholder="00:00" name="sunday_to">
                                 </div>
                              </div>
                           </div>

                           <div class="form-group">
                              <label for="exampleInputfnm">Pick Your Store Location</label>
                              <textarea class="form-control" name="store_address" rows="2" placeholder="Search Your Store Location" id="pac-input"></textarea>
                              <?php echo form_error('store_address'); ?>
                           </div>

                           <div class="form-group creat-map-img">
                              <div class="text-lg-center alert-danger" id="info"></div> 
                              <div id="map"></div>
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

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script type="text/javascript">

   // <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places">
   function initAutocomplete() {
      const map = new google.maps.Map(document.getElementById("map"), {
         center: {
            lat: -33.8688,
            lng: 151.2195
         },
         zoom: 13,
         mapTypeId: "roadmap",
      });
      // Create the search box and link it to the UI element.
      const input = document.getElementById("pac-input");
      const searchBox = new google.maps.places.SearchBox(input);

      // map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
     
      map.addListener("bounds_changed", () => {
         searchBox.setBounds(map.getBounds());
      });

      let markers = [];

      // Listen for the event fired when the user selects a prediction and retrieve
      // more details for that place.
      searchBox.addListener("places_changed", () => {
         const places = searchBox.getPlaces();

         if (places.length == 0) {
            return;
         }

         // Clear out the old markers.
         markers.forEach((marker) => {
            marker.setMap(null);
         });
         markers = [];

         // For each place, get the icon, name and location.
         const bounds = new google.maps.LatLngBounds();

         places.forEach((place) => {
            if (!place.geometry || !place.geometry.location) {
               console.log("Returned place contains no geometry");
               return;
            }

            const icon = {
               url: place.icon,
               size: new google.maps.Size(71, 71),
               origin: new google.maps.Point(0, 0),
               anchor: new google.maps.Point(17, 34),
               scaledSize: new google.maps.Size(25, 25),
            };

            // Create a marker for each place.
            markers.push(
               new google.maps.Marker({
                  map,
                  icon,
                  title: place.name,
                  position: place.geometry.location,
               })
            );
            if (place.geometry.viewport) {
               // Only geocodes have viewport.
               bounds.union(place.geometry.viewport);
            } else {
               bounds.extend(place.geometry.location);
            }
         });
         map.fitBounds(bounds);
      });
   }
</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCqQW9tN814NYD_MdsLIb35HRY65hHomco&callback=initAutocomplete&libraries=places&v=weekly" async></script>