<?php

include('config.php');

$conn = mysql_connect($config['db_hostname'], $config['db_username'], $config['db_password']);
mysql_select_db($config['db_database']);

// empty table
if(isset($_GET['wipe']) && $_GET['wipe'] == "yes"){
	mysql_query('TRUNCATE TABLE error_log');
	header("Location: errors.php");
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>Aspen Error Log Viewer</title>
		
		<script type="text/javascript" src="http://www.google.com/jsapi"></script>
		<script type="text/javascript">
			google.load("jquery", "1");
			google.setOnLoadCallback(function() {
				$('.error-body').hide();
				$('div.error-title').click(function(){
					elem_id = this.id;
					elem_id = elem_id.replace(/title_/, '');
					$('#body_'+elem_id).slideToggle();
				});
			});
		</script>

		<style type="text/css">
			div.error-holder { margin: 2px 10px; }
			div.error-title { border: 1px solid #FF6F6F; background: #FFC19F; }
			div.error-title:hover { background: #EF654A; cursor: pointer; }
			
			div.urgent { background: #EF6868; }
			div.urgent:hover { background: #DF3737; }
			
			div.error-title ul { margin: 0; padding: 0; }
			div.error-title li { list-style: none; display: inline-block; padding: 3px; }
			li.message, li.file { height: 15px; overflow: hidden; }
			li.message { width: 680px; }
			span { font-weight: bold; }
			
			div.error-body { background: #ccc; margin-bottom: 10px; }
			table { margin: 5px; }
			td, th { padding: 3px; 5px; }
			th { text-align: right; background: #aaa; }
			
			p { margin-left: 10px; }
		</style>

	</head>
	<body>

	<p><a href="errors.php?wipe=yes">Empty Error Log</a></p>
	
	<?php
	$result = mysql_query('SELECT *, COUNT(id) as error_count FROM error_log GROUP BY error_message, error_file, error_line ORDER BY error_count DESC');
	while ($row = mysql_fetch_assoc($result)) {
	
		$clean_message = preg_replace("/<a href='function.(.*)'>function.(.*)<\/a>/", '', $row['error_message']);
		$clean_message = str_replace('[]', '', $clean_message);
	
	?>
		<div class="error-holder">
			<div class="error-title<?php print ($row['error_count'] > 5 ? ' urgent' : ''); ?>" id="title_<?php print $row['id']; ?>">
				<ul>
					<li class="message">(<?php print $row['error_count']; ?>) <?php print $clean_message ?></li>
					<li class="file"><strong>File:</strong> <?php print basename($row['error_file']) ?> / <?php print $row['error_line'] ?></li>
				</ul>
			</div>
			<div class="error-body" id="body_<?php print $row['id']; ?>">
				<table>
					<tr><th>Application</th><td><?php print $row['application'] ?></td></tr>
					<tr><th>Version</th><td><?php print $row['version'] ?></td></tr>
					<tr><th>Date</th><td><?php print $row['date'] ?></td></tr>
					<tr><th>Visitor IP</th><td><?php print $row['visitor_ip'] ?></td></tr>
					<tr><th>Referrer</th><td><?php print $row['referer_url'] ?></td></tr>
					<tr><th>Request URI</th><td><?php print $row['request_uri'] ?></td></tr>
					<tr><th>User Agent</th><td><?php print $row['user_agent'] ?></td></tr>
					<tr><th>Error Type</th><td><?php print $row['error_type'] ?></td></tr>
					<tr><th>File</th><td><?php print $row['error_file'] ?></td></tr>
					<tr><th>Line</th><td><?php print $row['error_line'] ?></td></tr>
					<tr><th>Message</th><td><?php print $row['error_message'] ?></td></tr>
				</table>
			</div>
		</div>
	<?php } ?>

	</body>
</html>