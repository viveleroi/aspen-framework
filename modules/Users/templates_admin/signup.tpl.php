<h2><?= text('signup:title'); ?></h2>
<?= sml()->printMessage(); ?>
<?= $form->printErrors(); ?>
<form id="frm-signup" action="<?= $this->action(); ?>" method="post">
	<fieldset>
		<ol>
			<li>
				<label for="email"><?= text('signup:email'); ?>:</label>
				<input type="text" id="email" name="email" value="<?= $form->cv('email') ?>" class="text" />
			</li>
			<li>
				<label for="first_name"><?= text('signup:first_name'); ?>:</label>
				<input type="text" id="first_name" name="first_name" value="<?= $form->cv('first_name') ?>" class="text" />
			</li>
			<li>
				<label for="last_name"><?= text('signup:last_name'); ?>:</label>
				<input type="text" id="last_name" name="last_name" value="<?= $form->cv('last_name') ?>" class="text" />
			</li>
			<li>
				<label for="password"><?= text('signup:password'); ?>:</label>
				<input type="password" id="password" name="password" value="" class="text" />
			</li>
			<li>
				<label for="password_confirm"><?= text('signup:password_confirm'); ?>:</label>
				<input type="password" id="password_confirm" name="password_confirm" value="" class="text" />
			</li>
			<li class="frm-submit">
				<input type="submit" name="submit" value="<?= text('signup:submit'); ?>" />
			</li>
		</ol>
	</fieldset>
</form>