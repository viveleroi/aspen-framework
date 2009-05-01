<h2><?php print $this->text('s2:title'); ?></h2>

<p><?php print $this->text('s2:intro'); ?></p>

<?php print $this->APP->form->printErrors(); ?>
<?php print $this->APP->sml->printMessage(); ?>

<form action="<?php print $this->createUrl('account') ?>" method="post">

	<p>
		<label><?php print $this->text('s2:email'); ?>:</label><br />
		<input type="text" name="email" id="email" class="text" />
	</p>

	<p>
		<label><?php print $this->text('s2:name'); ?>:</label><br />
		<input type="text" name="nice_name" id="nice_name" class="text" />
	</p>
	
	<p>
		<label><?php print $this->text('s2:password'); ?>:</label><br />
		<input type="password" name="password_1" id="password_1" class="text" />
	</p>

	<p>
		<label><?php print $this->text('s2:confirm'); ?>:</label><br />
		<input type="password" name="password_2" id="password_2" class="text" />
	</p>

	<p><input type="submit" value="<?php print $this->text('s2:submit'); ?>" name="submit" id="submit" /></p>
	
</form>