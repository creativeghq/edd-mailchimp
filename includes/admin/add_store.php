<style type="text/css">
	.wp-list-table th{
	    padding-left: 10px;
	}
	.wp-list-table{
    	width: 98%;
	}
	tr.bold{
		font-weight: 800;
	}
</style>
<?php
	$edd_action = $_GET["edd_action"];
	if (isset($edd_action) && $edd_action == 'adb_mailchimp_disconnect_store') {
		
		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'edd-abd-disc' ) ) {
			return;
		}
		if(empty($_GET['store_id'])){
			return;
		}
		// Sync Products
		$api_data = new EDD_Mailchimp_Abandoned_Cart();
		$is_deleted = $api_data->delete_mailchimp_store($_GET['store_id']);
		
		add_action( 'admin_notices', 'edd_store_disc_notice' );
	}
	if (isset($edd_action) && $edd_action == 'adb_mailchimp_select_store') {
		
		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'edd-abd-select' ) ) {
			return;
		}
		if(empty($_GET['store_id'])){
			return;
		}
		edd_update_option( 'edd_abandoned_mailchimp_store_id', $_GET['store_id'] );
		add_action( 'admin_notices', 'edd_store_select_notice' );
	}
	

	function edd_store_disc_notice() {
		printf( '<div class="updated settings-error"> <p> %s </p> </div>', esc_html__( 'Store Deleted.', 'edd-mailchimp-abandoned-cart' ) );
	}
	function edd_store_select_notice() {
		printf( '<div class="updated settings-error"> <p> %s </p> </div>', esc_html__( 'Store selected for mailchimp abandoned cart.', 'edd-mailchimp-abandoned-cart' ) );
	}
?>


<div class="seting-main">
	<?php
		
		$api_data = new EDD_Mailchimp_Abandoned_Cart();
	
		if (isset($_POST["submit"])) {

			$response = $api_data->add_new_store($_POST);
			

			$store_Name = $_POST['edd_mailchimp_store_name'];
			if(isset($response['id'])){
				edd_update_option( 'edd_abandoned_mailchimp_store_id', $response['id'] );

	?>
           	<div class="updated settings-error"> <p>Store <strong>"<?= $store_Name;?>"</strong> has been saved.</p> </div>
            <br>
	<?php
			}else{
				$error = 'Error occured.';
				if(isset($response['detail'])){
					$error = $response['detail'];
				}
			?>
				<div class="error"> <p> <?= $error;?> </p> </div>
	            <br>
				<?php
			}
		}
	?>
	<div class="col8">
		<div class="delete-cache-head1">
			<h3>Manage Stores</h3> <small>These are the stores connected in your mail chimp account.</small>
		</div>
		<table class="form-table wp-list-table widefat">
			<thead>
				<tr>
					<th>Store Name</th>
					<th>Connected List Name</th>
					<th>Connected Site</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php 
					$api_data = new EDD_Mailchimp_Abandoned_Cart();
					$all_stores = $api_data->get_stores(true,'');

					global $edd_options;
    				$edd_abandoned_mailchimp_store_id = !empty( $edd_options['edd_abandoned_mailchimp_store_id'] ) ? $edd_options['edd_abandoned_mailchimp_store_id'] : '';

					if(isset($all_stores['stores'])){
						foreach ($all_stores['stores'] as $store) {
							$store_name = $store['name'];
							$store_list_name = '';
							$store_list_id = $store['list_id'];
							$store_id = $store['id'];
							$store_domain = $store['domain'];
							$store_site_script = $store['connected_site']['site_script'];
							$list_details = $api_data->edd_get_mailchimp_list_detail($store_list_id,'');
							if(isset($list_details['id'])){
								$store_list_name = $list_details['name'];
							}
						$select = "Select Store";
						$class = "";
						if($edd_abandoned_mailchimp_store_id == $store_id){
							$select = "Selected Store";
							$class = "bold";
						}
				?>
				<tr class="<?= $class;?>">
					<td>
						<?= $store_name;?>
					</td>
					<td>
						<span class="is-mailchimp-list-name "><?= $store_list_name;?></span>
					</td>

					<td>
						<a href="<?= $store_domain;?>"><?= $store_domain;?></a>
					</td>

					<td>
						<?php if(empty($store_domain) || $store_domain == home_url()){ 
								
								if($select == "Select Store"){
						?>
						<a class="edd-mailchimp-disconnect-list" style="color: green;" href="<?= wp_nonce_url( add_query_arg( array( 'edd_action' => 'adb_mailchimp_select_store','store_id' => $store_id) ), 'edd-abd-select' );?>"><?= $select;?></a>
						<?php } if($select == "Selected Store"){ ?>
							<label style="color: #000;cursor: auto;"><?= $select;?></label>
						<?php } ?>
						&nbsp; | &nbsp;
						<a class="edd-mailchimp-disconnect-list" style="color: red;" href="<?= wp_nonce_url( add_query_arg( array( 'edd_action' => 'adb_mailchimp_disconnect_store','store_id' => $store_id) ), 'edd-abd-disc' );?>">Disconnect</a>
						<?php } ?>
					</td>
				</tr>
				<?php 
						}
					}
				?>
			</tbody>
		</table>
	</div>
	<div class="col4">
		<div class="delete-cache-head1">
			<h3>Add New Store</h3>
		</div>
		<div class="delete-cache-body">
			<form  method="post">
			   
			    <table class="form-table">
			        <tbody>
			            
			            <tr class="edd_mailchimp_store_id">
			                <th scope="row">Choose a List</th>
			                <td>
			                    <select id="edd_mailchimp_list_id" name="edd_mailchimp_list_id" class="regular" data-placeholder="">
			                    	
			                    	<?php 
			                    		$err_abanMailChimpId='';
			                    		$data=$api_data->edd_get_mailchimp_lists();
			                    		
			                    		if($data['false'])
			                    		{
			                    			$err_abanMailChimpId=$data['false'];
			                    		}
			                    		else
			                    		{
			                    			if(isset($data) && !empty($data))
			                    			{
				                    			foreach ($data as $key => $value) {
				                    			?>
				                    				<option value="<?= $key;?>"><?= $value;?></option>
				                    			<?php
				                    			}
			                    			}
			                    		}
			                    	?>			                    	
			                    </select>
			                    <br>
			                    <span class="error"></span>
			                </td>
			            </tr>
			            <tr class="edd_mailchimp_store_id">
			                <th scope="row">Store Name</th>
			                <td>
		                    	<input class="regular-text" id="edd_mailchimp_store_name" name="edd_mailchimp_store_name"  placeholder="" type="text">
			                    
			                    <br>
			                    <span class="error"></span>
			                </td>
			            </tr>
			        </tbody>
			    </table>
			    <p class="submit">
			        <input name="submit" id="submit" class="button button-primary" value="Save Changes" type="submit">

			        <a href="<?= get_admin_url();?>edit.php?post_type=download&page=edd-settings&tab=extensions&section=mailchimp_abandoned_cart" class="button button-primary"> Go Back</a>
			    </p>
			</form>
		</div>
	</div>
	<div class="clear"></div>
</div>


