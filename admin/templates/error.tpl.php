<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<title><?php print $error['error_message'] ?></title>
	<style type="text/css">
		body { background-color: #eee; font-family: Georgia, serif; }
		.inner { margin: 50px 0 0 100px; }
		.inner p { width: 500px; }
		h2 { font-size: 1.2em; margin-left: 25px; }
		.err-box li { list-style: none; margin: 3px 0 }
		.key { display: block; width: 140px; color: #888; float: left; text-align: right; padding-right: 10px; }
		.data { padding-left: 10px; }
		ul.sub { margin-left: 120px; }
	</style>
</head>
<body>
	<h2><?php print $error['error_message'] ?></h2>
	<ul class="err-box">
	<?php
	foreach($error as $key => $data){
		if(!is_array($data)){
			print '<li><span class="key">'.$key.'</span>&rarr;<span class="data">'.$data.'</span></li>';
		} else {
			print '<li><span class="key">'.$key.'</span>&rarr;<span class="data"><ul class="sub">';
			foreach($data as $sub){
				print '<li><span class="key">'.$sub['function'].'</span>&rarr;<span class="data">'.$sub['file'].' ('.$sub['line'].')</span></li>';
			}
			print '</ul></span></li>';
		}
	}
	?>
	</ul>
</body>
</html>