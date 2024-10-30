<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       travisnguyen.me
 * @since      1.0.0
 *
 * @package    Message_Hub
 * @subpackage Message_Hub/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Message_Hub
 * @subpackage Message_Hub/admin
 * @author     Faba Technonogy <travisnguyen.me@gmail.com>
 */
class Message_Hub_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * An instance of this class should be passed to the run() function
		 * defined in Message_Hub_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Message_Hub_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/message-hub-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * An instance of this class should be passed to the run() function
		 * defined in Message_Hub_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Message_Hub_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/message-hub-admin.js', array( 'jquery' ), $this->version, false );
	}

}

function message_hub_register_settings() {
  add_option( 'message_hub_api_key', '');
  register_setting( 'message_hub_options_group', 'message_hub_api_key', 'message_hub_callback' );
}

add_action( 'admin_init', 'message_hub_register_settings' );

function message_hub_register_options_page() {
  add_options_page('Message Hub Settings', 'Message Hub', 'manage_options', 'message_hub', 'message_hub_options_page');
}
add_action('admin_menu', 'message_hub_register_options_page');

function message_hub_options_page()
{
  if ( ! current_user_can( 'manage_options' ) ) {
    return;
    }
    
  ?>
  <div class="wrap">
  <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
  <form method="post" action="options.php">
  <?php settings_fields( 'message_hub_options_group' ); ?>
  <h3>General</h3>
  <table>
  <tr valign="top">
  <th scope="row" style="text-align: left;min-width: 150px"><label for="message_hub_api_key">API Token</label></th>
  <td><input style="min-width: 350px" type="text" id="message_hub_api_key" name="message_hub_api_key" value="<?php echo get_option('message_hub_api_key'); ?>" /></td>
  </tr>
  </table>
  <?php  submit_button(); ?>
  </form>
  </div>
<?php
}

