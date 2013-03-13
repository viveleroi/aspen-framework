<h2><?php print text('login:title'); ?></h2>
<?php print sml()->printMessage(); ?>
<form action="<?= Url::path('authenticate')->action(); ?>" method="post">
	<fieldset>
		<ol>
			<li>
				<label for="user"><?php print text('login:email'); ?></label>
				<input type="text" name="user" id="user" value="" />
			</li>
			<li>
				<label for="pass"><?php print text('login:password'); ?></label>
				<input type="password" name="pass" id="pass" value="" />
			</li>
			<li>
				<input type="submit" name="submit" id="submit" value="Login" />
				<?php print Link::path(text('login:forgot_link'), 'forgot'); ?> or <?php print Link::path(text('signup:signup_link'), 'signup'); ?>
			</li>
		</ol>
	</fieldset>
</form>
