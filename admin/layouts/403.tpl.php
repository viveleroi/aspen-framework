<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<title><?= text('403:title'); ?></title>
	<style type="text/css">
		body { background-color: #eee; font-family: Georgia, serif; }
		h2 { font-size: 1.2em; margin-left: 25px; }
	</style>
</head>
<body>
	<h2><?= text('403:title'); ?></h2>
	<p><?= sprintf(text('403:message'), router()->module(), router()->method()); ?></p>
</body>
</html>