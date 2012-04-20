<?php

/**
 * Language translations
 */

// login
$lang['*']['users:login:head-title'] = 'Login';
$lang['*']['login:title'] 			= 'Login';
$lang['*']['login:email']			= 'Username';
$lang['*']['login:password'] 		= 'Password';
$lang['*']['login:forgot_link'] 	= 'Forgot your password?';
$lang['*']['users:login:say:error'] = 'Your username and password did not match. Please try again.';

// signup
$lang['*']['users:signup:head-title']	= 'Register a new account';
$lang['*']['signup:title']				= 'Create Account';
$lang['*']['signup:signup_link']		= 'Register';
$lang['*']['signup:first_name']			= 'First Name';
$lang['*']['signup:last_name']			= 'Last Name';
$lang['*']['signup:username']			= 'Username';
$lang['*']['signup:email']				= 'E-mail';
$lang['*']['signup:password']			= 'Password';
$lang['*']['signup:password_confirm']	= 'Confirm Password';
$lang['*']['signup:submit']				= 'Create Account';
$lang['*']['signup:account_success']	= 'Your account has been created successfully. Please login using your registered E-mail address and password.';
$lang['*']['signup:email:subject']		= 'Welcome to %s - Here\'s Your  Account Details';
$lang['*']['signup:email:body']			= 'Welcome to {app} {first_name}!<br /><br />Please login at {url} using either your username ({username}) or your email address ({email}), and the password you created during signup.<br /><br />If you forget your password, please use the forgotten password form at {forgot} to reset it.<br /><br />Thank you!';

// forgotten password
$lang['*']['users:forgot:head-title'] = 'Reset Your Password';
$lang['*']['forgot:title'] 			= 'Reset Your Password';
$lang['*']['forgot:email']			= 'Email';
$lang['*']['forgot:button'] 		= 'Send New Password';
$lang['*']['forgot:login'] 			= 'Back to Login';
$lang['*']['users:forgot:say:success']= 'Your password has been reset. Please check your email.';
$lang['*']['users:forgot:say:error']= 'We were unable to find any accounts matching that email.';

// my account
$lang['*']['users:my_account:head-title']	= 'My Account';
$lang['*']['myaccount:title']				= 'My Account';
$lang['*']['myaccount:help']				= 'Enter in your password twice to change it.';
$lang['*']['myaccount:label:password'] 		= 'Password';
$lang['*']['myaccount:label:password_2'] 	= 'Confirm';
$lang['*']['myaccount:button:submit'] 		= 'Save Changes';
$lang['*']['users:myaccount:say:success']= 'Your account has been updated successfully.';

// user list
$lang['*']['users:view:head-title']		= 'Users';
$lang['*']['userlist:title'] 			= 'User Accounts';
$lang['*']['userlist:adduser'] 			= 'Create New User Account';
$lang['*']['userlist:th:name'] 			= 'Name';
$lang['*']['userlist:th:email']			= 'Email';
$lang['*']['userlist:th:latestlogin']	= 'Latest Login';
$lang['*']['userlist:th:groups']		= 'User Groups';
$lang['*']['userlist:noresults'] 		= 'No user accounts were found.';

// user add/edit form
$lang['*']['users:add:head-title']  = 'Add User';
$lang['*']['users:edit:head-title'] = 'Edit User';
$lang['*']['userform:addtitle'] 	= 'Create Account';
$lang['*']['userform:edittitle'] 	= 'Update Account';
$lang['*']['userform:delete_link'] 	= 'Delete Account';
$lang['*']['userform:delete_warn'] 	= 'Are you sure you want to delete this user account?';
$lang['*']['userform:profile'] 		= 'Profile';
$lang['*']['userform:email']		= 'Email';
$lang['*']['userform:username']		= 'Username';
$lang['*']['userform:first_name'] 	= 'First Name';
$lang['*']['userform:last_name'] 	= 'Last Name';
$lang['*']['userform:password'] 	= 'Password';
$lang['*']['userform:confirm'] 		= 'Confirm';
$lang['*']['userform:allowlogin'] 	= 'Allow Login';
$lang['*']['userform:accessgroup'] 	= 'Access Groups';
$lang['*']['userform:addbutton'] 	= 'Create New User';
$lang['*']['userform:editbutton'] 	= 'Save Changes';
$lang['*']['users:edit:say:success']= 'User account changes have been saved successfully.';

// delete
$lang['*']['users:delete:say:success']= 'User account has been deleted successfully.';

// permission denied
$lang['*']['users:denied:head-title'] = 'Permission Denied';
$lang['*']['denied:title'] 	= 'Permission Denied';
$lang['*']['denied:intro'] 	= 'Your user account does not currently have sufficient permission to access this page. Please contact an *istrator for assistance.';

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
$lang['*']['db:error:groups-no*']	= 'You may not remove your own *istrator status.';
?>