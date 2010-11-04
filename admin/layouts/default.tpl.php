<!DOCTYPE html>
<html>
<head>
	<title><?= $this->page_title(); ?></title>
	<meta charset="utf-8">
	<meta name="description" content="">
	<meta name="keywords" content="">
	<?php //$this->addCss( array('file'=>'screen.css','mediatype'=>'screen, projection','from'=>'i')); ?>
	<?php //$this->addCss( array('file'=>'ie.css','cdtnl_cmt'=>'if IE','from'=>'i')); ?>
	<?php $this->loadModuleHeader(); ?>
</head>
<body id="<?= $this->body_id(); ?>">

	
		<h1><?= app()->config('application_name'); ?></h1>
	<?php if(user()->isLoggedIn()): ?>
		<span>Logged in as <?= $this->link(app()->session->getName('first_name'), 'my_account', false, 'Users'); ?></span>
	<?php endif ?>

		
	<?php if(user()->isLoggedIn()): ?>
	<ul id="nav">
		<?= app()->generateInterfaceMenu(false) ?>
		<li><?= $this->link('Logout', 'logout', false, 'Users'); ?></li>
	</ul>
	<?php endif; ?>
		
		
<?php $this->page(); ?>
<p><?= text('copyright', VERSION); ?></p>
	
</body>
<?= $this->htmlHide(VERSION_COMPLETE); ?>
</html>