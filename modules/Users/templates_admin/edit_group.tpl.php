<h2><?php print $this->text('groupform:'.ADD_OR_EDIT.'title'); ?></h2>

<?php print $this->APP->form->printErrors(); ?>
<?php print $this->APP->sml->printMessage(); // print any system messages/errors ?>

<?php if(IS_EDIT_PAGE){ ?>
<p><a href="<?php print $this->APP->template->createXhtmlValidUrl('delete_group', array('id' => $values['id'])) ?>" onclick="return confirm('<?php print $this->text('groupform:delete_warn'); ?>')"><?php print $this->text('groupform:delete_link'); ?></a></p>
<?php } ?>

<form method="post" action="<?php print $this->createFormAction(); ?>">
	
	<fieldset>
	
		<p>
		<label for="name"><?php print $this->text('groupform:name'); ?>:</label><br />
		<input type="text" id="name" name="name" value="<?php print $values['name'] ?>" class="text" />
		</p>

		<p><input type="submit" name="submit" value="<?php print $this->text('groupform:'.ADD_OR_EDIT.'button'); ?>" /></p>
		
	</fieldset>

	<!--
	<fieldset>
		<legend>Permissions</legend>

		<p>Check boxes below each application, module, and method (page) that you wish to allow users of this group to be able to access.</p>

		<table cellspacing="0">
			<thead>
				<tr>
					<th>Application</th>
					<th>Module</th>
					<th>Method</th>
				</tr>
			</thead>
			<tbody>
				<?php
				//if($permissions){
					//foreach($permissions as $permissions){ ?>
				<tr>
					<td></td>
					<td></td>
					<td></td>
				</tr>
				<?php
					//}
				//}
				?>
				<tr>
					<td></td>
					<td></td>
					<td></td>
				</tr>
				<tr>
					<td></td>
					<td></td>
					<td></td>
				</tr>
			</tbody>
		</table>

	</fieldset>
 -->

</form>