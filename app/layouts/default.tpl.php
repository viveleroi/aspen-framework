<!DOCTYPE html>
<html>
<head>
	<title><?= $this->page_title(); ?></title>
	<meta charset="utf-8">
	<?php // Here is an example on how to either print resources directly, or append them to the header ?>
	<?php //$this->add_resource( new Aspen_Javascript('http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js') ); ?>
	<?= new Aspen_Javascript('http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js', array('cache-bust'=>false)); ?>
	<?php $this->loadModuleHeader(); ?>
</head>
<body id="<?= $this->body_id(); ?>">
	<h1><?= config()->get('application_name'); ?></h1>
	<?php $this->page(); ?>
	<p><?= text('copyright', VERSION); ?></p>
	<?php $this->loadModuleFooter(); ?>
</body>
<?= $this->htmlHide(VERSION_COMPLETE); ?>
</html>