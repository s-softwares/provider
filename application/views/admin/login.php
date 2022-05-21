<!DOCTYPE html>
<html lang="en">
    
<style type="text/css">
	p.admin-tex {
    display: grid;
}
</style>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="<?php echo base_url(); ?>uploads/ez_logo.png" type="image/png" sizes="16x16">
	<title>Swaft | Log in</title>

	<!-- Google Font: Source Sans Pro -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
	<!-- Font Awesome -->
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/plugins/fontawesome-free/css/all.min.css">
	<!-- icheck bootstrap -->
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
	<!-- Theme style -->
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/dist/css/adminlte.min.css">
</head>

<body class="hold-transition login-page">
	<div class="login-box">

		<div class="login-logo">
			<a href="<?php echo base_url(); ?>admin/login"><img style="height:100px;width: 100px;" src="<?php echo base_url(); ?>uploads/Transparent_white.png"></a>
		</div>

		<!-- /.login-logo -->
		<div class="card card-outline card-primary">
			<div class="card-header text-center">
				<a href="#" class="h1"><b>Swaft</b> APP</a>
			</div>
			<div class="card-body">
				<!-- <p class="login-box-msg">Sign in to start your session</p> -->
				<?php if (!empty($this->session->flashdata('success'))) : ?>
					<div class="alert alert-success alert-dismissible fade show">
						<a href="javascript:void()" class="close" data-dismiss="alert" aria-label="close">&times;</a>
						<span> <?php echo $this->session->flashdata('success'); ?> </span>
					</div>
				<?php endif ?>
				<?php if ($this->session->flashdata('error')) : ?>
					<div class="alert alert-danger alert-dismissible fade show">
						<a href="javascript:void()" class="close" data-dismiss="alert" aria-label="close">&times;</a>
						<span><?php echo $this->session->flashdata('error') ?></span>
					</div>
				<?php endif ?>
				<!-- <form action="#" method="post"> -->
				<form role="form" method="post" action="<?php echo base_url('admin/login-admin'); ?>">
					<div class="input-group mb-3">
						<input type="email" class="form-control" placeholder="Email" name="email">
						<div class="input-group-append">
							<div class="input-group-text">
								<span class="fas fa-envelope"></span>
							</div>
						</div>
					</div>
					<div class="input-group mb-3">
						<input type="password" class="form-control" placeholder="Password" name="password">
						<div class="input-group-append">
							<div class="input-group-text">
								<span class="fas fa-lock"></span>
							</div>
						</div>
					</div>
					<div class="row">
						
						<!-- /.col -->
						<div class="col-4">
							<button type="submit" class="btn btn-primary btn-block">Sign In</button>
						</div>
						<!-- /.col -->
					</div>
					
					<br>
					<div class="input-group mb-3 card card-outline card-primary">
						<br>
						<p class="admin-tex pl-4"><b>Admin</b>
						admin@demo.com | Password: 123456
						</p>
						<p class="admin-tex pl-4"><b>Vendor</b>
						vendor@demo.com | Password: 123456
						vendor1@demo.com | Password: 123456</p>
					</div>
					
				</form>
				
			</div>
			<!-- /.card-body -->
		</div>
		<!-- /.card -->
	</div>
	<!-- /.login-box -->

	<!-- jQuery -->
	<script type="text/javascript" src="<?php echo base_url() ?>assets/plugins/jquery/jquery.min.js"></script>
	<!-- Bootstrap 4 -->
	<script type="text/javascript" src="<?php echo base_url() ?>assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
	<!-- AdminLTE App -->
	<script type="text/javascript" src="<?php echo base_url() ?>assets/dist/js/adminlte.min.js"></script>
</body>

</html>