<?php
// $id = $this->uri->segment(3);
// $id = $this->session->userdata('aid');
// $profile = $this->admin_model->get_admin($id);
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1>Edit Product Category</h1>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="<?php echo base_url('admin/dashboard'); ?>">Home</a></li>
						<li class="breadcrumb-item active">Edit Product Category</li>
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
							<h3 class="card-title">Edit Category</h3>
						</div>
						<!-- /.card-header -->
						<!-- form start -->
						<form method="POST" enctype="multipart/form-data" action="<?php echo base_url('admin/update-product-category'); ?>">
							<div class="col-md-12">
								<div class="box-body">

									<div class="form-group">
										<input type="hidden" name="id" value="<?php echo $product_category->id; ?>">
										<label>Category Name</label>
										<input type="text" name="c_name" value="<?php echo $product_category->c_name; ?>" class="form-control" placeholder="Category Name" autocomplete="off"><?php echo form_error('c_name'); ?>
									</div>

									<div class="form-group">
										<label for="exampleInputFile">Category Images</label>
										<div class="input-group">
											<div class="custom-file">
												<input type="file" name="image" class="custom-file-input" id="exampleInputFile">
												<label class="custom-file-label" for="exampleInputFile">Choose file</label>
											</div>
										</div>
									</div>
									<div class="form-group">
										<?php $images = explode("::::", $product_category->image); ?>
										<?php foreach ($images as $key => $image) { ?>
											<img src="<?php echo base_url('uploads/') . $image ?>" class="img" height="70" width="70">
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