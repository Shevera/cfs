<?php
/**
 *
 * Class for subscribe
 * @author Daniel Söderström <info@dcweb.nu>
 *
 */

// If this file is called directly, abort.
if ( !defined( 'WPINC' ) )
	die();

if( class_exists( 'CFS_Contact_Form' ) ) {
	$cfs_contact_form = new CFS_Contact_Form;
}

class CFS_Contact_Form {

	private $options; // holds the values to be used in the setting page

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		// only in admin mode
		if( is_admin() ) {

			add_action( 'admin_menu', array( $this, 'add_plugin_pages' ) );
			add_action('wp_ajax_setting_save', array( $this, 'setting_save_action_function' ) );

			$this->options['cfs_to_whom'] = get_option('cfs_to_whom');
			$this->options['cfs_from_whom'] = get_option('cfs_from_whom');
			$this->options['cfs_subject'] = get_option('cfs_subject');
			$this->options['cfs_message'] = get_option('cfs_message');

		}

		add_action('wp_ajax_contact_form_save_data', array( $this, 'contact_form_save_data_function' ) );
		//[contactform formId="10"]
		add_shortcode( 'contactform', array( $this, 'shortcode_function' ) );

	}

	/**
	 * Display form in frontend by shortcode
	 *
	 */
	public function shortcode_function($atts){
		$repeatable_fields = false;
		$post_id = $atts['formid'];

		if($post_id){
			$repeatable_fields = get_post_meta($post_id, 'repeatable_fields', true);
		}

		$html = '';
		$html .= '<h3>'.__("Contact form" , "cfs_textdomain").'</h3>';
		$html .= '<form class="contact_form">';
		foreach ( $repeatable_fields as $field ) {
			$html .= '<label>'.$field["name"];
			$html .= '<input type="text" class="form_field" name="'.$field["name"].'" />';
			$html .= '</label>';
		}
		$html .= '<input type="submit" class="form_submit" value="'.__("Send" , "cfs_textdomain").'"/>';
		$html .= '</form>';
		$html .= '<div class="hidden" id="cfs_info"></div>';

		echo $html;


	}

	/**
	 * Callback saving data from frontend contact dorm to DB
	 *
	 */
	public function contact_form_save_data_function(){
		global $wpdb;

		if( isset($_POST['data']) ){

			$data = $_POST['data'];

			$table_name = $wpdb->prefix . 'cfs';

			$wpdb->insert(
				$table_name,
				array(
					'data' => $data
				),
				array(
					'%s'
				)
			);
			echo __('success' , 'cfs_textdomain');
			die();
		}else{
			echo __('error' , 'cfs_textdomain');
			die();
		}
	}

	/**
	 * Callback saving setting data from backend
	 *
	 */
	public function setting_save_action_function(){

		if( isset($_POST['cfs_to_whom']) ){
			update_option( 'cfs_to_whom', $_POST['cfs_to_whom']);
		}
		if(isset($_POST['cfs_to_whom'])){
			update_option( 'cfs_from_whom', $_POST['cfs_from_whom']);
		}
		if(isset($_POST['cfs_subject'])){
			update_option( 'cfs_subject', $_POST['cfs_subject']);
		}
		if(isset($_POST['cfs_message'])){
			update_option( 'cfs_message', $_POST['cfs_message']);
		}
		echo __('success' , 'cfs_textdomain');
		die();

	}

	/**
	 * Create plugin submenu page
	 *
	 */
	public function add_plugin_pages(){
		add_submenu_page('edit.php?post_type=cfs', __('Settings','cfs_textdomain'), __('Settings','cfs_textdomain'), 'manage_options', 'contact-menu-slug',  array( $this, 'contact_form_page') );
	}

	/**
	 * Display plugin setting pages
	 *
	 */
	public function contact_form_page(){
		global $wpdb;

		$table_name = $wpdb->prefix . 'cfs';

		$sql = "SELECT `id`, `data` FROM $table_name";
		$lists = $wpdb->get_results($sql, ARRAY_A);

		?>

		<div class="wrapp">
			<ul class="nav nav-tabs">
				<li class="active"><a data-toggle="tab" href="#contact_form"><?php _e('Contact form' , 'cfs_textdomain'); ?></a></li>
				<li><a data-toggle="tab" href="#settings"><?php _e('Settings' , 'cfs_textdomain'); ?></a></li>
			</ul>

			<div class="tab-content">
				<div id="contact_form" class="tab-pane fade in active">
					<h3><?php _e('Info about subscribers' , 'cfs_textdomain'); ?></h3>
                    <?php if($lists): ?>
					<table>
						<tr>
							<th><?php _e('id' , 'cfs_textdomain'); ?></th>
							<th><?php _e('field name' , 'cfs_textdomain'); ?></th>
							<th><?php _e('field value' , 'cfs_textdomain'); ?></th>
						</tr>
						<?php foreach ($lists as $key=>$list):?>
						<tr>
							<td><?php echo $key + 1 ; ?></td>
							<td>
								<?php
								$output = array();
								$names = $list['data'];
								parse_str($names, $output);
								?>
								<?php foreach ($output as $name_item => $item): ?>
									<?php echo $name_item . "<br>";?>
								<?php endforeach; ?>
							</td>
							<td>
								<?php foreach ($output as $name_item => $item): ?>
									<?php if($item){echo $item . "<br>";}else{echo ' - '. "<br>";} ?>
								<?php endforeach; ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</table>
			        <?php endif; ?>
				</div>
				<div id="settings" class="tab-pane fade">
					<h3><?php _e('Contact form settings' , 'cfs_textdomain'); ?></h3>
					<form class="contact_form_setting">
						<label for="cfs_to_whom"><?php _e('to whom' , 'cfs_textdomain');?></label>
						<input id="cfs_to_whom" type="text" value="<?php echo $this->options['cfs_to_whom']; ?>">
						</br>
						<label for="cfs_from_whom"><?php _e('from whom' , 'cfs_textdomain');?></label>
						<input id="cfs_from_whom" type="text" value="<?php echo $this->options['cfs_from_whom']; ?>">
						</br>
						<label for="cfs_subject"><?php _e('subject' , 'cfs_textdomain');?></label>
						<input id="cfs_subject" type="text" value="<?php echo $this->options['cfs_subject']; ?>">
						</br>
						<label for="cfs_message"><?php _e('message' , 'cfs_textdomain');?></label>
						<textarea id="cfs_message"><?php echo $this->options['cfs_message']; ?></textarea>
						</br>
						<input type="submit" value="<?php _e('Save' , 'cfs_textdomain');?>">
					</form>
					<div class="hidden" id="cfs_info"></div>
				</div>

			</div>
		</div>
		<?php
	}

}

?>
