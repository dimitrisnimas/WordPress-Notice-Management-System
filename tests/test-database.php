<?php
/**
 * Database integration tests.
 */

require_once __DIR__ . '/class-anm-test-case.php';

class ANM_Database_Test extends ANM_Test_Case {

	public function test_notice_assignment_and_dismissal_flow() {
		$client_id = $this->create_client();
		$notice_id = $this->create_notice();

		ANM_Database::assign_notice_to_clients( $notice_id, array( $client_id ) );

		$notices = ANM_Database::get_notices_for_client( $client_id );

		$this->assertCount( 1, $notices );
		$this->assertSame( $notice_id, (int) $notices[0]->id );
		$this->assertFalse( ANM_Database::is_notice_dismissed( $notice_id, $client_id, 42 ) );

		ANM_Database::dismiss_notice( $notice_id, $client_id, 42 );

		$this->assertTrue( ANM_Database::is_notice_dismissed( $notice_id, $client_id, 42 ) );
	}

	public function test_search_and_pagination_return_expected_records() {
		$this->create_notice( array( 'title' => 'Alpha notice' ) );
		$this->create_notice( array( 'title' => 'Beta notice' ) );

		$results = ANM_Database::get_notices_page( 1, 1, 'Alpha' );

		$this->assertSame( 1, ANM_Database::count_notices( 'Alpha' ) );
		$this->assertCount( 1, $results );
		$this->assertSame( 'Alpha notice', $results[0]->title );
	}

	public function test_rotating_a_client_key_invalidates_the_previous_key() {
		$client_id = $this->create_client();
		$old_key   = ANM_Database::get_client( $client_id )->api_key;
		$new_key   = ANM_Database::rotate_client_api_key( $client_id );

		$this->assertNotFalse( $new_key );
		$this->assertNotSame( $old_key, $new_key );
		$this->assertNull( ANM_Database::get_client_by_api_key( $old_key ) );
		$this->assertSame( $client_id, (int) ANM_Database::get_client_by_api_key( $new_key )->id );
	}

	public function test_deleting_a_client_removes_related_records() {
		global $wpdb;

		$client_id = $this->create_client();
		$notice_id = $this->create_notice();
		ANM_Database::assign_notice_to_clients( $notice_id, array( $client_id ) );
		ANM_Database::dismiss_notice( $notice_id, $client_id, 7 );

		ANM_Database::delete_client( $client_id );

		$relationship_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}anm_notice_clients WHERE client_id = %d",
				$client_id
			)
		);
		$dismissal_count    = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}anm_dismissals WHERE client_id = %d",
				$client_id
			)
		);

		$this->assertSame( '0', $relationship_count );
		$this->assertSame( '0', $dismissal_count );
	}
}
