<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title><?= text('403:title'); ?></title>
	<style>
		body { background-color: #eee; font-family: Georgia, serif; }
		h2 { font-size: 1.2em; margin-left: 25px; }
	</style>
</head>
<body>
	<h2><?= text('403:title'); ?></h2>
	<p><?= text('403:message', router()->module(), router()->method()); ?></p>
</body>
</html>