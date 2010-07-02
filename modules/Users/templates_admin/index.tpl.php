<h2><?php print $this->text('userlist:title'); ?></h2>

<?php print app()->sml->printMessage(); ?>

<table cellspacing="0">
	<caption>User Accounts | <?php print $this->link($this->text('userlist:adduser'), 'add'); ?></caption>
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
		if($users){
			foreach($users as $user){
		?>
		<tr>
			<td><?php print $this->link($user['first_name'].' '.$user['last_name'], 'edit', array('id' => $user['id'])) ?></td>
			<td><?php print $user['username'] ?></td>
			<td><?php print Date::niceDate($user['latest_login']) ?></td>
			<td><?php print Utils::implode(', ', Utils::extract('Groups.{n}.name', $user)) ?></td>
		</tr>
		<?php
			}
		} else { ?>
		<tr><td><?php print $this->text('userlist:noresults'); ?></td></tr>
		<?php } ?>
	</tbody>
</table>