<h2><?php print $this->text('userlist:title'); ?></h2>

<?php print $this->APP->sml->printMessage(); ?>

<table cellspacing="0">
	<caption>User Accounts | <?php print $this->createLink($this->text('userlist:adduser'), 'add'); ?></caption>
	<thead>
		<tr>
			<th><?php print $this->text('userlist:th:name'); ?></th>
			<th><?php print $this->text('userlist:th:username'); ?></th>
			<th><?php print $this->text('userlist:th:latestlogin'); ?></th>
			<th><?php print $this->text('userlist:th:groups'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		if($users['RECORDS']){
			foreach($users['RECORDS'] as $user){ ?>
		<tr>
			<td><?php print $this->createLink($user['nice_name'], 'edit', array('id' => $user['id'])) ?></td>
			<td><?php print $user['username'] ?></td>
			<td><?php print $this->niceDate($user['latest_login']) ?></td>
			<td><?php print implode(', ', $this->APP->user->usersGroups($user['id'])) ?></td>
		</tr>
		<?php
			}
		} else { ?>
		<tr><td><?php print $this->text('userlist:noresults'); ?></td></tr>
		<?php } ?>
	</tbody>
</table>


<table cellspacing="0">
	<caption>Groups | <?php print $this->createLink($this->text('grouplist:addgroup'), 'add_group'); ?></caption>
	<thead>
		<tr>
			<th><?php print $this->text('grouplist:th:name'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		if($groups['RECORDS']){
			foreach($groups['RECORDS'] as $group){ ?>
		<tr>
			<td><?php print $this->createLink($group['name'], 'edit_group', array('id' => $group['id'])) ?></td>
		</tr>
		<?php
			}
		} else { ?>
		<tr><td><?php print $this->text('grouplist:noresults'); ?></td></tr>
		<?php } ?>
	</tbody>
</table>