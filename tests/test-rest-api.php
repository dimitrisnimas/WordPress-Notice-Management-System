<?php
/**
 * REST API integration tests.
 */

require_once __DIR__ . '/class-anm-test-case.php';

class ANM_REST_API_Test extends ANM_Test_Case {

	public function set_up() {
		parent::set_up();

		global $wp_rest_server;

		$wp_rest_server = new WP_REST_Server();
		do_action( 'rest_api_init' );
	}

	public function test_notices_endpoint_requires_an_api_key() {
		$response = rest_do_request( new WP_REST_Request( 'GET', '/anm/v1/notices' ) );

		$this->assertSame( 401, $response->get_status() );
		$this->assertSame( 'no_api_key', $response->get_data()['code'] );
	}

	public function test_client_receives_only_its_assigned_notices() {
		$client_id       = $this->create_client();
		$other_client_id = $this->create_client(
			array(
				'site_name' => 'Other Client',
				'site_url'  => 'https://other.example',
			)
		);
		$notice_id       = $this->create_notice();
		$other_notice_id = $this->create_notice( array( 'title' => 'Other notice' ) );

		ANM_Database::assign_notice_to_clients( $notice_id, array( $client_id ) );
		ANM_Database::assign_notice_to_clients( $other_notice_id, array( $other_client_id ) );

		$request = $this->authenticated_request( 'GET', '/anm/v1/notices', $client_id );
		$request->set_param( 'user_id', 12 );
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( 1, $data['count'] );
		$this->assertSame( $notice_id, (int) $data['notices'][0]['id'] );
	}

	public function test_client_cannot_dismiss_an_unassigned_notice() {
		$client_id = $this->create_client();
		$notice_id = $this->create_notice();
		$request   = $this->authenticated_request( 'POST', '/anm/v1/dismiss/' . $notice_id, $client_id );
		$request->set_body_params( array( 'user_id' => 9 ) );

		$response = rest_do_request( $request );

		$this->assertSame( 404, $response->get_status() );
		$this->assertSame( 'notice_not_found', $response->get_data()['code'] );
	}

	public function test_dismissed_notice_is_removed_from_the_user_feed() {
		$client_id = $this->create_client();
		$notice_id = $this->create_notice();
		ANM_Database::assign_notice_to_clients( $notice_id, array( $client_id ) );

		$dismiss_request = $this->authenticated_request( 'POST', '/anm/v1/dismiss/' . $notice_id, $client_id );
		$dismiss_request->set_body_params( array( 'user_id' => 24 ) );
		$dismiss_response = rest_do_request( $dismiss_request );

		$feed_request = $this->authenticated_request( 'GET', '/anm/v1/notices', $client_id );
		$feed_request->set_param( 'user_id', 24 );
		$feed_response = rest_do_request( $feed_request );

		$this->assertSame( 200, $dismiss_response->get_status() );
		$this->assertSame( 0, $feed_response->get_data()['count'] );
	}

	private function authenticated_request( $method, $route, $client_id ) {
		$request = new WP_REST_Request( $method, $route );
		$request->set_header( 'X-ANM-API-Key', ANM_Database::get_client( $client_id )->api_key );

		return $request;
	}
}
