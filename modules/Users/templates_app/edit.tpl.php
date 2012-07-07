<h2><?php print text('userform:'.ADD_OR_EDIT.'title'); ?></h2>

<?php $form->printErrors(); ?>
<?php print sml()->printMessage(); ?>

<?php if(IS_EDIT_PAGE){ ?>
<p><a href="<?php print $this->xhtmlUrl('delete', array('id' => $form->cv('id'))) ?>" onclick="return confirm('<?php print text('userform:delete_warn'); ?>')"><?php print text('userform:delete_link'); ?></a></p>
<?php } ?>
<form method="post" action="<?= Url::path()->action(); ?>">
	<fieldset>
		<legend><?php print text('userform:profile'); ?></legend>
		<ol>
			<li>
				<label for="email"><?php print text('userform:email'); ?>:</label>
				<input type="text" id="email" name="email" value="<?php print $form->cv('email') ?>" />
			</li>
			<li>
				<label for="first_name"><?php print text('userform:first_name'); ?>:</label>
				<input type="text" id="first_name" name="first_name" value="<?php print $form->cv('first_name') ?>" />
			</li>
			<li>
				<label for="last_name"><?php print text('userform:last_name'); ?>:</label>
				<input type="text" id="last_name" name="last_name" value="<?php print $form->cv('last_name') ?>" />
			</li>

			<li>
				<label for="password"><?php print text('userform:password'); ?>:</label>
				<input type="password" id="password" name="password" value=""  />
			</li>
			<li>
				<label for="password_confirm"><?php print text('userform:confirm'); ?>:</label>
				<input type="password" id="password_confirm" name="password_confirm" value="" />
			</li>
			<li>
				<label for="allow_login">Allow Login:</label>
				<input id="allow_login_hidden" name="allow_login" type="hidden" value="0" />
				<input id="allow_login" name="allow_login"<?php print $form->checked('allow_login'); ?> type="checkbox" value="1" />
			</li>
		</ol>
	</fieldset>
	<fieldset>
		<legend><?php print text('userform:accessgroup'); ?></legend>
		<ol>
			<?php if($groups): foreach($groups as $group): ?>
			<li>
				<input type="checkbox" name="Groups[]" value="<?php print $group['id'] ?>" id="group_<?php print $group['id'] ?>"<?php print $form->checked('Groups', $group['id']); ?> />
				<label for="group_<?php print $group['id'] ?>"><?php print $group['name'] ?></label>
			</li>
			<?php endforeach; endif; ?>
		</ol>
	</fieldset>
	<fieldset>
		<input type="submit" name="submit" value="<?php print text('userform:'.ADD_OR_EDIT.'button'); ?>" />
	</fieldset>
</form>