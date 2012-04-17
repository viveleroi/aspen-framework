<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title><?= text('404:title'); ?></title>
	<style>
		body { background-color: #eee; font-family: Georgia, serif; }
		h2 { font-size: 1.2em; margin-left: 25px; }
	</style>
</head>
<body>
	<h2><?= text('404:title'); ?></h2>
	<p><?= text('404:message', router()->module(), router()->method()); ?></p>
</body>
</html>