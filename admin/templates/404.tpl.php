<h2><?php print text('404:title'); ?></h2>
<p><?php print sprintf(text('404:message'), app()->router->module(), app()->router->method()); ?></p>