<h2><?php print $this->text('s1b:title'); ?></h2>

<p><?php print $this->text('s1b:intro'); ?></p>

<?php print app()->sml->printMessage(); ?>

<form action="<?php print $this->action('account') ?>" method="post">
	<textarea name="config_content" id="config_content" rows="10" cols="50"><?php print $config ?></textarea>
	<p><input type="submit" value="<?php print $this->text('s1b:submit'); ?>" name="submit-noprocess" id="submit" /></p>
</form>