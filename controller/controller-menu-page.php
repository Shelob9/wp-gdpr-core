<?php


namespace wp_gdpr\controller;

use wp_gdpr\lib\Appsaloon_Table_Builder;

class Controller_Menu_Page {

	/**
	 * Controller_Menu_Page constructor.
	 */
	public function __construct() {
		if ( ! has_action( 'init', array( $this, 'send_email' ) ) ) {
			add_action( 'init', array( $this, 'send_email' ) );
		}
	}

	/**
	 * build table in menu admin
	 */
	public function build_table_with_requests() {
		$requesting_users = $this->get_requests_from_gdpr_table();

		if ( ! is_array( $requesting_users ) ) {
			return;
		}

		$form_content = $this->get_form_content( $requesting_users );

		//map status from number to string
		$requesting_users = array_map( array( $this, 'map_request_status' ), $requesting_users );
		//add checkbox input in every element with email address
		$requesting_users = array_map( array( $this, 'map_checkboxes_send_email' ), $requesting_users );

		//show table object
		$table = new Appsaloon_Table_Builder(
			array( 'id', 'email', '', 'requested at', 'status', 'send email' ),
			$requesting_users
			, array( $form_content ) );

		//execute
		$table->print_table();
	}

	/**
	 * @return array|null|object
	 * get all records from gdpr_requests table
	 */
	public function get_requests_from_gdpr_table() {
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->prefix}gdpr_requests";

		return $wpdb->get_results( $query, ARRAY_A );
	}

	/**
	 * @param $requesting_users
	 *
	 * @return string
	 */
	public function get_form_content( $requesting_users ) {
		ob_start();
		$controller = $this;
		include_once GDPR_DIR . 'view/admin/small-form.php';

		return ob_get_clean();
	}

	/**
	 * @param $data
	 *
	 * @return mixed
	 * add checkbox element in array
	 */
	public function map_checkboxes_send_email( $data ) {

		$data['checkbox'] = $this->create_single_input_with_email( $data['email'] );

		return $data;
	}


	/**
	 *  create checkbox as delegate of gdpr_form
	 */
	public function create_single_input_with_email( $email ) {

		return '<input type="checkbox" form="gdpr_form"  name="gdpr_emails[]" value="' . $email . '">';
	}

	/**
	 * @param $data
	 *
	 * @return mixed
	 *
	 * callback to map status from int to string
	 */
	public function map_request_status( $data ) {

		switch ( $data['status'] ) {
			case 0:
				$data['status'] = 'waiting for email';
				break;
			case 1:
				$data['status'] = 'email sent';
				break;
			case 2:
				$data['status'] = 'url is visited';
				break;
		}

		return $data;
	}

	/**
	 * this function is not in use
	 */
	public function print_inputs_with_emails() {
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->prefix}gdpr_requests";

		$requesting_users = $wpdb->get_results( $query, ARRAY_A );

		foreach ( $requesting_users as $user ) {
			/**
			 * if status is 0
			 * email is not sent
			 *
			 */
			if ( $user['status'] == 0 ) {
				echo '<input hidden name="gdpr_emails[]" value="' . $user['email'] . '">';
			}
		}

	}

	/**
	 * send emails when POST request
	 */
	public function send_email() {
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_REQUEST['gdpr_emails'] ) && is_array( $_REQUEST['gdpr_emails'] ) ) {
			foreach ( $_REQUEST['gdpr_emails'] as $single_address ) {
				$single_address = sanitize_email( $single_address );
				$to             = $single_address;
				$subject        = 'Data request';
				$content        = $this->get_email_content( $single_address );

				wp_mail( $to, $subject, $content, array() );

				$this->update_gdpr_request_status( $single_address );
			}
		}
	}

	/**
	 * @param $single_adress
	 *
	 * @return string content of email
	 *
	 */
	public function get_email_content( $single_adress ) {
		ob_start();
		$url = $this->create_unique_url( $single_adress );
		include GDPR_DIR . 'view/front/email-template.php';

		return ob_get_clean();
	}

	/**
	 * @param $email_address
	 *
	 * @return string
	 * create url
	 * encode gdpr#example@email.com into base64
	 */
	public function create_unique_url( $email_address ) {
		return home_url() . '/' . base64_encode( 'gdpr#' . $email_address );
	}

	public function update_gdpr_request_status( $email ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'gdpr_requests';

		$wpdb->update( $table_name, array( 'status' => 1 ), array( 'email' => $email ) );
	}

	/**
	 * search for plugins
	 */
	public function build_table_with_plugins() {

		$plugins = get_plugins();
		$plugins = array_map( function ( $k ) {
			return array( $k['Name'] );
		}, $plugins );


		$plugins = $this->filter_plugins( $plugins );

		$table = new Appsaloon_Table_Builder(
			array( 'plugin name' ),
			$plugins
			, array() );

		$table->print_table();

	}

	/**
	 * @param array $plugins
	 *
	 * @return array
	 */
	public function filter_plugins( array $plugins ) {

		return array_filter( $plugins, function ( $data ) {
			$plugin_name = strtolower( $data[0] );
			foreach ( array( 'woocommerce', 'gdpr', 'gravity' ) as $pl ) {
				if ( strpos( $plugin_name, $pl ) !== false ) {
					return true;
				}
			}
		} );
	}
}