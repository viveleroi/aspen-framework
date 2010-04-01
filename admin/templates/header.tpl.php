<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title><?php print $this->APP->config('application_name'); ?></title>

		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="description" content="aspen framework" />
		<meta name="keywords" content="aspen framework" />

		<?php $this->addCss( array('file'=>'screen.css','mediatype'=>'screen, projection','from'=>'i')); ?>
		<?php $this->addCss( array('file'=>'print.css','mediatype'=>'print','from'=>'i')); ?>
		<?php $this->addCss( array('file'=>'ie.css','cdtnl_cmt'=>'if IE','from'=>'i')); ?>
	  	
	  	<?php $this->loadModuleHeader(); ?>

	</head>

	<body id="<?php print $this->body_id(); ?>">

	<div class="container">
		
		<div id="header">
		<h1><?php print $this->APP->config('application_name'); ?></h1>
		<?php if($this->APP->user->isLoggedIn()){ ?>
		<span>Logged in as <?php print $this->createLink($this->APP->params->session->getRaw('nice_name'), 'my_account', false, 'Users'); ?></span>
		<?php } ?>
		<div class="clear clearfix"></div>
		</div>
		
		<?php print $this->APP->modulesAwaitingInstallAlert(); ?>
		
		<?php if($this->APP->user->isLoggedIn()){ ?>
		<ul id="nav">
			<?php print $this->APP->generateInterfaceMenu(false) ?>
			<li><?php print $this->createLink('Logout', 'logout', false, 'Users'); ?></li>
		</ul>
		<?php } ?>
		
		<div id="content" class="box<?php print $this->APP->user->isLoggedIn() ? ' span-19 last' : '' ?>">