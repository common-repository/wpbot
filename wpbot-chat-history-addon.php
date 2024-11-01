<?php
/**
* Plugin Name: WPBot
* Plugin URI: https://wordpress.org/plugins/wpbot/
* Description: WPBot Chat History addon for saving WPBot chat history. 
* Version: 0.9.6
* Author: WPBot ChatBot
* Author URI: https://www.wpbot.pro/
* Requires at least: 4.6
* Tested up to: 6.5
* License: GPL2
*/

if(!function_exists('qcpdcs_chat_session_menu_fnc')){
	defined('ABSPATH') or die("No direct script access!");
	define('QCPDCS_WPCHATBOT_HISTORY_PLUGIN_URL', plugin_dir_url(__FILE__));
	define('QCPDCS_WPCHATBOT_HISTORY_DIR_PATH', plugin_dir_path(__FILE__));
	define('QCPDCS_WPCHATBOT_HISTORY_VERSION', '0.0.6');
	require_once("class-qc-free-plugin-upgrade-notice.php");
	add_action('init', 'qcpdcs_chat_session_dependencies');
	function qcpdcs_chat_session_dependencies(){
		include_once(ABSPATH.'wp-admin/includes/plugin.php');
		if ( !class_exists('qcld_wb_Chatbot') && !class_exists('QCLD_Woo_Chatbot') && (qcpdcs_is_kbxwpbot_active() != true) ) {
			add_action('admin_notices', 'qcpdcs_require_notice');
		} 
	}

	/**
	 *
	 * Function to load translation files.
	 *
	 */
	function qcpdcs_chat_session_lang_init() {
		load_plugin_textdomain( 'wpbot-cs', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
	add_action( 'plugins_loaded', 'qcpdcs_chat_session_lang_init');

	add_action( 'admin_menu', 'qcpdcs_chat_session_menu_fnc' );

	function qcpdcs_chat_session_menu_fnc(){
		
		if ( current_user_can( 'publish_posts' ) && ( class_exists('qcld_wb_Chatbot') || class_exists('QCLD_Woo_Chatbot') ) ){

			add_menu_page( 'Bot - Sessions', 'Bot - Sessions', 'publish_posts', 'wbcs-botsessions-page', 'qcpdcs_wpbcs_menu_page_callback_func', 'dashicons-menu', '9' );
			
			if(qcpdcs_is_woowbot_active()){
				add_submenu_page( 'wbcs-botsessions-page', 'ChatBot Sessions', 'ChatBot Sessions', 'manage_options','woowbot_cs_menu_page', 'qcpdcs_woowbot_cs_menu_page_callback_func' );
			}

		}
		
	}

	function qcpdcs_require_notice()
	{
	?>
		<div id="message" class="error">
			<p>
				<?php echo esc_html( 'Please install & activate the WPBot or WoowBot WooCommerce Chatbot plugin to get the Chat Session Addon work properly.' ) ?>
			</p>
		</div>
	<?php
	}

	function qcpdcs_wpbcs_menu_page_callback_func(){

		global $wpdb;
		$wpdb->show_errors = true;
		
		$tableuser    = $wpdb->prefix.'wpbot_user';
		$tableconversation    = $wpdb->prefix.'wpbot_Conversation';
		$mainurl = admin_url( 'admin.php?page=wbcs-botsessions-page');
		if( isset($_GET['min_interaction']) ){
			$mainurl = $mainurl.'&min_interaction='. sanitize_text_field( $_GET['min_interaction'] );
		}

		if( isset($_GET['wp_user']) && $_GET['wp_user'] !="" ){
			$mainurl = $mainurl.'&wp_user='. sanitize_text_field( $_GET['wp_user'] );
		}
		
		$msg = '';
		if(isset($_GET['action']) && $_GET['action']=='deleteall'){
			global $wpdb;
			$wpdb->query("TRUNCATE TABLE `$tableuser`");
			$wpdb->query("TRUNCATE TABLE `$tableconversation`");
			$msg = esc_html('All Sessions has been deleted successfully!');
		}
		
		if(isset($_GET['msg']) && $_GET['msg']=='success'){
			echo '<div class="notice notice-success"><p>'.esc_html( 'Record has beed Deleted Successfully!' ).'</p></div>';
		}
		
		if(isset($_GET['userid']) && $_GET['userid']!=''){
		$userid = sanitize_text_field($_GET['userid']);
		$userinfo = $wpdb->get_row( "select * from $tableuser where 1 and id = '".$userid."'" );
		$delurl = admin_url( 'admin.php?page=wbcs-botsessions-page&userid='.$userinfo->id.'&act=delete');
		?>	
		<div class="sld_menu_title" style="text-align:left;">
			<table class="form-table">
				<tbody>
				<tr><th style="padding: 5px;" scope="row"><?php echo esc_html('User Name'); ?></th><td style="padding: 5px;"><?php echo esc_html($userinfo->name) ?></td></tr>

				<tr><th style="padding: 5px;" scope="row"><?php echo esc_html('Date'); ?></th><td style="padding: 5px;"><?php echo date('M,d,Y h:i:s A', strtotime($userinfo->date)); ?></td></tr>
				</tbody>
			</table>

			<a href="<?php echo esc_url($delurl); ?>" class="button-primary" onclick="return confirm('are you sure?')" style="float: right; position: relative; top: -65px; right: 21px;"><?php echo esc_html('Delete'); ?></a>
			
		</div>
		<?php 
		
		$result = $wpdb->get_row("select * from $tableconversation where 1 and user_id = '".$userid."'");
		
			if(!empty($result)):
			
			$qcld_wb_chatbot_theme = get_option('qcld_wb_chatbot_theme');
			if (file_exists(QCLD_wpCHATBOT_PLUGIN_DIR_PATH . '/templates/' . $qcld_wb_chatbot_theme . '/style.css')) {
				wp_register_style('qcld-wp-chatbot-style', QCLD_wpCHATBOT_PLUGIN_URL . '/templates/' . $qcld_wb_chatbot_theme . '/style.css', '', QCLD_wpCHATBOT_VERSION, 'screen');
				wp_enqueue_style('qcld-wp-chatbot-style');
			}
			wp_register_style('qcld-wp-chatbot-history-style', QCPDCS_WPCHATBOT_HISTORY_PLUGIN_URL . '/css/history-style.css', '', QCLD_wpCHATBOT_VERSION, 'screen');
			wp_enqueue_style('qcld-wp-chatbot-history-style');
			wp_register_style('qcld-wp-chatbot-common-style', QCLD_wpCHATBOT_PLUGIN_URL . '/css/common-style.css', '', QCLD_wpCHATBOT_VERSION, 'screen');
			wp_enqueue_style('qcld-wp-chatbot-common-style');
			
			
		?>
			<div class="qchero_sliders_list_wrapper">
			<div class="qchero_slider_table_area" style="max-width: 650px;">
				<div class="wp-chatbot-messages-wrapper">
				<?php echo wp_kses_post( htmlspecialchars_decode( $result->conversation ) ); ?>
				</div>
			</div>
			</div>
		<?php
			endif;
		}else{

			wp_register_style('qcld-wp-chatbot-history-style', QCPDCS_WPCHATBOT_HISTORY_PLUGIN_URL . '/css/history-style.css', '', QCLD_wpCHATBOT_VERSION, 'screen');
			wp_enqueue_style('qcld-wp-chatbot-history-style');
			wp_enqueue_style('qcld-wp-chatbot-jquery-ui');
			wp_register_style('qcld-wp-chatbot-jquery-ui', QCPDCS_WPCHATBOT_HISTORY_PLUGIN_URL . '/css/jqueryui.css', '', '', 'screen');
			wp_register_script('qcld-wp-chatsession-admin-js',QCPDCS_WPCHATBOT_HISTORY_PLUGIN_URL . '/js/chatsession.js' , array('jquery'), true);
			wp_enqueue_script('qcld-wp-chatsession-admin-js');
			wp_localize_script('qcld-wp-chatsession-admin-js', 'ajax_object',
					array('ajax_url' => admin_url('admin-ajax.php')));
			
			$where = '';
			if( isset( $_GET['min_interaction'] ) && $_GET['min_interaction'] != 'all' ){

				if( isset( $_GET['min_interaction'] ) && $_GET['min_interaction'] > 0 ){
					$where = ' and `interaction` >= '. sanitize_text_field( $_GET['min_interaction'] );
				}
				if( isset( $_GET['min_interaction'] ) && $_GET['min_interaction'] == 0 ){
					$where = ' and `interaction` = '.sanitize_text_field( $_GET['min_interaction'] );
				}

			}
			
			$wwhere = '';
			if( isset( $_GET['wp_user'] ) && $_GET['wp_user'] != "all" && $_GET['wp_user'] != 0 && $_GET['wp_user'] != "" ){
				$wwhere = ' and `user_id` = '.sanitize_text_field( $_GET['wp_user'] );
			}
			
			$sql = "SELECT * FROM $tableuser WHERE 1 $where $wwhere order by `date` DESC";
			
			$sql1 = "SELECT count(*) FROM $tableuser WHERE 1 $where $wwhere order by `date` DESC";
			
			$total             = $wpdb->get_var( $sql1 );
			$items_per_page = 30;
			$page             = isset( $_GET['cpage'] ) ? abs( (int) sanitize_text_field( $_GET['cpage'] ) ) : 1;
			$offset         = ( $page * $items_per_page ) - $items_per_page;
			$sql .=" LIMIT ${offset}, ${items_per_page}";
			$rows = $wpdb->get_results( $sql );
			$totalPage         = ceil($total / $items_per_page);
			$result = $wpdb->get_results($sql);
			$customPagHTML = '';
			if($totalPage > 1){
				$customPagHTML     =  '<div><span class="wpbot_pagination">Page '.esc_html($page).' of '.esc_html($totalPage).'</span>'.paginate_links( array(
				'base' => add_query_arg( 'cpage', '%#%' ),
				'format' => '',
				'prev_text'    => __('« prev'),
				'next_text'    => __('next »'),
				'total' => esc_html($totalPage),
				'current' => esc_html($page),
				
				)).'</div>';
			}
	$deleteurl = admin_url( 'admin.php?page=wbcs-botsessions-page&action=deleteall');
		?>
		<div class="qchero_sliders_list_wrapper">
			<?php 
				if($msg!=''){
					?>
					<div class="notice notice-success is-dismissible">
						<p><?php echo esc_html($msg); ?></p>
					</div>
					<?php
				}
			?>
			<div class="sld_menu_title">
				<h2 style="font-size: 26px;text-align:center"><?php echo esc_html__('Chat Sessions', 'wpbot-cs').' ('.esc_html( $total ).')'; ?></h2>
			</div>
			
			<?php if($customPagHTML!=''): ?>
			<div class="sld_menu_title sld_menu_title_align"><?php echo wp_kses_post($customPagHTML); ?> </div>
			<?php endif; ?>
			
			<!-- <form id="wpcs_form_sessions_min" action="<?php //echo esc_url($mainurl); ?>" method="GET" style="width:100%;margin-top: 20px;">
			<input type="hidden" name="page" value="wbcs-botsessions-page" />
			Filter by Minimum Interaction <select name="min_interaction" id="qcld_session_min_interaction">
				<option value="all" <?php// echo (isset($_GET['min_interaction']) && $_GET['min_interaction']=='all' ?'selected="selected"':'') ?> >All</option>
				<option value="0" <?php //echo (isset($_GET['min_interaction']) && $_GET['min_interaction']==0 ?'selected="selected"':'') ?>>0</option>
				<option value="1" <?php //echo (isset($_GET['min_interaction']) && $_GET['min_interaction']==1 ?'selected="selected"':'') ?>>1</option>
				<option value="2" <?php //echo (isset($_GET['min_interaction']) && $_GET['min_interaction']==2 ?'selected="selected"':'') ?>>2</option>
				<option value="3" <?php //echo (isset($_GET['min_interaction']) && $_GET['min_interaction']==3 ?'selected="selected"':'') ?>>3</option>
				<option value="4" <?php //echo (isset($_GET['min_interaction']) && $_GET['min_interaction']==4 ?'selected="selected"':'') ?>>4</option>
				<option value="5" <?php //echo (isset($_GET['min_interaction']) && $_GET['min_interaction']==5 ?'selected="selected"':'') ?>>5</option>
			</select>

			<?php 
			// $wpusersql = "SELECT DISTINCT `user_id` FROM $tableuser WHERE 1 $where order by `date` DESC limit 0, 150";
			// $wp_user_ids =  $wpdb->get_col( $wpusersql );

			// if ( ! empty( $wp_user_ids ) ) {
			// 	$args = array(
			// 		'include' => $wp_user_ids, // These are the ID's of users you want to get.
			// 		'fields'   => array( 'ID', 'display_name' ),
			// 	);
				
			// 	$wp_users = get_users( $args );
			// 	if( ! empty( $wp_users ) ) {
					?>
					<?php //echo esc_html( 'Filter By Registered User' ); ?> <select name="wp_user" id="qcld_session_wp_user">
						<option value="">All</option>
						<?php 
							//foreach( $wp_users as $wp_user ) {
								?>
								<option value="<?php //echo esc_html( $wp_user->ID ); ?>" <?php// echo ( isset($_GET['wp_user']) && $_GET['wp_user'] == $wp_user->ID ? 'selected="selected"' : '' ); ?> > <?php// echo esc_html( $wp_user->display_name ); ?> </option>
								<?php
						//	}
						?>
					</select>
					<?php
			// 	}
			// }

			?>
			
			</form> -->
			
			<form id="wpcs_form_sessions" action="<?php echo esc_url($mainurl); ?>" method="POST" style="width:100%">
			<input type="hidden" name="wpbot_session_remove" />
			<br>
			<button class="button-primary" id="wpbot_submit_session_delete" name="wpbot_session_delete"><?php echo esc_html('Delete'); ?></button>
			<a href="<?php echo esc_url($deleteurl); ?>" class="button button-primary" ><?php echo esc_html('Delete All Sessions'); ?></a>
			
			<?php if(!empty($result)): ?>
			
			<div class="qchero_slider_table_area">
				<div class="sld_payment_table">
					<div class="sld_payment_row header">
					
						<div class="sld_payment_cell">
							<input type="checkbox" id="wpbot_checked_all" />
						</div>
						
						<div class="sld_payment_cell">
							<?php echo esc_html__( 'Date', 'wpbot-cs' ) ?>
						</div>
						<div class="sld_payment_cell">
							<?php echo esc_html__( 'User Interaction Count', 'wpbot-cs' ) ?>
						</div>
						<div class="sld_payment_cell">
							<?php echo esc_html__( 'Session ID', 'wpbot-cs' ) ?>
						</div>
						
						<div class="sld_payment_cell">
							<?php echo esc_html__( 'Name', 'wpbot-cs' ); ?>
						</div>

						<div class="sld_payment_cell">
							<?php echo esc_html__( 'Action', 'wpbot-cs' ); ?>
						</div>
						
					</div>

			<?php
			foreach($result as $row){
				$url = admin_url( 'admin.php?page=wbcs-botsessions-page&userid='.$row->id);
				$delurl = admin_url( 'admin.php?page=wbcs-botsessions-page&userid='.$row->id.'&act=delete');
			?>
				<div class="sld_payment_row body">
					
					<div class="sld_payment_cell">
						
						<input type="checkbox" name="sessions[]" class="wpbot_sessions_checkbox" value="<?php echo esc_html($row->id) ?>" />
					</div>
					
					<div class="sld_payment_cell">
						<div class="sld_responsive_head"><?php echo esc_html__('Date', 'wpbot-cs') ?></div>
						<a href="<?php echo esc_url($url); ?>"><?php echo date('M,d,Y h:i:s A', strtotime($row->date)); ?></a>
					</div>
					<div class="sld_payment_cell">
						<div class="sld_responsive_head"><?php echo esc_html__('User Interaction Count', 'wpbot-cs') ?></div>
						<?php
							$res = $wpdb->get_row("select * from $tableconversation where 1 and user_id = '".$row->id."'");
							echo esc_html( $res->interaction );
						?>
					</div>
					<div class="sld_payment_cell">
						<div class="sld_responsive_head"><?php echo esc_html__('Session ID', 'wpbot-cs') ?></div>
						<?php echo esc_html($row->session_id); ?>
					</div>
					
					<div class="sld_payment_cell">
						<div class="sld_responsive_head"><?php echo esc_html__('Name', 'wpbot-cs') ?></div>
						<?php echo esc_html($row->name); ?>
					</div>

					<div class="sld_payment_cell">
						<div class="sld_responsive_head"><?php echo esc_html__('Action', 'wpbot-cs') ?></div>
						<a href="<?php echo esc_url($url); ?>" class="button-primary"><?php echo esc_html('View Chat') ?></a>
						<a href="<?php echo esc_url($delurl); ?>" class="button-primary" onclick="return confirm('are you sure?')"><?php echo esc_html('Delete'); ?></a>
						<?php if($row->email!=''): ?>
						<a href="#" data-email="<?php echo esc_html($row->email); ?>" class="button-primary wpcsmyBtn"><?php echo esc_html('Send Email') ?></a>
						<?php endif; ?>
					</div>
					
				</div>
			<?php
			}
			?>

			</div>

		</div>
		</form>
		<?php endif; ?>
		</div>
		<?php
		}
	}

	function qcpdcs_woowbot_cs_menu_page_callback_func(){
		global $wpdb;
		$wpdb->show_errors = true;
		
		$tableuser    = $wpdb->prefix.'wowbot_user';
		$tableconversation    = $wpdb->prefix.'wowbot_Conversation';

		
		$msg = '';
		if(isset($_GET['action']) && $_GET['action']=='deleteall'){
			global $wpdb;
			$wpdb->query("TRUNCATE TABLE `$tableuser`");
			$wpdb->query("TRUNCATE TABLE `$tableconversation`");
			$msg = esc_html('All Sessions has been deleted successfully!');
		}
		
		if(isset($_GET['msg']) && $_GET['msg']=='success'){
			echo '<div class="notice notice-success"><p>Record has beed Deleted Successfully!</p></div>';
		}
		
		if(isset($_GET['userid']) && $_GET['userid']!=''){
		$userid = sanitize_text_field( $_GET['userid'] );
		$userinfo = $wpdb->get_row("select * from $tableuser where 1 and id = '".$userid."'");
		?>	
		<div class="sld_menu_title" style="text-align:left;">
			<table class="form-table">
				<tbody>
				<tr><th style="padding: 5px;" scope="row"><?php echo esc_html('User Name'); ?></th><td style="padding: 5px;"><?php echo esc_html($userinfo->name); ?></td></tr>

				<tr><th style="padding: 5px;" scope="row"><?php echo esc_html('Date'); ?></th><td style="padding: 5px;"><?php echo date('M,d,Y h:i:s A', strtotime($userinfo->date)); ?></td></tr>
				</tbody>
			</table>
		</div>
		<?php 
		
		$result = $wpdb->get_row("select * from $tableconversation where 1 and user_id = '".$userid."'");
		
			if(!empty($result)):
			
			$qcld_wb_chatbot_theme = get_option('qcld_woo_chatbot_theme');
			if (file_exists(QCLD_WOOCHATBOT_PLUGIN_DIR_PATH . '/templates/' . $qcld_wb_chatbot_theme . '/style.css')) {
				wp_register_style('qcld-wp-chatbot-style', QCLD_WOOCHATBOT_PLUGIN_URL . '/templates/' . $qcld_wb_chatbot_theme . '/style.css', '', QCLD_WOOCHATBOT_VERSION, 'screen');
				wp_enqueue_style('qcld-wp-chatbot-style');
			}
			wp_register_style('qcld-wp-chatbot-history-style', QCPDCS_WPCHATBOT_HISTORY_PLUGIN_URL . '/css/history-style.css', '', QCLD_WOOCHATBOT_VERSION, 'screen');
			wp_enqueue_style('qcld-wp-chatbot-history-style');
			wp_register_style('qcld-wp-chatbot-common-style', QCLD_WOOCHATBOT_PLUGIN_URL . '/css/common-style.css', '', QCLD_WOOCHATBOT_VERSION, 'screen');
			wp_enqueue_style('qcld-wp-chatbot-common-style');
			
			
		?>
			<div class="qchero_sliders_list_wrapper">
			<div class="qchero_slider_table_area" style="max-width: 650px;">
				<div id="woo-chatbot-shortcode-template-container" class="wp-chatbot-messages-wrapper">
				<?php echo wp_kses_post( htmlspecialchars_decode($result->conversation) ); ?>
				</div>
			</div>
			</div>
		<?php
			endif;
		}else{
			wp_register_style('qcld-wp-chatbot-history-style', QCPDCS_WPCHATBOT_HISTORY_PLUGIN_URL . '/css/history-style.css', '', QCLD_wpCHATBOT_VERSION, 'screen');
			wp_enqueue_style('qcld-wp-chatbot-history-style');
			
			wp_register_script('qcld-wp-chatsession-admin-js',QCPDCS_WPCHATBOT_HISTORY_PLUGIN_URL . '/js/chatsession.js' , array('jquery'), true);
			wp_enqueue_script('qcld-wp-chatsession-admin-js');
			wp_localize_script('qcld-wp-chatsession-admin-js', 'ajax_object',
					array('ajax_url' => admin_url('admin-ajax.php')));
			
			
			$sql = "select * from $tableuser where 1 order by `date` DESC";
			$result1 = $wpdb->get_results($sql);
			$sql1 = "SELECT count(*) FROM $tableuser where 1";
			
			$total             = $wpdb->get_var( $sql1 );
			$items_per_page = 30;
			$page             = isset( $_GET['cpage'] ) ? abs( (int) sanitize_text_field( $_GET['cpage'] ) ) : 1;
			$offset         = ( $page * $items_per_page ) - $items_per_page;
			$sql .=" LIMIT ${offset}, ${items_per_page}";
			$rows = $wpdb->get_results( $sql );
			$totalPage         = ceil($total / $items_per_page);
			$result = $wpdb->get_results($sql);

			if($totalPage > 1){
				$customPagHTML     =  '<div><span class="wpbot_pagination">Page '.esc_html($page).' of '.esc_html($totalPage).'</span>'.paginate_links( array(
				'base' => add_query_arg( 'cpage', '%#%' ),
				'format' => '',
				'prev_text'    => __('« prev'),
				'next_text'    => __('next »'),
				'total' => esc_html($totalPage),
				'current' => esc_html($page),
				
				)).'</div>';
			}
			$mainurl = admin_url( 'admin.php?page=woowbot_cs_menu_page');
			$deleteurl = admin_url( 'admin.php?page=woowbot_cs_menu_page&action=deleteall');
		?>
		<div class="qchero_sliders_list_wrapper">
		
			<?php 
				if($msg!=''){
					?>
					<div class="notice notice-success is-dismissible">
						<p><?php echo esc_html($msg); ?></p>
					</div>
					<?php
				}
			?>
		
			<div class="sld_menu_title">
				<h2 style="font-size: 26px;text-align:center"><?php echo esc_html__('Chat Sessions', 'wpbot-cs').' ('.count($result1).')'; ?></h2>
			</div>
			<?php if($customPagHTML!=''): ?>
			<div class="sld_menu_title sld_menu_title_align"><?php echo wp_kses_post($customPagHTML); ?> </div>
			<?php endif; ?>
			<?php if(!empty($result)): ?>
			
			<form id="wpcs_form_sessions" action="<?php echo esc_url($mainurl); ?>" method="POST" style="width:100%">
			<input type="hidden" name="wowbot_session_remove" />
			<br>
			<button class="button-primary" id="wpbot_submit_delcs_form"><?php echo esc_html('Delete'); ?></button>
			<a href="<?php echo esc_url($deleteurl); ?>" class="button button-primary" ><?php echo esc_html('Delete All Sessions'); ?></a>
			<div class="qchero_slider_table_area">
				<div class="sld_payment_table">
					<div class="sld_payment_row header">
						
						<div class="sld_payment_cell">
							<input type="checkbox" id="wpbot_checked_all" />
						</div>
						
						<div class="sld_payment_cell">
							<?php echo esc_html__( 'Date', 'wpbot-cs' ) ?>
						</div>
						<div class="sld_payment_cell">
							<?php echo esc_html__( 'User Interaction Count', 'wpbot-cs' ) ?>
						</div>
						<div class="sld_payment_cell">
							<?php echo esc_html__( 'Session ID', 'wpbot-cs' ) ?>
						</div>
						
						<div class="sld_payment_cell">
							<?php echo esc_html__( 'Name', 'wpbot-cs' ); ?>
						</div>
						<div class="sld_payment_cell">
							<?php echo esc_html__( 'Action', 'wpbot-cs' ); ?>
						</div>
						
					</div>

			<?php
			foreach($result as $row){
				$url = admin_url( 'admin.php?page=woowbot_cs_menu_page&userid='.$row->id);
				$delurl = admin_url( 'admin.php?page=woowbot_cs_menu_page&userid='.$row->id.'&act=delete');
			?>
				<div class="sld_payment_row body">
				
					<div class="sld_payment_cell">
						
						<input type="checkbox" name="sessions[]" class="wpbot_sessions_checkbox" value="<?php echo esc_html($row->id) ?>" />
					</div>
					
					<div class="sld_payment_cell">
						<div class="sld_responsive_head"><?php echo esc_html__('Date', 'wpbot-cs') ?></div>
						<a href="<?php echo esc_url($url); ?>"><?php echo date('M,d,Y h:i:s A', strtotime($row->date)); ?></a>
					</div>
					<div class="sld_payment_cell">
						<div class="sld_responsive_head"><?php echo esc_html__('User Interaction Count', 'wpbot-cs') ?></div>
						<?php
							$res = $wpdb->get_row("select * from $tableconversation where 1 and user_id = '".$row->id."'");
							echo esc_html( substr_count($res->conversation, "woo-chat-user-msg") );
						?>
					</div>
					<div class="sld_payment_cell">
						<div class="sld_responsive_head"><?php echo esc_html__('Session ID', 'wpbot-cs') ?></div>
						<?php echo esc_html($row->session_id); ?>
					</div>
					
					<div class="sld_payment_cell">
						<div class="sld_responsive_head"><?php echo esc_html__('Name', 'wpbot-cs') ?></div>
						<?php echo esc_html($row->name); ?>
					</div>

					<div class="sld_payment_cell">
						<div class="sld_responsive_head"><?php echo esc_html__('Action', 'wpbot-cs') ?></div>
						<a href="<?php echo esc_url($url); ?>" class="button-primary"><?php echo esc_html('View Chat'); ?></a>
						<a href="<?php echo esc_url($delurl); ?>" class="button-primary" onclick="return confirm('are you sure?')"><?php echo esc_html('Delete'); ?></a>
						<?php if($row->email!=''): ?>
						<a href="#" data-email="<?php echo esc_html($row->email); ?>" class="button-primary wpcsmyBtn"><?php echo esc_html('Send Email'); ?></a>
						<?php endif; ?>
					</div>
					
				</div>
			<?php
			}
			?>

			</div>

		</div>
		</form>
		<?php endif; ?>
		</div>
		<?php
		}
	}

	if(!function_exists('qcpdcs_isset_table_column')) {
		function qcpdcs_isset_table_column($table_name, $column_name)
		{
			global $wpdb;
			$columns = $wpdb->get_results("SHOW COLUMNS FROM  " . $table_name, ARRAY_A);
			foreach ($columns as $column) {
				if ($column['Field'] == $column_name) {
					return true;
				}
			}
		}
	}

	register_activation_hook(__FILE__, 'qcpdcs_chatboot_sessions_defualt_options');
	function qcpdcs_chatboot_sessions_defualt_options(){
		global $wpdb;
		$collate = '';
		update_option('enable_chat_session', '1');
		if ( $wpdb->has_cap( 'collation' ) ) {

			if ( ! empty( $wpdb->charset ) ) {

				$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {

				$collate .= " COLLATE $wpdb->collate";

			}
		}
		
		//Bot User Table
		$table1    = $wpdb->prefix.'wpbot_user';
		$sql_sliders_Table1 = "
			CREATE TABLE IF NOT EXISTS `$table1` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`session_id` varchar(256) NOT NULL,
			`name` varchar(256) NOT NULL,
			`email` varchar(256) NOT NULL,
			`date` datetime NOT NULL,
			`phone` varchar(256) NOT NULL,
			`interaction` int(11) NOT NULL,
			`user_id` int(11) NOT NULL,
			PRIMARY KEY (`id`)
			)  $collate AUTO_INCREMENT=1 ";
			
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql_sliders_Table1 );
		
		if ( ! qcpdcs_isset_table_column( $table1, 'phone' ) ) {
			$sql_wp_Table_update_1 = "ALTER TABLE `$table1` ADD `phone` varchar(256) NOT NULL;";
			$wpdb->query( $sql_wp_Table_update_1 );
		}

		//Bot User Table
		$table2    = $wpdb->prefix.'wpbot_Conversation';
		$sql_sliders_Table2 = "
			CREATE TABLE IF NOT EXISTS `$table2` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`user_id` int(11) NOT NULL,
			`conversation` LONGTEXT NOT NULL,
			`interaction` int(11) NOT NULL,
			PRIMARY KEY (`id`)
			)  $collate AUTO_INCREMENT=1 ";
			
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql_sliders_Table2 );

		if ( ! qcpdcs_isset_table_column( $table2, 'interaction' ) ) {
			$sql_wp_Table_update_1 = "ALTER TABLE `$table2` ADD `interaction` int(11) NOT NULL;";
			$wpdb->query( $sql_wp_Table_update_1 );
		}
		if ( ! qcpdcs_isset_table_column( $table1, 'interaction' ) ) {
			$sql_wp_Table_update_1 = "ALTER TABLE `$table1` ADD `interaction` int(11) NOT NULL;";
			$wpdb->query( $sql_wp_Table_update_1 );
		}
		
	}
	register_deactivation_hook( __FILE__, 'qcld_wb_chatboot_sessions_deactivation_option' );
	function qcld_wb_chatboot_sessions_deactivation_option(){
		update_option('enable_chat_session', '0');
	}
	function qcpdcs_is_woowbot_active(){
		if(class_exists('QCLD_Woo_Chatbot')){
			return true;
		}else{
			return false;
		}
	}

	function qcpdcs_is_wpbot_active(){
		
		if(class_exists('qcld_wb_Chatbot')){
			return true;
		}else{
			return false;
		}
		
	}

	add_action('init', 'qcpdcs_request_handle');

	function qcpdcs_request_handle(){
		global $wpdb;
		$wpdb->show_errors = true;
		
		$tableuser    = $wpdb->prefix.'wowbot_user';
		$tableconversation    = $wpdb->prefix.'wowbot_Conversation';
		
		$tableuser1    = $wpdb->prefix.'wpbot_user';
		$tableconversation1    = $wpdb->prefix.'wpbot_Conversation';

		$table    = $wpdb->prefix.'wpbot_failed_response';
		
		if(isset($_GET['page']) && $_GET['page']=='woowbot_cs_menu_page' && isset($_GET['act']) && $_GET['act']=='delete'){
			$userid = sanitize_text_field( $_GET['userid'] );
			$wpdb->delete(
				"$tableuser",
				array( 'id' => $userid ),
				array( '%d' )
			);
			$wpdb->delete(
				"$tableconversation",
				array( 'user_id' => $userid ),
				array( '%d' )
			);
			wp_redirect(admin_url( 'admin.php?page=woowbot_cs_menu_page&msg=success'));exit;
		}

		if(isset($_GET['page']) && $_GET['page']=='wbcs-botsessions-notansweredpage' && isset($_GET['act']) && $_GET['act']=='delete'){
			$userid = sanitize_text_field( $_GET['id'] );
			$wpdb->delete(
				"$table",
				array( 'id' => $userid ),
				array( '%d' )
			);
			wp_redirect(admin_url( 'admin.php?page=wbcs-botsessions-notansweredpage&msg=success'));exit;
		}
		
		if(isset($_POST['wowbot_session_remove']) && !empty($_POST['sessions'])){
			
			$userids = qcpdcs_recursive_sanitize_text_field( $_POST['sessions'] );
			foreach($userids as $userid){
				$wpdb->delete(
					"$tableuser",
					array( 'id' => $userid ),
					array( '%d' )
				);
				$wpdb->delete(
					"$tableconversation",
					array( 'user_id' => $userid ),
					array( '%d' )
				);
			}
			wp_redirect(admin_url( 'admin.php?page=woowbot_cs_menu_page&msg=success'));exit;
			
		}
		
		if(isset($_GET['page']) && $_GET['page']=='wbcs-botsessions-page' && isset($_GET['act']) && $_GET['act']=='delete'){
			$userid = sanitize_text_field( $_GET['userid'] );
			$wpdb->delete(
				"$tableuser1",
				array( 'id' => $userid ),
				array( '%d' )
			);
			$wpdb->delete(
				"$tableconversation1",
				array( 'user_id' => $userid ),
				array( '%d' )
			);
			wp_redirect(admin_url( 'admin.php?page=wbcs-botsessions-page&msg=success'));exit;
		}
		
		if(isset($_POST['wpbot_session_remove']) && !empty($_POST['sessions'])){
			
			if(isset($_POST['wpbot_session_delete'])){
				$userids = qcpdcs_recursive_sanitize_text_field( $_POST['sessions'] );
				foreach($userids as $userid){
					$wpdb->delete(
						"$tableuser1",
						array( 'id' => $userid ),
						array( '%d' )
					);
					$wpdb->delete(
						"$tableconversation1",
						array( 'user_id' => $userid ),
						array( '%d' )
					);
				}
				wp_redirect(admin_url( 'admin.php?page=wbcs-botsessions-page&msg=success'));exit;
			}

			
		}
		
	}


	function qcpdcs_order_menu_submenu(){
		global $submenu;
		
		if(!qcpdcs_is_wpbot_active() && !qcpdcs_is_kbxwpbot_active() ){
			unset($submenu['wbcs-botsessions-page'][0]);
		}
		
		
		
		return $submenu;
	}
	add_filter( 'custom_menu_order', 'qcpdcs_order_menu_submenu', 1 );

	add_action('admin_footer', 'qcpdcs_admin_footer_content');
	function qcpdcs_admin_footer_content(){
		if((isset($_GET['page']) && $_GET['page']=='wbcs-botsessions-page') || (isset($_GET['page']) && $_GET['page']=='woowbot_cs_menu_page')){
		?>
		<div id="wpcsmyModal" class="wpcsmodal">

		<!-- Modal content -->
			<div class="wpcsmodal-content">
			<span class="wpcsclose">&times;</span>
			<h2><?php echo esc_html('Send an Email to'); ?> <span id="wpcs_show_email"></span></h2>
			<div class="wpcs_form_container">
			<form id="wpcs_email_form" action="">
				<label for="fname"><?php echo esc_html('Subject'); ?></label>
				<input type="text" class="wpcs_text_field" id="wpcs_email_subject" name="wpcs_email_subject" placeholder="Subject.." required>
				
				<label for="lname"><?php echo esc_html('Your Message'); ?></label>
				<textarea id="wpcs_email_message" class="wpcs_text_field" name="wpcs_email_message" placeholder="" style="height:200px" required></textarea>
				<input type="hidden" id="wpcs_to_email_address" value="" />
				<input type="submit" class="wpcs_submit_field" id="wpcs_email_submit" value="Submit">
				<span id="wpcs_email_loading" style=" display: none;"><img style="width:20px;" src="<?php echo esc_url(QCPDCS_WPCHATBOT_HISTORY_PLUGIN_URL.'images/ajax-loader.gif'); ?>"></span>
				<span id="wpcs_email_status"></span>
			</form>
			</div>
			</div>

		</div>
		<?php
		}
	}


function qcpdcs_send_email() {

		
	$subject = sanitize_text_field($_POST['data']['subject']);
	$message = sanitize_text_field($_POST['data']['message']);
	$to = sanitize_email($_POST['data']['to']);
	
	$url = get_site_url();
    $url = parse_url($url);
    $domain = $url['host'];
	$fromEmail = "wordpress@" . $domain;
	
	$body = $message;

	$headers = array();
	$headers[] = 'Content-Type: text/html; charset=UTF-8';
	$headers[] = 'From: ' . esc_html($domain) . ' <' . esc_html($fromEmail) . '>';

	$result = wp_mail($to, $subject, $body, $headers);
	if ($result) {
		$response['status'] = 'success';
		$response['message'] = 'Email has been sent successfully!';
	}else{
		$response['status'] = 'fail';
		$response['message'] = 'Unable to send email. Please contact your server administrator.';
	}
    
    ob_clean();
    echo json_encode($response);
    die();

}

add_action( 'wp_ajax_wpcs_send_email',        'qcpdcs_send_email' );
add_action( 'wp_ajax_nopriv_wpcs_send_email', 'qcpdcs_send_email' );



	function qcpdcs_is_kbxwpbot_active(){

		if ( defined( 'KBX_WP_CHATBOT' ) && (KBX_WP_CHATBOT == '1') ) {
			return true;
		}else{
			return false;
		}
	}

	if(!function_exists('qcpdcs_chatbot_conversation_save')){
		function qcpdcs_chatbot_conversation_save() {
			
			check_ajax_referer( 'qcsecretbotnonceval123qc', 'security' );
			global $wpdb;

			$tableuser    = $wpdb->prefix.'wpbot_user';
			$tableconversation    = $wpdb->prefix.'wpbot_Conversation';
			
			$conversation = qc_wpbot_input_validation($_POST['conversation']);
			$email = sanitize_email($_POST['email']);
			$phone = sanitize_text_field($_POST['phone']);
			$name = sanitize_text_field($_POST['name']);
			$session_id = sanitize_text_field($_POST['session_id']);
			$wpuser_id = sanitize_text_field($_POST['user_id']);
			
			
			$response = array();
			$response['status'] = 'success';
			
			$user_exists = $wpdb->get_row("select * from $tableuser where 1 and session_id = '".$session_id."'");
			if(empty($user_exists)){
			
				$interaction = (int)substr_count($conversation, "wp-chat-user-msg");
				if( $interaction == 0 ){
					$interaction = (int)substr_count($conversation, "woo-chat-user-msg");
				}

				$wpdb->insert(
					$tableuser,
					array(
						'date'  => current_time( 'mysql' ),
						'name'   => $name,
						'email'   => $email,
						'phone'   => $phone,
						'session_id'   => $session_id,
						'interaction'	=> $interaction,
						'user_id'		=> $wpuser_id
					)
				);

				
				$user_id = $wpdb->insert_id;
				$wpdb->insert(
					$tableconversation,
					array(
						'user_id'   => $user_id,
						'conversation'   => $conversation,
						'interaction'   => $interaction
					)
				);

			}else{

				$interaction = (int)substr_count($conversation, "wp-chat-user-msg");
				if( $interaction == 0 ){
					$interaction = (int)substr_count($conversation, "woo-chat-user-msg");
				}

				$user_id = $user_exists->id;
				$wpdb->update(
					$tableuser,
					array(
						'date'  => current_time( 'mysql' ),
						'name'=>$name,
						'email' => $email,
						'phone' => $phone,
						'interaction'	=> $interaction,
						'user_id'		=> $wpuser_id
					),
					array('id'=>$user_id),
					array(
						'%s',
						'%s',
						'%s',
						'%s',
						'%d',
						'%d',
					),
					array('%d')
				);
				$wpdb->update(
					$tableconversation,
					array(
						'conversation' => $conversation,
						'interaction' => $interaction,
					),
					array('user_id'=>$user_id),
					array(
						'%s',
						'%d',
					),
					array('%d')
				);
				
			}


			echo json_encode($response);

			die();
		}
	}
	add_action( 'wp_ajax_qcld_wb_chatbot_conversation_save', 'qcpdcs_chatbot_conversation_save' );
	add_action( 'wp_ajax_nopriv_qcld_wb_chatbot_conversation_save', 'qcpdcs_chatbot_conversation_save' );
	function qcpdcs_chatbot_session_date_filter(){
		global $wpdb;
		$tableuser    = $wpdb->prefix.'wpbot_user';
		$start_date = sanitize_text_field($_POST['start_date']);
		$end_date = sanitize_text_field($_POST['end_date']);
		$sql = "SELECT * FROM $tableuser WHERE date between '". $start_date ."' AND '". $end_date ."'";
		$result = $wpdb->get_results($sql);
		echo json_encode($result);
		wp_die();

	}
	add_action( 'wp_ajax_qcld_chatbot_session_date_filter', 'qcpdcs_chatbot_session_date_filter' );
	add_action( 'wp_ajax_nopriv_qcld_chatbot_session_date_filter', 'qcpdcs_chatbot_session_date_filter' );

	/**
	 * Recursive sanitation for an array
	 * 
	 * @param $array
	 *
	 * @return array
	 */
	function qcpdcs_recursive_sanitize_text_field($array) {
		foreach ( $array as $key => &$value ) {
			if ( is_array( $value ) ) {
				$value = qcpdcs_recursive_sanitize_text_field($value);
			} else {
				$value = sanitize_text_field( $value );
			}
		}

		return $array;
	}

}


