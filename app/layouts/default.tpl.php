<!DOCTYPE html>
<html>
<head>
	<title><?= $this->page_title(); ?></title>
	<meta charset="utf-8">
	<?php //$this->add_resource( new Aspen_Css('css/base.css') ); ?>
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