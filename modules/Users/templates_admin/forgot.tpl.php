<h2><?php print text('forgot:title'); ?></h2>
<?php print $form->printErrors(); ?>
<?php print sml()->printMessage(); ?>
<form action="<?php print $this->xhtmlUrl(); ?>" method="post">
	<fieldset>
		<ol>
			<li>
				<label for="user"><?php print text('forgot:username'); ?>:</label>
				<input type="text" name="user" id="user" value="" />
			</li>
			<li>
				<input type="submit" name="submit" id="submit" value="<?php print text('forgot:button'); ?>" />
				<?php print $this->link(text('forgot:login'), 'login'); ?>
			</li>
			</ol>
	</fieldset>
</form>