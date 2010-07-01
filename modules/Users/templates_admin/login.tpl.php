<h2><?php print $this->text('login:title'); ?></h2>

<?php print app()->sml->printMessage(); ?>

<form action="<?php print $this->action('authenticate'); ?>" method="post">
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
			<?php print $this->link($this->text('login:forgot_link'), 'forgot'); ?>
		</p>
	
	</fieldset>
</form>