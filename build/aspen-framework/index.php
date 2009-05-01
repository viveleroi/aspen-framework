<?php require('system/loader.inc.php'); ?>
<?php $APP = load_module('Index'); ?>
<?php $APP->redirectOnInstall(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title><?php print $APP->config('application_name'); ?></title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" href="<?php print $APP->router->getApplicationUrl() ?>/admin/css/screen.css" type="text/css" media="screen, projection" />
	  	<!--[if IE]><link rel="stylesheet" href="<?php print $APP->router->getApplicationUrl() ?>/admin/css/ie.css" type="text/css" media="screen, projection" /><![endif]-->
	</head>

	<body id="<?php print $APP->template->body_id(); ?>">
	
		<div class="container">

			<h1>Aspen Framework</h1>
				
			<div id="content" class="box">
				
				<h2>Welcome!</h2>
				<p><em>This template is located in <code>index.php</code></em>.</p>
				<p>You have successfully installed the Aspen framework. By default, we've provided two examples of ways you can use the framework.</p>
				<p>First, you have an application called "<a href="admin" title="Click here to login to the admin section.">Admin</a>" which is running within the framework entirely. This demo application may be used as the basis for a website administration section, but may be changed to serve whatever needs you have.</p>
				<p>Second, you have a single web page (this template) which only loads the framework for support purposes. Take a look at <code>index.php</code> to see an example of this use case.</p>
				<p>For more information about using the framework, we offer several places to find information.</p>
				
				<ul>
					<li><a href="http://docs.aspen-framework.org" title="Documentation">Aspen Documentation</a></li>
					<li><a href="http://docs.aspen-framework.org/API/" title="API Documentation">API Manual</a></li>
					<li><a href="http://groups.google.com/group/aspen-framework" title="Support Discussions">Support Discussions</a></li>
				</ul>
					
			
			</div>
			
			<p class="small"><a href="http://www.aspen-framework.org"><?php print $APP->config('application_name'); ?></a> <?php print VERSION ?></p>

		</div>
	</body>
</html>