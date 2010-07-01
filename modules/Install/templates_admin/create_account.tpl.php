<h2><?php print $this->text('s2:title'); ?></h2>

<p><?php print $this->text('s2:intro'); ?></p>

<?php print $form->printErrors(); ?>
<?php print app()->sml->printMessage(); ?>

<form action="<?php print $this->url('account') ?>" method="post">

	<p>
		<label for="username"><?php print $this->text('s2:email'); ?>:</label><br />
		<input type="text" name="username" id="username" class="text" />
	</p>

	<p>
		<label for="nice_name"><?php print $this->text('s2:name'); ?>:</label><br />
		<input type="text" name="nice_name" id="nice_name" class="text" />
	</p>
	
	<p>
		<label for="password"><?php print $this->text('s2:password'); ?>:</label><br />
		<input type="password" name="password" id="password" class="text" />
	</p>

	<p>
		<label for="password_2"><?php print $this->text('s2:confirm'); ?>:</label><br />
		<input type="password" name="password_2" id="password_2" class="text" />
	</p>

	<p><input type="submit" value="<?php print $this->text('s2:submit'); ?>" name="submit" id="submit" /></p>
	
</form>