<?php

/**
 * Issue Viewers - This plugin stores and shows issue viewers and their view count
 * @author Dentxinho
 *
 */
class IssueViewersPlugin extends MantisPlugin {
	function register()	{
		$this->name = 'Issue Viewers';
		$this->description = 'This plugin stores and shows issue viewers and their view count';
		$this->page = '';

		$this->version = '0.1';
		$this->requires = array(
						'MantisCore' => '1.2.0',
						);

		$this->author = 'Dentxinho';
		$this->contact = 'https://github.com/Dentxinho';
		$this->url = 'https://github.com/Dentxinho';
	}

	function hooks() {
		return array(
			'EVENT_VIEW_BUG_DETAILS'	=> 'store_viewer',
			'EVENT_VIEW_BUG_EXTRA'		=> 'display_viewers'
		);
	}

	function schema() {
		$schema[] = array( "CreateTableSQL", array( plugin_table( "bug_viewer_data" ), "
						id			I	NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
						bug_id		I	NOTNULL,
						user_id		I	DEFAULT NULL,
						view_date	I	NOTNULL
					", array( "mysql" => "DEFAULT CHARSET=utf8" ) ) );
		$schema[] = array( 'CreateIndexSQL', array( 'idx_bug_id', plugin_table( 'bug_viewer_data' ), 'bug_id' ) );
		$schema[] = array( 'CreateIndexSQL', array( 'idx_user_id', plugin_table( 'bug_viewer_data' ), 'user_id' ) );
		return $schema;
	}

	function store_viewer($p_event, $bug_id) {
		if (current_user_is_anonymous() || !auth_is_user_authenticated())
			$user_id = NULL;
		else
			$user_id = current_user_get_field( "id" );

		$dbtable = plugin_table( "bug_viewer_data" );
		$dbquery = "INSERT INTO {$dbtable} (bug_id, user_id, view_date) VALUES (" . db_param() . "," . db_param() . "," . db_now() . ")";
		$dboutput = db_query_bound( $dbquery, array( $bug_id, $user_id ) );
	}

	function display_viewers( $p_event, $bug_id ) {
		$dbtable = plugin_table( "bug_viewer_data" );
		$dbquery = "SELECT IFNULL(user_id, 'anon') AS user_id, COUNT(IFNULL(user_id,'anon')) AS count, MAX(view_date) AS last_view
					FROM {$dbtable}
					WHERE bug_id = " . db_param() . "
					GROUP BY user_id
					ORDER BY MAX(view_date) DESC";

		$dboutput = db_query_bound( $dbquery, array( $bug_id ) );

		$viewers = array();
		$view_count = 0;
		$last_view = FALSE;

		if ( $dboutput->RecordCount() > 0 ) {
			$data = $dboutput->GetArray();
			$last_view = $data[0]['last_view'];

			foreach ($data as $row) {
				$viewers[$row['user_id']] = $row['count'];
				$view_count += $row['count'];
			}
		}

		$this->_print_viewers_layout( $viewers, $view_count, $last_view );

	}

	private function _print_viewers_layout( $viewers, $view_count, $last_view ) {
		echo '<a name="viewers" id="viewers" /><br />';
		collapse_open( 'viewers' );
		echo '<table class="width100" cellspacing="1">
			 	<tr>
					<td class="form-title" colspan="2">';
						collapse_icon( 'viewers' );
		echo 			plugin_lang_get( 'viewed_by' ),
			 		'</td>
				</tr>
				<tr class="row-1">
					<td class="category" width="15%">',
						plugin_lang_get( 'user_list' ),
					'</td>
					<td>';
					if ( 0 == count($viewers) ) {
						echo plugin_lang_get( 'no_views' );
					} else {
						foreach ( $viewers as $user_id => &$count ) {
							$count = ( ( $user_id != 'anon' ) ? prepare_user_name( $user_id ) : lang_get( 'anonymous' ) ) . " (" . $count . "x)";
				 		}
				 		echo implode( ', ',$viewers );
			 		}
		echo		'</td>
				</tr>
				<tr class="row-1">
					<td class="category" width="15%">',
						plugin_lang_get( 'total_views' ),
					'</td>
					<td>' . $view_count . '</td>
				</tr>
				<tr class="row-1">
					<td class="category" width="15%">',
						plugin_lang_get( 'last_view' ),
					'</td>
					<td>' . ($last_view ? date( config_get( 'normal_date_format' ), $last_view ) : ' - ') . '</td>
				</tr>
			</table>';
			collapse_closed( 'viewers' );
		echo '<table class="width100" cellspacing="1">
			 	<tr>
					<td class="form-title" colspan="2">';
						collapse_icon( 'viewers' );
		echo 			plugin_lang_get( 'viewed_by' ),
			 		'</td>
				</tr>
			</table>';
		collapse_end( 'viewers' );
	}
}
