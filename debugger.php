<?php
$disable_error_log = $_REQUEST['disable_error_log'] ?? 'N';
$error_type = $_REQUEST['error_type'] ?? '3';
if ($disable_error_log == 'N') {
	@ini_set('log_errors', 'On');
	@ini_set('error_log', ABSPATH . '/wp-content/uploads/php-errors.log');
	ini_set('display_errors', 'on');
	error_reporting(E_ALL & ~E_DEPRECATED);
	set_error_handler("exception_error_handler");
}
function exception_error_handler($severity, $message, $file, $line) {
	$error_type = $_REQUEST['error_type'] ?? '2';
	/* if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return;
    } */

	//warning
	if ($severity == $error_type || $error_type == 'all') {
		print('<pre>DBG=' . print_r(get_defined_vars(), true) . '</pre>');
		//throw new ErrorException($message, 0, $severity, $file, $line);
	}
}
