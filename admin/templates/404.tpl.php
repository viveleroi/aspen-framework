<h2><?php print $this->text('404:title'); ?></h2>

<p><?php print sprintf($this->text('404:message'), app()->router->getSelectedModule(), app()->router->getSelectedMethod()); ?></p>