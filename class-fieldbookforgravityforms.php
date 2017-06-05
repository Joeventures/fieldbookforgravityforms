<?php

if( class_exists("GFForms")) {
	GFForms::include_feed_addon_framework();

	class FieldbookForGForms extends GFFeedAddOn {
		public $_async_feed_processing = true;
		protected $_version = '1.1.0';
		protected $_min_gravityforms_version = '2.2';
		protected $_slug = 'gravityfield';
		protected $_path = 'fieldbookforgravityforms/fieldbookforgravityforms.php';
		protected $_full_path = __FILE__;
		protected $_title = 'Fieldbook for Gravity Forms';
		protected $_short_title = 'Fieldbook';

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
		 * @return FieldbookForGForms
		 */
		public static function get_instance() {
			if ( self::$_instance == null ) {
				self::$_instance = new FieldbookForGForms();
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
					'title'  => 'Fieldbook API Settings',
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
			$tables     = $fb->sheet_meta();

			return $tables;
		}

		/**
		 * Get the sheet id from the sheet name
		 *
		 * @param $sheet_name string name of the sheet
		 *
		 * @return string sheet_id
		 */
		function get_sheet_id($sheet_name) {
			$fb_connect = array(
				'api_key' => $this->api_key,
				'api_secret' => $this->api_secret,
				'book_id' => $this->book_id,
			);
			$fb = new PhieldBook($fb_connect);
			$all_sheets = $fb->sheet_meta();
			$result = array_search($sheet_name, array_column($all_sheets, 'slug'));
			$result = $all_sheets[$result]['id'];

			return $result;
		}

		/**
		 * Get an array of fields in a table, excluding linked fields
		 *
		 * @param $sheet_name string name of the table
		 *
		 * @return array
		 */
		function get_fields( $sheet_name ) {
			if ( ! $sheet_name ) {
				return array();
			}

			$fb_connect = array(
				'api_key'    => $this->api_key,
				'api_secret' => $this->api_secret,
				'book_id'    => $this->book_id,
				'sheet_id'   => $this->get_sheet_id($sheet_name)
			);
			$fb = new PhieldBook( $fb_connect );
			$fields = $fb->field_meta();

			return $fields;
		}

		/**
		 * Format the list of fields in the Fieldbook table into a proper field map
		 *
		 * @return array
		 */
		function map_fields( $table = null ) {
			$table     = is_null( $table ) ? $this->get_setting( 'sheet_name' ) : $table;
			$fields    = $this->get_fields( $table );
			$field_map = array();
			foreach ( $fields as $field ) {
				if($field['fieldType'] == 'formula') continue;
				$label = $field['name'] == '' ? $field['slug'] : $field['name'];
				$label .= $field['fieldType'] == 'link' ? ' (link)' : '';
				$field_map[] = array(
					'name'     => $field['slug'],
					'label'    => $label,
					'required' => false,
				);
			}
			return $field_map;
		}

		/**
		 * Format the list of fields in the Fieldbook table into [name] and [label]. Meant to use for building the Fieldbook Key Field options
		 *
		 * @return array
		 */
		function map_fb_fields_for_match() {
			$table = $this->get_setting('sheet_name');
			$fields = $this->get_fields($table);
			$field_map = array();
			foreach($fields as $field) {
				$label = $field['name'] == '' ? $field['slug'] : $field['name'];
				$field_map[] = array(
					'value' => $field['slug'],
					'label' => $label
				);
			}
			return $field_map;
		}

		/**
		 * Configures which columns should be displayed on the feed list page.
		 *
		 * @return array
		 */
		public function feed_list_columns() {
			return array(
				'sheet_name'  => esc_html__( 'Sheet Name', 'fieldbookforgravityforms' ),
				'feed_type' => esc_html__( 'Feed Type', 'fieldbookforgravityforms' ),
			);
		}

		/**
		 * Used to properly display the Table Name column in the list of feeds
		 *
		 * @param $feed
		 *
		 * @return string
		 */
		public function get_column_value_sheet_name( $feed ) {
			return ucfirst( rgars( $feed, 'meta/sheet_name' ) );
		}

		public function get_column_value_feed_type( $feed ) {
			return ucfirst( rgars( $feed, 'meta/feed_type' ) );
		}

		/**
		 * Build the fields for the feed.
		 *
		 * @return array
		 */
		public function feed_settings_fields() {
			return array(
				array(
					// SECTION 1
					'title'  => 'Fieldbook Field Settings',
					'name'   => 'tableName',
					'fields' => array(
						$this->do_field_feed_type(),
						$this->do_field_sheet_name()
					),
				),
				array(
					// SECTION 2 ( dependency: table_name selected )
					'title'      => 'Map Fields',
					'dependency' => 'sheet_name',
					'fields'     => array(
						array(
							'label' => 'Map Data Fields',
							'name' => 'field_map',
							'type' => 'field_map',
							'field_map' => $this->map_fields()
						),
						$this->do_field_map_key_fields(),
						array(
							'type' => 'feed_condition',
							'name' => 'feed_condition',
							'label' => 'Feed Condition'
						)
					),
				),
			);
		}

		/**
		 * Define the fieldbook_list field type - a drop-down box of Fieldbook Tables
		 *
		 * @param $field
		 */
		public function settings_fieldbook_list( $field ) {
			$field['type']     = 'select';
			$field['onchange'] = 'jQuery(this).parents("form").submit();';
			$html              = $this->settings_select( $field, false );
			echo $html;
		}

		function check_fieldbook_connection() {
			$fb_connect = array(
				'api_key'    => $this->api_key,
				'api_secret' => $this->api_secret,
				'book_id'    => $this->book_id
			);
			$fb = new PhieldBook($fb_connect);
			$fb->book_meta();
			if($fb->response_info['http_code'] == 200) {
				return true;
			} else {
				return false;
			}
		}

		/******************* FIELDS GO HERE *******************/
		public function do_field_feed_type() {
			$choices[] = array(
				'label'   => 'Create',
				'name'    => 'create',
				'tooltip' => 'Each form entry will create a new record',
				'value'   => 'create'
			);
			$choices[] = array(
				'label'   => 'Update',
				'name'    => 'update',
				'value'   => 'update',
				'tooltip' => 'Each form entry will update an existing record, or create a new record if a match does not exist'
			);
			return array(
				'label'      => 'Feed Type',
				'type'       => 'radio',
				'name'       => 'feed_type',
				'onchange'   => 'jQuery(this).parents("form").submit();',
				'horizontal' => true,
				'choices'    => $choices
			);
		}

		public function do_field_map_key_fields() {
			return array(
				'label' => 'Map Key Fields',
				'name' => 'map_key_fields',
				'type' => 'field_map',
				'field_map' => $this->map_fields(),
				'dependency' => array(
					'field' => 'feed_type',
					'values' => array('update')
				)
			);
		}

		public function do_field_sheet_name($label = 'Sheet Name') {

			$choices = array();

			if($this->check_fieldbook_connection()) {
				$tables    = $this->all_tables;
				$choices[] = array( 'label' => 'Select a Fieldbook Sheet', 'value' => '' );
				foreach ( $tables as $table ) {
					$choices[] = array( 'label' => $table['title'], 'value' => $table['slug'] );
				}
			}

			return array(
				'label'      => $label,
				'type'       => 'fieldbook_list',
				'name'       => 'sheet_name',
				'choices'    => $choices,
				'dependency' => 'feed_type'
			);
		}

		/***************** PROCESS FEED BEGIN *****************/
		/**
		 * What to do when a form is submitted
		 *
		 * @param $feed
		 * @param $entry
		 * @param $form
		 */
		public function process_feed($feed, $entry, $form) {
			$fields = $this->get_field_map_fields($feed, 'field_map');

			$sheet_name = $feed['meta']['sheet_name'];
			$feed_type = $feed['meta']['feed_type'];

			$params = array();
			foreach($fields as $fieldbook_field => $form_field) {
				if($form_field == '') continue;
				$params[$fieldbook_field] = $this->get_field_value($form, $entry, $form_field);
			}

			$fb_connect = array(
				'api_key' => $this->api_key,
				'api_secret' => $this->api_secret,
				'book_id' => $this->book_id,
				'sheet_title' => $sheet_name
			);
			$fb = new PhieldBook($fb_connect);
			if($feed_type == 'create') {
				$fb->create($params);
			} elseif($feed_type == 'update') {
				$search_fields = $this->get_field_map_fields($feed, 'map_key_fields');
				$find_param = array();
				foreach($search_fields as $fieldbook_field => $form_field) {
					if($form_field == '') continue;
					$find_param[$fieldbook_field] = $this->get_field_value($form, $entry, $form_field);
				}

				$fieldbook_record = $fb->search($find_param);
				if(count($fieldbook_record) > 0) {
					$update_id = $fieldbook_record[0]['id'];
					$fb->record_id = $update_id;
					$fb->update($params);
				} else {
					$fb->create($params);
				}

			}
		}

	}
}