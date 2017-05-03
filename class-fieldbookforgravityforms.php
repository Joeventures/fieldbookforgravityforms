<?php

if( class_exists("GFForms")) {
	GFForms::include_feed_addon_framework();

	class FieldBookForGForms extends GFFeedAddOn {
		protected $_version = '1.0';
		protected $_min_gravityforms_version = '2.2';
		protected $_slug = 'gravityfield';
		protected $_path = 'fieldbookforgravityforms/fieldbookforgravityforms.php';
		protected $_full_path = __FILE__;
		protected $_title = 'FieldBook for Gravity Forms';
		protected $_short_title = 'FieldBook';

		private static $_instance = null;

		// Fieldbook API Variables
		private $api_key;
		private $api_secret;
		private $book_id;

		private $all_tables;

		public function init() {
			parent::init();
			$this->api_key    = $this->get_plugin_setting( 'api_key' );
			$this->api_secret = $this->get_plugin_setting( 'api_secret' );
			$this->book_id    = $this->book_url_to_id();
			$this->all_tables = $this->get_tables();
		}

		/**
		 * Get an instance of this class.
		 *
		 * @return FieldBookForGForms
		 */
		public static function get_instance() {
			if ( self::$_instance == null ) {
				self::$_instance = new FieldBookForGForms();
			}
			return self::$_instance;
		}

		/**
		 * Define the plugin settings
		 *
		 * @return array
		 */
		public function plugin_settings_fields() {
			return array(
				array(
					'title'  => 'FieldBook API Settings',
					"fields" => array(
						array(
							'name'       => 'api_key',
							'label'      => 'API Key Username',
							'type'       => 'text',
							'input_type' => 'text',
							'class'      => 'medium',
						),
						array(
							'name'       => 'api_secret',
							'label'      => 'API Key Password',
							'type'       => 'text',
							'input_type' => 'text',
							'class'      => 'medium',
						),
						array(
							'name'       => 'book_id',
							'label'      => 'Base API URL',
							'type'       => 'text',
							'input_type' => 'text',
							'class'      => 'medium',
						),
					)
				),
			);
		}

		/**
		 * Accept a book URL or book ID
		 *
		 * @param $book string
		 *
		 * @return string
		 */
		public function book_url_to_id() {
			$book = $this->get_plugin_setting( 'book_id' );
			if ( filter_var( $book, FILTER_VALIDATE_URL ) ) {
				$book = explode( '/', $book );
				$book = end( $book );
			}

			return $book;
		}

		/**
		 * Get an array of tables in the Fieldbook Book
		 *
		 * @return array
		 */
		function get_tables() {
			$fb_connect = array(
				'api_key'    => $this->api_key,
				'api_secret' => $this->api_secret,
				'book_id'    => $this->book_id
			);
			$fb         = new PhieldBook( $fb_connect );
			$tables     = $fb->get();

			return $tables;
		}

	}
}