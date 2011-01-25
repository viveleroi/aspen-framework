<!DOCTYPE html>
<html>
<head>
	<title><?= $this->page_title(); ?></title>
	<meta charset="utf-8">
	<?php //$this->addCss('admin/screen.css'); ?>
	<?php //$this->addCss('admin/ie.css',array('cdtnl_cmt'=>'if IE')); ?>
	<?php $this->loadModuleHeader(); ?>
</head>
<body id="<?= $this->body_id(); ?>">

	<h1><?= app()->config('application_name'); ?></h1>
<?php if(user()->isLoggedIn()): ?>
	<span>Logged in as <?= $this->link(session()->getName('first_name'), 'users/my_account'); ?></span>
<?php endif ?>

<?php if(user()->isLoggedIn()): ?>
	<ul id="nav">
		<?= app()->generateInterfaceMenu(false) ?>
		<li><?= $this->link('Logout', 'users/logout'); ?></li>
	</ul>
<?php endif; ?>
		
<?php $this->page(); ?>
	
	<p><?= text('copyright', VERSION); ?></p>
	<?php $this->loadModuleFooter(); ?>
</body>
<?= $this->htmlHide(VERSION_COMPLETE); ?>
</html>