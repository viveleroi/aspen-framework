<h2><?php print $this->text('myaccount:title'); ?></h2>

<?php print $this->APP->form->printErrors(); ?>
<?php print $this->APP->sml->printMessage(); ?>

<form action="<?php print $this->createUrl() ?>" method="post">
	<fieldset>
		<p class="notice"><?php print $this->text('myaccount:help'); ?></p>
	
		<p>
			<label for="password_1"><?php print $this->text('myaccount:label:password'); ?>:</label><br />
			<input type="password" name="password_1" id="password_1" class="text" />
		</p>
		
		<p>
			<label for="password_2"><?php print $this->text('myaccount:label:password_2'); ?>:</label><br />
			<input type="password" name="password_2" id="password_2" class="text" />
		</p>
		
		<p><input type="submit" name="submit" value="<?php print $this->text('myaccount:button:submit'); ?>" /></p>
	
	</fieldset>
</form>