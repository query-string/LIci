<?

session_start();

$root = "../../..";
if (file_exists($root.'/wp-load.php')) {
	require_once($root.'/wp-load.php');
} else {
	require_once($root.'/wp-config.php');
}

if (current_user_can('activate_plugins')) {
	if (isset($_GET['op'])) {
		$op = $_GET['op'];
		switch($op) {
			case 'get_posts':
				lici_get_post_ids();
				break;
			case 'sop':
				$pid = intval($_GET['pid']);
				$lid = intval($_GET['lid']);
				lici_send_one_post($pid,$lid);
				break;
			case 'set_default':
				$pid = intval($_GET['id']);
				lici_set_default($pid);
				break;
		}
	}
} else {
	print "not admin";
}

function lici_get_post_ids() {
	global $wpdb;
	
	$posts = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE `post_type`='post' AND `post_status`='publish' AND `post_password`='' ORDER BY `ID` ASC;");
	$po = "";
	$f = true;
	foreach($posts as $p) {
		if ($f) {
			$po .= "$p->ID";
			$f = false;
		} else {
			$po .= ",$p->ID";
		}
	}
	print $po;
}

function lici_send_one_post($pid,$lid) {
	global $wpdb;
	$options_table = $wpdb->prefix."lici_options";
	
	//$lid = get_option("lici-default");
	$login = $wpdb->get_row("SELECT * FROM $options_table WHERE `id`='$lid' LIMIT 1;");
	
	$post = get_post($pid);
	if (lici_send_data($post, $login)) {
		print "ok\n".$_SESSION['wplicierror'];
	} else {
		print "error";
	}
	
}

function lici_set_default($pid) {
	update_option("lici-default",$pid);
	print "Теперь $pid по умолчанию";
}

?>