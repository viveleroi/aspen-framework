<h2><?php print $this->text('s1:title'); ?></h2>

<p><?php print $this->text('s1:intro'); ?></p>

<?php print $form->printErrors(); ?>
<?php print app()->sml->printMessage(); ?>

<form action="<?php print $this->url('setup') ?>" method="post">

	<p>
	<label><?php print $this->text('s1:user'); ?>:</label><br />
	<input type="text" name="db_username" id="db_username" class="text" />
	</p>
	
	<p>
	<label><?php print $this->text('s1:pass'); ?>:</label><br />
	<input type="password" name="db_password" id="db_password" class="text" />
	</p>
	
	<p>
	<label><?php print $this->text('s1:database'); ?>:</label><br />
	<input type="text" name="db_database" id="db_database" class="text" />
	</p>
	
	<p>
	<label><?php print $this->text('s1:host'); ?>:</label><br />
	<input type="text" name="db_hostname" id="db_hostname" value="localhost" class="text" />
	</p>

	<p><input type="submit" value="<?php print $this->text('s1:submit'); ?>" name="submit" id="submit" /></p>
</form>