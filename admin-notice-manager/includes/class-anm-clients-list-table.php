<?php
/**
 * Clients list table.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ANM_Clients_List_Table extends WP_List_Table {

	private $search;

	public function __construct( $search = '' ) {
		parent::__construct(
			array(
				'singular' => 'client',
				'plural'   => 'clients',
				'ajax'     => false,
			)
		);

		$this->search = $search;
	}

	public function get_columns() {
		return array(
			'site_name'      => __( 'Site Name', 'admin-notice-manager' ),
			'site_url'       => __( 'Site URL', 'admin-notice-manager' ),
			'status'         => __( 'Status', 'admin-notice-manager' ),
			'last_connected' => __( 'Last Connected', 'admin-notice-manager' ),
			'api_key'        => __( 'API Key', 'admin-notice-manager' ),
		);
	}

	public function no_items() {
		echo $this->search
			? esc_html__( 'No clients match your search.', 'admin-notice-manager' )
			: esc_html__( 'No clients found.', 'admin-notice-manager' );
	}

	public function prepare_items() {
		$per_page     = 20;
		$current_page = $this->get_pagenum();
		$total_items  = ANM_Database::count_clients( $this->search );

		$this->_column_headers = array( $this->get_columns(), array(), array() );
		$this->items           = ANM_Database::get_clients_page( $current_page, $per_page, $this->search );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => (int) ceil( $total_items / $per_page ),
			)
		);
	}

	public function column_site_name( $client ) {
		$edit_url   = add_query_arg(
			array(
				'page'   => 'anm-clients',
				'action' => 'edit',
				'id'     => $client->id,
			),
			admin_url( 'admin.php' )
		);
		$delete_url = wp_nonce_url(
			add_query_arg(
				array(
					'action'    => 'anm_delete_client',
					'client_id' => $client->id,
				),
				admin_url( 'admin-post.php' )
			),
			'anm_delete_client_' . $client->id
		);

		return sprintf(
			'<strong><a href="%1$s">%2$s</a></strong>%3$s',
			esc_url( $edit_url ),
			esc_html( $client->site_name ),
			$this->row_actions(
				array(
					'edit'   => '<a href="' . esc_url( $edit_url ) . '">' . esc_html__( 'Edit', 'admin-notice-manager' ) . '</a>',
					'delete' => '<a href="' . esc_url( $delete_url ) . '" class="submitdelete anm-confirm-delete">' . esc_html__( 'Delete', 'admin-notice-manager' ) . '</a>',
				)
			)
		);
	}

	public function column_site_url( $client ) {
		return sprintf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
			esc_url( $client->site_url ),
			esc_html( $client->site_url )
		);
	}

	public function column_status( $client ) {
		return sprintf(
			'<span class="anm-status anm-status-%1$s">%2$s</span>',
			esc_attr( $client->status ),
			esc_html( ucfirst( $client->status ) )
		);
	}

	public function column_last_connected( $client ) {
		if ( ! $client->last_connected ) {
			return '<em>' . esc_html__( 'Never', 'admin-notice-manager' ) . '</em>';
		}

		$last_connected = date_create_immutable_from_format( 'Y-m-d H:i:s', $client->last_connected, wp_timezone() );

		if ( ! $last_connected ) {
			return '<em>' . esc_html__( 'Unknown', 'admin-notice-manager' ) . '</em>';
		}

		return sprintf(
			/* translators: %s: human-readable time difference. */
			esc_html__( '%s ago', 'admin-notice-manager' ),
			esc_html( human_time_diff( $last_connected->getTimestamp(), current_datetime()->getTimestamp() ) )
		);
	}

	public function column_api_key( $client ) {
		return sprintf(
			'<code class="anm-api-key-preview">%1$s</code> <button type="button" class="button button-small anm-copy-api-key" data-api-key="%2$s">%3$s</button>',
			esc_html( substr( $client->api_key, 0, 12 ) . '…' ),
			esc_attr( $client->api_key ),
			esc_html__( 'Copy', 'admin-notice-manager' )
		);
	}
}
