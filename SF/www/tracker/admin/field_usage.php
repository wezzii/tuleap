<?php
//
// Copyright (c) Xerox Corporation, CodeX Team, 2001-2003. All rights reserved
//
// $Id$
//
//
//  Written for CodeX by Stephane Bouhet
//

if ( !user_isloggedin() ) {
	exit_not_logged_in();
	return;
}

if ( !$ath->userIsAdmin() ) {
	exit_permission_denied();
	return;
}

$ath->adminHeader(array('title'=>'Tracker Administration - Field Usage Administration','help' => 'TrackerAdministration.html#TrackerFieldUsageManagement'));

echo '<H2>Tracker \'<a href="/tracker/admin/?group_id='.$group_id.'&atid='.$atid.'">'.$ath->getName().'</a>\' - Field Usage Administration</H2>';
$ath->displayFieldUsageList();
$ath->displayFieldUsageForm();

$ath->footer(array());

?>
