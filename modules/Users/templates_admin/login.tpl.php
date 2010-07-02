<h2><?php print $this->text('login:title'); ?></h2>

<?php print app()->sml->printMessage(); ?>

<form action="<?php print $this->action('authenticate'); ?>" method="post">
	<fieldset>
		<ol>
			<li>
				<label for="user"><?php print $this->text('login:username'); ?></label>
				<input type="text" name="user" id="user" value="" />
			</li>
			<li>
				<label for="pass"><?php print $this->text('login:password'); ?></label>
				<input type="password" name="pass" id="pass" value="" />
			</li>
			<li>
				<input type="submit" name="submit" id="submit" value="Login" />
				<?php print $this->link($this->text('login:forgot_link'), 'forgot'); ?>
			</li>
		</ol>
	</fieldset>
</form>