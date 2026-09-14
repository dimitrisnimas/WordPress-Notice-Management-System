<?php
/**
 * Notices list table.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ANM_Notices_List_Table extends WP_List_Table {

	private $search;

	public function __construct( $search = '' ) {
		parent::__construct(
			array(
				'singular' => 'notice',
				'plural'   => 'notices',
				'ajax'     => false,
			)
		);

		$this->search = $search;
	}

	public function get_columns() {
		return array(
			'title'      => __( 'Title', 'admin-notice-manager' ),
			'type'       => __( 'Type', 'admin-notice-manager' ),
			'status'     => __( 'Status', 'admin-notice-manager' ),
			'clients'    => __( 'Clients', 'admin-notice-manager' ),
			'expiration' => __( 'Expiration', 'admin-notice-manager' ),
			'created'    => __( 'Created', 'admin-notice-manager' ),
		);
	}

	public function no_items() {
		echo $this->search
			? esc_html__( 'No notices match your search.', 'admin-notice-manager' )
			: esc_html__( 'No notices found.', 'admin-notice-manager' );
	}

	public function prepare_items() {
		$per_page     = 20;
		$current_page = $this->get_pagenum();
		$total_items  = ANM_Database::count_notices( $this->search );

		$this->_column_headers = array( $this->get_columns(), array(), array() );
		$this->items           = ANM_Database::get_notices_page( $current_page, $per_page, $this->search );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => (int) ceil( $total_items / $per_page ),
			)
		);
	}

	public function column_title( $notice ) {
		$edit_url   = add_query_arg(
			array(
				'page'   => 'anm-notices',
				'action' => 'edit',
				'id'     => $notice->id,
			),
			admin_url( 'admin.php' )
		);
		$delete_url = wp_nonce_url(
			add_query_arg(
				array(
					'action'    => 'anm_delete_notice',
					'notice_id' => $notice->id,
				),
				admin_url( 'admin-post.php' )
			),
			'anm_delete_notice_' . $notice->id
		);

		return sprintf(
			'<strong><a href="%1$s">%2$s</a></strong>%3$s',
			esc_url( $edit_url ),
			esc_html( $notice->title ),
			$this->row_actions(
				array(
					'edit'   => '<a href="' . esc_url( $edit_url ) . '">' . esc_html__( 'Edit', 'admin-notice-manager' ) . '</a>',
					'delete' => '<a href="' . esc_url( $delete_url ) . '" class="submitdelete anm-confirm-delete">' . esc_html__( 'Delete', 'admin-notice-manager' ) . '</a>',
				)
			)
		);
	}

	public function column_type( $notice ) {
		return sprintf(
			'<span class="anm-badge anm-badge-%1$s">%2$s</span>',
			esc_attr( $notice->notice_type ),
			esc_html( ucfirst( $notice->notice_type ) )
		);
	}

	public function column_status( $notice ) {
		return sprintf(
			'<span class="anm-status anm-status-%1$s">%2$s</span>',
			esc_attr( $notice->status ),
			esc_html( ucfirst( $notice->status ) )
		);
	}

	public function column_clients( $notice ) {
		return esc_html( count( ANM_Database::get_notice_clients( $notice->id ) ) );
	}

	public function column_expiration( $notice ) {
		if ( ! $notice->expiration_date ) {
			return '<em>' . esc_html__( 'Never', 'admin-notice-manager' ) . '</em>';
		}

		return esc_html( date_i18n( get_option( 'date_format' ), strtotime( $notice->expiration_date ) ) );
	}

	public function column_created( $notice ) {
		return esc_html( date_i18n( get_option( 'date_format' ), strtotime( $notice->created_at ) ) );
	}
}
