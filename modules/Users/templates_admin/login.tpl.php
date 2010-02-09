<h2><?php print $this->text('login:title'); ?></h2>

<?php print $this->APP->sml->printMessage(); ?>

<form action="<?php print $this->createFormAction('authenticate'); ?>" method="post">
	<fieldset>
	
		<p>
			<label for="user"><?php print $this->text('login:username'); ?></label><br />
			<input type="text" name="user" id="user" value="" class="text" />
		</p>
		
		<p>
			<label for="pass"><?php print $this->text('login:password'); ?></label><br />
			<input type="password" name="pass" id="pass" value="" class="text" />
		</p>
		
		<p>
			<input type="submit" name="submit" id="submit" value="Login" />
			<?php print $this->createLink($this->text('login:forgot_link'), 'forgot'); ?>
		</p>
	
	</fieldset>
</form>