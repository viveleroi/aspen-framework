<h2><?php print $this->text('userform:edittitle'); ?></h2>

<?php print $this->APP->form->printErrors(); ?>
<?php print $this->APP->sml->printMessage(); ?>

<p><a href="<?php print $this->APP->template->createXhtmlValidUrl('delete', array('id' => $values['id'])) ?>" onclick="return confirm('<?php print $this->text('userform:delete_warn'); ?>')"><?php print $this->text('userform:delete_link'); ?></a></p>

<form method="post" action="<?php print $this->createFormAction(); ?>">
	<fieldset>
		<legend><?php print $this->text('userform:profile'); ?></legend>
	
		<p>
		<label for="username"><?php print $this->text('userform:username'); ?>:</label><br />
		<input type="text" id="username" name="username" value="<?php print $values['username'] ?>" class="text" />
		</p>
		
		<p>
		<label for="nice_name"><?php print $this->text('userform:nicename'); ?>:</label><br />
		<input type="text" id="nice_name" name="nice_name" value="<?php print $values['nice_name'] ?>" class="text" />
		</p>
		
		<p>
		<label for="password"><?php print $this->text('userform:password'); ?>:</label><br />
		<input type="password" id="password" name="password" value="" class="text" />
		</p>
		
		<p>
		<label for="password_confirm"><?php print $this->text('userform:confirm'); ?>:</label><br />
		<input type="password" id="password_confirm" name="password_confirm" value="" class="text" />
		</p>
	
		<p>
		<label for="allow_login">Allow Login:</label>
		<input id="allow_login" name="allow_login"<?php print $values['allow_login'] ? ' checked="checked"' : '' ?> type="checkbox" value="1" />
		</p>
	</fieldset>
	
	<fieldset>
		<legend><?php print $this->text('userform:accessgroup'); ?></legend>
		
		<p>
		<?php
		if($groups){
			foreach($groups as $group){
		?>
		<input type="checkbox" name="group[]" value="<?php print $group['id'] ?>" id="group_<?php print $group['id'] ?>"<?php print (in_array($group['id'],$values['group'])? ' checked="checked"' : '') ?> />
		<label for="group_<?php print $group['id'] ?>"><?php print $group['name'] ?></label><br />
		<?php
			}
		}
		?>
		</p>
		
		<p><input type="submit" name="submit" value="<?php print $this->text('userform:editbutton'); ?>" /></p>
	</fieldset>
</form>