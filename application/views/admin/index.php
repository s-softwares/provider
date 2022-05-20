<?php

  $type = $this->session->userdata['user_type'];
?>

<?php  if($type == 'admin'){ ?> 
   <?php $this->load->view('admin/index_admin'); ?>
<?php }else{ ?>
   <?php $this->load->view('admin/index_vendor'); ?>
<?php }  ?>
