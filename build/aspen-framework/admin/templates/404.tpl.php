<h2><?php print $this->text('404:title'); ?></h2>

<p><?php print sprintf($this->text('404:message'), $this->APP->router->getSelectedModule(), $this->APP->router->getSelectedMethod()); ?></p>