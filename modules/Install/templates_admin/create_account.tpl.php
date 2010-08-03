<h2><?php print text('s2:title'); ?></h2>
<p><?php print text('s2:intro'); ?></p>

<?php print $form->printErrors(); ?>
<?php print app()->sml->printMessage(); ?>

<form action="<?php print $this->url('account') ?>" method="post">
	<ol>
		<li>
			<label for="username"><?php print text('s2:email'); ?>:</label>
			<input type="text" name="username" id="username" />
		</li>
		<li>
			<label for="first_name"><?php print text('s2:first_name'); ?>:</label>
			<input type="text" name="first_name" id="first_name" />
		</li>
		<li>
			<label for="last_name"><?php print text('s2:last_name'); ?>:</label>
			<input type="text" name="last_name" id="last_name" />
		</li>
		<li>
			<label for="password"><?php print text('s2:password'); ?>:</label>
			<input type="password" name="password" id="password" />
		</li>
		<li>
			<label for="password_2"><?php print text('s2:confirm'); ?>:</label>
			<input type="password" name="password_2" id="password_2" />
		</li>
		<li><input type="submit" value="<?php print text('s2:submit'); ?>" name="submit" id="submit" /></li>
	</ol>
</form>