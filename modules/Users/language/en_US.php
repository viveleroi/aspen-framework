<?php

/**
 * Language translations
 */

// login
$lang['admin']['users:login:head-title'] = 'Login';
$lang['admin']['login:title'] 			= 'Login';
$lang['admin']['login:email']			= 'Username';
$lang['admin']['login:password'] 		= 'Password';
$lang['admin']['login:forgot_link'] 	= 'Forgot your password?';
$lang['admin']['users:login:say:error'] = 'Your username and password did not match. Please try again.';

// signup
$lang['admin']['users:signup:head-title']	= 'Register a new account';
$lang['admin']['signup:title']				= 'Create Account';
$lang['admin']['signup:signup_link']		= 'Register';
$lang['admin']['signup:first_name']			= 'First Name';
$lang['admin']['signup:last_name']			= 'Last Name';
$lang['admin']['signup:email']				= 'E-mail';
$lang['admin']['signup:password']			= 'Password';
$lang['admin']['signup:password_confirm']	= 'Confirm Password';
$lang['admin']['signup:submit']				= 'Create Account';
$lang['admin']['signup:account_success']	= 'Your account has been created successfully. Please login using your registered E-mail address and password.';
$lang['admin']['signup:email:subject']		= 'Welcome to %s - Here\'s Your  Account Details';
$lang['admin']['signup:email:body']			= 'Welcome to {app} {first_name}!<br /><br />Please login at {url} using either your username ({username}) or your email address ({email}), and the password you created during signup.<br /><br />If you forget your password, please use the forgotten password form at {forgot} to reset it.<br /><br />Thank you!';

// forgotten password
$lang['admin']['users:forgot:head-title'] = 'Reset Your Password';
$lang['admin']['forgot:title'] 			= 'Reset Your Password';
$lang['admin']['forgot:email']			= 'Email';
$lang['admin']['forgot:button'] 		= 'Send New Password';
$lang['admin']['forgot:login'] 			= 'Back to Login';
$lang['admin']['users:forgot:say:success']= 'Your password has been reset. Please check your email.';
$lang['admin']['users:forgot:say:error']= 'We were unable to find any accounts matching that email.';

// my account
$lang['admin']['users:my_account:head-title']	= 'My Account';
$lang['admin']['myaccount:title']				= 'My Account';
$lang['admin']['myaccount:help']				= 'Enter in your password twice to change it.';
$lang['admin']['myaccount:label:password'] 		= 'Password';
$lang['admin']['myaccount:label:password_2'] 	= 'Confirm';
$lang['admin']['myaccount:button:submit'] 		= 'Save Changes';
$lang['admin']['users:myaccount:say:success']= 'Your account has been updated successfully.';

// user list
$lang['admin']['users:view:head-title']		= 'Users';
$lang['admin']['userlist:title'] 			= 'User Accounts';
$lang['admin']['userlist:adduser'] 			= 'Create New User Account';
$lang['admin']['userlist:th:name'] 			= 'Name';
$lang['admin']['userlist:th:email']			= 'Email';
$lang['admin']['userlist:th:latestlogin']	= 'Latest Login';
$lang['admin']['userlist:th:groups']		= 'User Groups';
$lang['admin']['userlist:noresults'] 		= 'No user accounts were found.';

// user add/edit form
$lang['admin']['users:add:head-title']  = 'Add User';
$lang['admin']['users:edit:head-title'] = 'Edit User';
$lang['admin']['userform:addtitle'] 	= 'Create Account';
$lang['admin']['userform:edittitle'] 	= 'Update Account';
$lang['admin']['userform:delete_link'] 	= 'Delete Account';
$lang['admin']['userform:delete_warn'] 	= 'Are you sure you want to delete this user account?';
$lang['admin']['userform:profile'] 		= 'Profile';
$lang['admin']['userform:email']		= 'Email';
$lang['admin']['userform:username']		= 'Username';
$lang['admin']['userform:first_name'] 	= 'First Name';
$lang['admin']['userform:last_name'] 	= 'Last Name';
$lang['admin']['userform:password'] 	= 'Password';
$lang['admin']['userform:confirm'] 		= 'Confirm';
$lang['admin']['userform:allowlogin'] 	= 'Allow Login';
$lang['admin']['userform:accessgroup'] 	= 'Access Groups';
$lang['admin']['userform:addbutton'] 	= 'Create New User';
$lang['admin']['userform:editbutton'] 	= 'Save Changes';
$lang['admin']['users:edit:say:success']= 'User account changes have been saved successfully.';

// delete
$lang['admin']['users:delete:say:success']= 'User account has been deleted successfully.';

// permission denied
$lang['admin']['users:denied:head-title'] = 'Permission Denied';
$lang['admin']['denied:title'] 	= 'Permission Denied';
$lang['admin']['denied:intro'] 	= 'Your user account does not currently have sufficient permission to access this page. Please contact an administrator for assistance.';

// query validation errors
$lang['*']['db:error:first_name']		= 'You must enter your first name.';
$lang['*']['db:error:last_name']		= 'You must enter your last name.';
$lang['*']['db:error:email']			= 'You must enter a valid E-mail address.';
$lang['*']['db:error:email-dup']		= 'The chosen email has already been used.';
$lang['*']['db:error:username']			= 'You must enter a valid username (alphanumeric, hyphens, or underscores).';
$lang['*']['db:error:username-dup']		= 'The chosen username has already been used.';
$lang['*']['db:error:password']			= 'You must enter a valid password.';
$lang['*']['db:error:password_match']	= 'Your passwords do not match.';
$lang['*']['db:error:groupname']		= 'You must enter a name.';
$lang['*']['db:error:groups']			= 'You must select at least one group.';
$lang['*']['db:error:groups-noadmin']	= 'You may not remove your own administrator status.';
?>