<h2><?php print text('myaccount:title'); ?></h2>
<?php print $form->printErrors(); ?>
<?php print app()->sml->printMessage(); ?>
<form action="<?php print $this->url() ?>" method="post">
	<input id="allow_login_hidden" name="allow_login" type="hidden" value="<?php print $form->cv('allow_login') ?>" />
	<fieldset>
		<p class="notice"><?php print text('myaccount:help'); ?></p>
		<ol>
			<li>
				<label for="username"><?php print text('userform:username'); ?>:</label>
				<input type="text" id="username" name="username" value="<?php print $form->cv('username'); ?>" />
			</li>
			<li>
				<label for="password"><?php print text('myaccount:label:password'); ?>:</label>
				<input type="password" name="password" id="password" />
			</li>
			<li>
				<label for="password_confirm"><?php print text('myaccount:label:password_2'); ?>:</label>
				<input type="password" name="password_confirm" id="password_confirm" />
			</li>
			<li><input type="submit" name="user-submit" value="<?php print text('myaccount:button:submit'); ?>" /></li>
		</ol>
	</fieldset>
</form>