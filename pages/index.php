<?php
html_page_top1( plugin_lang_get( 'title' ) );
html_page_top2();
echo '<link rel="stylesheet" type="text/css" href="'. plugin_file( 'view.css' ) .'"/>';

$uid=$_GET["uid"];
$iid=$_GET["iid"];
$filter_date=$_GET["date"];
$dbtable = plugin_table( "bug_viewer_data" );
echo '<center>';
echo '<h1>Issue Views</h1>';
if (!$uid=="") {
	$dbquery = "SELECT IFNULL(user_id, 'anon') AS user_id, bug_id, view_date
					FROM {$dbtable} where user_id=" .db_param().
					"ORDER BY view_date DESC LIMIT 100";
	$dboutput = db_query_bound( $dbquery,array($uid));
	echo '<br/>Filtered by User '. prepare_user_name( $uid).' - <a href="'.plugin_page( "index" ).'">Show All</a><br/><hr/>';
}else if (!$iid==""){
	$dbquery = "SELECT IFNULL(user_id, 'anon') AS user_id, bug_id, view_date
					FROM {$dbtable} where bug_id=" .db_param().
					"ORDER BY view_date DESC LIMIT 100";
	$dboutput = db_query_bound( $dbquery,array($iid));
	echo '<br/>Filtered by Issue '. string_get_bug_view_link( $iid ).' - <a href="'.plugin_page( "index" ).'">Show All</a><br/><hr/>';
}else if (!$filter_date==""){
	$dbquery = "SELECT IFNULL(user_id, 'anon') AS user_id, bug_id, view_date 
				FROM {$dbtable} where (
				view_date >= (UNIX_TIMESTAMP(FROM_UNIXTIME(" .db_param().",\"%Y-%m-%d\"))) 
				AND view_date <= (UNIX_TIMESTAMP(FROM_UNIXTIME(" .db_param().",\"%Y-%m-%d\"))) 
				) 
				ORDER BY view_date DESC LIMIT 100";
	$dboutput = db_query_bound( $dbquery,array($filter_date,$filter_date+86400));
	//date( config_get( 'normal_date_format' ), $filter_date )
	//echo config_get( 'normal_date_format' );
	echo '<br/>Filtered by date '. date(config_get( 'short_date_format' ), $filter_date ).' - <a href="'.plugin_page( "index" ).'">Show All</a><br/><hr/>';
}else{
	$dbquery = "SELECT IFNULL(user_id, 'anon') AS user_id, bug_id, view_date
					FROM {$dbtable}
					ORDER BY view_date DESC LIMIT 300";
	$dboutput = db_query_bound( $dbquery);	
}

if  (auth_get_current_user_id()==1) {
	//
}
echo '</center>';
if ( $dboutput->RecordCount() > 0 ) {
	$data = $dboutput->GetArray();
	
	echo '<table class="width=90%" align="center" cellspacing="1">';
	echo '<tr bgcolor="#FFFFFF">';
	echo '<td class="left" valign="top" colspan="8" nowrap="nowrap">';
	
	echo 'Total : '.$dboutput->RecordCount();
	echo '<br/>Admin users are not shown here unless you are admin.';
	
	echo '</span></td>';
	echo '</tr>';
	
	echo '<tr style="color: #fff; background: black; font-weight: bold;">';
	echo '<td class="left" valign="top" width ="5%" nowrap="nowrap">';
	echo '<span class="smallB">';
	echo '#';
	echo '</span></td>';
	echo '<td class="left" valign="top" width ="15%" nowrap="nowrap">';
	echo '<span class="smallB">';
	echo 'Date Time';
	echo '</span></td>';
	echo '<td class="left" valign="top" width="20%" nowrap="nowrap"><span class="smallB">';
	echo 'View User';
	echo '</span></td>';
	echo '<td class="left" valign="top" width="10%" nowrap="nowrap"><span class="smallB">';
	echo 'View Bug';
	echo '</span></td>';
	echo '<td class="left" valign="top" width="10%" nowrap="nowrap"><span class="smallB">';
	echo 'AssignedTo';
	echo '</span></td>';
	echo '<td class="center" valign="top" colspan="3" nowrap="nowrap" ><span class="smallB">';
	echo 'Commands';
	echo '</span></td>';
	/* echo '<td class="left" valign="top" width="15%" nowrap="nowrap"><span class="smallB">';
	echo 'Issue Filter Command';
	echo '</span></td>';
	echo '<td class="left" valign="top" width="15%" nowrap="nowrap"><span class="smallB">';
	echo 'Date Filter Command';
	echo '</span></td>'; */
	echo '</tr>';
	
	$r_id=0;
	foreach ($data as $row) {	
				
				$g_bug = bug_get( $row['bug_id'] );
				$status_color = get_status_color( $g_bug->status );
				$r_id+=1;
				//$status_color = '#FFFFFF';
				if  (($row['user_id']==1) and auth_get_current_user_id()<>1) {
					continue;
				}
					
				echo '<tr bgcolor="' . $status_color . '">';
				echo '<td class="left" valign="top" width ="5%" nowrap="nowrap">';
				echo '<span class="small">';
				echo $r_id;
				echo '</span></td>';
				echo '<td class="left" valign="top" width ="15%" nowrap="nowrap">';
				echo '<span class="small">';
				echo date( config_get( 'normal_date_format' ), $row['view_date'] );
				echo '</span></td>';
				echo '<td class="left" valign="top" width="20%" nowrap="nowrap"><span class="small">';
				echo prepare_user_name( $row['user_id']);
				echo '</span></td>';
				echo '<td class="left" valign="top" width="10%" nowrap="nowrap"><span class="small">';
				echo string_get_bug_view_link( $row['bug_id'] );
				echo '</span></td>';
				echo '<td class="left" valign="top" width="10%" nowrap="nowrap"><span class="small">';
				echo print_user_with_subject( $g_bug->handler_id, $row['bug_id'] );
				echo '</span></td>';
				echo '<td class="left" valign="top" width="10%" nowrap="nowrap"><span class="small">';
				echo '<a href="'.plugin_page( "index" ).'&uid='.$row['user_id'].'">Fileter by this user</a>';
				echo '</span></td>';
				echo '<td class="left" valign="top" width="10%" nowrap="nowrap"><span class="small">';
				echo '<a href="'.plugin_page( "index" ).'&iid='.$row['bug_id'].'">'.'Filter by this issue<a>';
				echo '</span></td>';
				echo '<td class="left" valign="top" width="10%" nowrap="nowrap"><span class="small">';
				echo '<a href="'.plugin_page( "index" ).'&date='.$row['view_date'].'">'.'Filter by this date<a>';
				echo '</span></td>';
				echo '</tr>';
				
				
			}
			echo '</table>';
}

echo '<br/>';
html_status_legend();
echo '<br/>';

html_page_bottom1( );	

function getusername($html){
	preg_match_all("/\<a.*href=\"(.*?)\".*?\>(.*)\<\/a\>+/", stripslashes($html), $matches, PREG_SET_ORDER);
	 foreach($matches as $key=>$match) {
        return $match[2];
    }
	
	
}	
?>