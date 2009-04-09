<?php

/**
 * Language translations
 */

// prerequisites
$lang['admin']['s0:title'] = 'Install ' . $this->APP->config('application_name') . ' - Prerequisites';
$lang['admin']['s0:intro'] = 'We have identified some software or settings that may not be setup properly, or are not in the recommended configuration. Use this page to determine what issues there may be.';
$lang['admin']['s0:phpvers'] = 'Your PHP Version';
$lang['admin']['s0:mysqlloaded'] = 'MySQL Loaded';
$lang['admin']['s0:refresh'] = 'Refresh';

// step one - setting up config
$lang['admin']['s1:title'] = 'Install ' . $this->APP->config('application_name') . ' - Step One';
$lang['admin']['s1:intro'] = 'Enter in the username and password required to connect to your MySQL database. We\'ll also need the database name, and hostname (typically "localhost").';
$lang['admin']['s1:user'] = 'Username';
$lang['admin']['s1:pass'] = 'Password';
$lang['admin']['s1:database'] = 'Database';
$lang['admin']['s1:host'] = 'Hostname';
$lang['admin']['s1:submit'] = 'Continue to Step Two';

// create config file manually
$lang['admin']['s1b:title'] = 'Install ' . $this->APP->config('application_name') . ' - Create Configuation File';
$lang['admin']['s1b:intro'] = 'We were unable to automatically create a configuration file. Please copy and paste the following content into a plain text file called "config.php" and upload it to the root directory of the framework. Afterwards, click to continue.';
$lang['admin']['s1b:submit'] = 'Continue';

// setup user account
$lang['admin']['s2:title'] = 'Install ' . $this->APP->config('application_name') . ' - Step Two';
$lang['admin']['s2:intro'] = 'Enter in the administrator account information and click Next to complete.';
$lang['admin']['s2:email'] = 'E-mail';
$lang['admin']['s2:name'] = 'Your Name';
$lang['admin']['s2:password'] = 'Password';
$lang['admin']['s2:confirm'] = 'Confirm';
$lang['admin']['s2:submit'] = 'Create Account';

// success
$lang['admin']['success:title'] = 'Install ' . $this->APP->config('application_name') . ' - Success';
$lang['admin']['success:intro'] = 'The installation process has been completed successfully. You may now begin using your application.';
$lang['admin']['success:link'] = 'Begin using ' . $this->APP->config('application_name');

// upgrade
$lang['admin']['upgrade:title'] = 'Database Upgrade Required';
$lang['admin']['upgrade:intro'] = 'We need to upgrade your database before you may continue using this application. Please click the link below to update it automatically.';
$lang['admin']['upgrade:link'] = 'Upgrade My Database';

?>