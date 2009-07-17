<h2><?php print $this->text('myaccount:title'); ?></h2>

<?php print $this->APP->form->printErrors(); ?>
<?php print $this->APP->sml->printMessage(); ?>

<form action="<?php print $this->createUrl() ?>" method="post">
	<fieldset>
		<p class="notice"><?php print $this->text('myaccount:help'); ?></p>

		<p>
		<label for="username"><?php print $this->text('userform:username'); ?>:</label><br />
		<input type="text" id="username" name="username" value="<?php print $values['username'] ?>" class="text" />
		</p>
	
		<p>
			<label for="password"><?php print $this->text('myaccount:label:password'); ?>:</label><br />
			<input type="password" name="password" id="password" class="text" />
		</p>
		
		<p>
			<label for="password_confirm"><?php print $this->text('myaccount:label:password_2'); ?>:</label><br />
			<input type="password" name="password_confirm" id="password_confirm" class="text" />
		</p>
		
		<p><input type="submit" name="submit" value="<?php print $this->text('myaccount:button:submit'); ?>" /></p>
	
	</fieldset>
</form>