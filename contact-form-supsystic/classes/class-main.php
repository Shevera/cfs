<?php
/**
 *
 * CFS Main class
 *
 */

// If this file is called directly, abort.
if ( !defined( 'WPINC' ) )
	die();

class CFS_Main {

	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// load public css
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );

		// load public scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// load admin css
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );

		// load admin scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Create contact form post type
		add_action( 'init', array( $this, 'register_post_type'), 99 );

		//Create meta box for post type
		add_action('admin_init', array( $this, 'add_meta_boxes'), 1);

		//Save meta box for post type
		add_action('save_post', array( $this, 'repeatable_meta_box_save'));

		//add shortcode columns
		add_action("manage_cfs_posts_custom_column",  array( $this, 'cfs_columns_content') , 10 , 2);
		add_filter("manage_edit-cfs_columns", array( $this, 'cfs_columns_head'));

	}

	/**
	 * Add shortcode column
	 *
	 */
	public function cfs_columns_head( $columns ) {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Title' ),
			'shortcode' => __( 'Shortcode' ),
			'date' => __( 'Date' )
		);

		return $columns;
	}

	/**
	 * Show shortcode column content
	 *
	 */
	public function cfs_columns_content($column_name , $post_id) {
		if($column_name == 'shortcode'){
			echo '[contactform formId="'.$post_id.'"]';
		}
	}

	/**
	 * Add meta box to contact form post type
	 *
	 */
	public function add_meta_boxes() {
		add_meta_box( 'repeatable-fields', 'Form field' , array( $this, 'repeatable_meta_box_display'), 'cfs', 'normal', 'high');
	}

	/**
	 * Display repeatable meta box in cfs post type
	 *
	 */
	public	function repeatable_meta_box_display() {
		global $post;
		$repeatable_fields = get_post_meta($post->ID, 'repeatable_fields', true);
		wp_nonce_field( 'repeatable_meta_box_nonce', 'repeatable_meta_box_nonce' );
		?>

		<table id="repeatable-fieldset-one" width="100%">
			<thead>
			<tr>
				<th width="2%"></th>
				<th width="30%"><?php _e('Field name' , 'cfs_textdomain');?></th>
				<th width="2%"></th>
			</tr>
			</thead>
			<tbody>
			<?php
			if ( $repeatable_fields ) :
				foreach ( $repeatable_fields as $field ) {
					?>
					<tr>
						<td><a class="button remove-row" href="#">-</a></td>
						<td><input type="text" class="widefat" name="name[]" value="<?php if($field['name'] != '') echo esc_attr( $field['name'] ); ?>" /></td>

						<td><a class="sort">|||</a></td>

					</tr>
					<?php
				}
			else :
				// show a blank one
				?>
				<tr>
					<td><a class="button remove-row" href="#">-</a></td>
					<td><input type="text" class="widefat" name="name[]" /></td>

					<td><a class="sort">|||</a></td>

				</tr>
			<?php endif; ?>

			<!-- empty hidden one for jQuery -->
			<tr class="empty-row screen-reader-text">
				<td><a class="button remove-row" href="#">-</a></td>
				<td><input type="text" class="widefat" name="name[]" /></td>

				<td><a class="sort">|||</a></td>

			</tr>
			</tbody>
		</table>

		<p><a id="add-row" class="button" href="#">Add another</a>
			<input type="submit" class="metabox_submit" value="Save" />
		</p>

		<?php
	}

	/**
	 * Save repeatable meta box in cfs post type
	 *
	 */
	public function repeatable_meta_box_save($post_id) {
		if ( ! isset( $_POST['repeatable_meta_box_nonce'] ) ||
		     ! wp_verify_nonce( $_POST['repeatable_meta_box_nonce'], 'repeatable_meta_box_nonce' ) )
			return;
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			return;
		if (!current_user_can('edit_post', $post_id))
			return;
		$old = get_post_meta($post_id, 'repeatable_fields', true);
		$new = array();
		$names = $_POST['name'];
		$count = count( $names );
		for ( $i = 0; $i < $count; $i++ ) {
			if ( $names[$i] != '' ) :
				$new[$i]['name'] = stripslashes( strip_tags( $names[$i] ) );
			endif;
		}
		if ( !empty( $new ) && $new != $old )
			update_post_meta( $post_id, 'repeatable_fields', $new );
		elseif ( empty($new) && $old )
			delete_post_meta( $post_id, 'repeatable_fields', $old );
	}


	/**
	 * Single instance of this class.
	 *
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}


	/**
	 * Fired when the plugin is activated.
	 *
	 */
	public static function activate(  ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'cfs';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  data varchar(255) DEFAULT '' NOT NULL,
		  PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		update_option( 'cfs_to_whom', '');
		update_option( 'cfs_from_whom', '');
		update_option( 'cfs_subject', '');
		update_option( 'cfs_message', '');


	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 */
	public static function deactivate(  ) {

	}

	/**
	 * Fired when the plugin is uninstall.
	 *
	 */
	public static function uninstall(  ) {

		global $wpdb;
		$table_name = $wpdb->prefix . 'cfs';
		$sql = "DROP TABLE IF EXISTS $table_name";
		$wpdb->query($sql);

		delete_option( 'cfs_to_whom');
		delete_option( 'cfs_from_whom');
		delete_option( 'cfs_subject');
		delete_option( 'cfs_message');
	}
	/**
	 * Load the plugin text domain for translation
	 *
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'cfs_textdomain', false, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );
	}

	/**
	 * Register and enqueue public style sheet
	 *
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 'cfs-style', CFS_PLUGIN_URL . '/css/cfs-style.css', array() );

	}

	/**
	 * Register and enqueue admin style sheet
	 *
	 */
	public function enqueue_admin_styles() {
		wp_enqueue_style( 'cfs-admin-style', CFS_PLUGIN_URL . '/css/cfs-admin-style.css', array() );

		wp_register_style( 'bootstrap-min-css', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
		wp_enqueue_style('bootstrap-min-css');

		wp_register_style( 'bootstrap-theme-min-css', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css' );
		wp_enqueue_style('bootstrap-theme-min-css');
	}

	/**
	 * Register and enqueue admin scripts
	 *
	 */
	public function enqueue_admin_scripts() {

		wp_register_script( 'back-end-script', CFS_PLUGIN_URL . '/js/admin-script.js', array( 'jquery' ), false, true );

		wp_enqueue_script( 'back-end-script' );

		wp_localize_script( 'back-end-script', 'ajax_object', array(
				'ajaxurl'     => admin_url( 'admin-ajax.php' ),
				'ajax_nonce'  => wp_create_nonce('ajax_nonce')
			)
		);

		wp_register_script( 'bootstrap-js', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', null, null, false );
		wp_enqueue_script('bootstrap-js');
	}

	/**
	 * Register and enqueue public scripts
	 *
	 */
	public function enqueue_scripts() {

		wp_register_script( 'front-end-script', CFS_PLUGIN_URL . '/js/script.js', array( 'jquery' ), false, true );

		wp_enqueue_script( 'front-end-script' );

		wp_localize_script( 'front-end-script', 'ajax_object', array(
				'ajaxurl'     => admin_url( 'admin-ajax.php' ),
				'ajax_nonce'  => wp_create_nonce('ajax_nonce')
			)
		);

	}

	/**
	 * Register custom post type for contact forms
	 *
	 */
	public function register_post_type(){

		$labels = array(
			'name' => __( 'Contact form', 'stc_textdomain' ),
			'singular_name' => __( 'Contact form', 'stc_textdomain' ),
			'add_new' => __( 'Add new contact form', 'stc_textdomain' ),
			'add_new_item' => __( 'Add new contact form', 'stc_textdomain' ),
			'edit_item' => __( 'Edit contact form', 'stc_textdomain' ),
			'new_item' => __( 'New contact form', 'stc_textdomain' ),
			'view_item' => __( 'Show contact form', 'stc_textdomain' ),
			'search_items' => __( 'Search contact form', 'stc_textdomain' ),
			'not_found' => __( 'Not found', 'stc_textdomain' ),
			'not_found_in_trash' => __( 'Nothing found in trash', 'stc_textdomain' ),
			'menu_name' => __( 'Сontact form', 'stc_textdomain' ),
		);

		$args = array(
			'labels' => $labels,
			'hierarchical' => true,
			'supports' => array( 'title' ),
			'public' => false,
			'menu_icon' => 'dashicons-groups',
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_nav_menus' => true,
			'publicly_queryable' => false,
			'exclude_from_search' => true,
			'has_archive' => false,
			'query_var' => true,
			'can_export' => true,
			'rewrite' => true,
			'capability_type' => 'post',
		);

		register_post_type( 'cfs', $args );

	}

}



