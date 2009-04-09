<h2><?php print $this->text('forgot:title'); ?></h2>

<?php print $this->APP->form->printErrors(); ?>
<?php print $this->APP->sml->printMessage(); ?>

<form action="<?php print $this->createXhtmlValidUrl(); ?>" method="post">
	<fieldset>
	
		<p>
			<label for="user"><?php print $this->text('forgot:username'); ?>:</label><br />
			<input type="text" name="user" id="user" value="" class="text" />
		</p>
		
		<p>
			<input type="submit" name="submit" id="submit" value="<?php print $this->text('forgot:button'); ?>" />
			<?php print $this->createLink($this->text('forgot:login'), 'login'); ?>
		</p>
	</fieldset>
</form>