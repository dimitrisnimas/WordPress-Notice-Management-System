<?php
/**
 * Shared integration test case.
 */

abstract class ANM_Test_Case extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		ANM_Database::create_tables();
		$this->clear_plugin_tables();
	}

	public function tear_down() {
		$this->clear_plugin_tables();
		parent::tear_down();
	}

	protected function create_client( $overrides = array() ) {
		return ANM_Database::insert_client(
			wp_parse_args(
				$overrides,
				array(
					'site_name' => 'Example Client',
					'site_url'  => 'https://client.example',
					'status'    => 'active',
				)
			)
		);
	}

	protected function create_notice( $overrides = array() ) {
		return ANM_Database::insert_notice(
			wp_parse_args(
				$overrides,
				array(
					'title'          => 'Maintenance',
					'content'        => '<p>Scheduled maintenance.</p>',
					'content_type'   => 'html',
					'notice_type'    => 'warning',
					'status'         => 'active',
					'is_dismissible' => 1,
				)
			)
		);
	}

	private function clear_plugin_tables() {
		global $wpdb;

		$tables = array(
			$wpdb->prefix . 'anm_dismissals',
			$wpdb->prefix . 'anm_notice_clients',
			$wpdb->prefix . 'anm_clients',
			$wpdb->prefix . 'anm_notices',
		);

		foreach ( $tables as $table ) {
			$wpdb->query( $wpdb->prepare( 'DELETE FROM %i', $table ) );
		}
	}
}
