<h2><?php print $this->text('s1:title'); ?></h2>
<p><?php print $this->text('s1:intro'); ?></p>

<?php print $form->printErrors(); ?>
<?php print app()->sml->printMessage(); ?>

<form action="<?php print $this->url('setup') ?>" method="post">
	<ol>
		<li>
			<label><?php print $this->text('s1:user'); ?>:</label>
			<input type="text" name="db_username" id="db_username" />
		</li>
		<li>
			<label><?php print $this->text('s1:pass'); ?>:</label>
			<input type="password" name="db_password" id="db_password" />
		</li>
		<li>
			<label><?php print $this->text('s1:database'); ?>:</label>
			<input type="text" name="db_database" id="db_database" />
		</li>
		<li>
			<label><?php print $this->text('s1:host'); ?>:</label>
			<input type="text" name="db_hostname" id="db_hostname" value="localhost" />
		</li>
		<li><input type="submit" value="<?php print $this->text('s1:submit'); ?>" name="submit" id="submit" /></li>
	</ol>
</form>