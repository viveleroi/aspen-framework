<h2><?= text('index:view:page-title'); ?></h2>
<?= sml()->printMessage(); ?>
<p><em><?= text('index:view:intro'); ?></em></p>
<p><?= user()->isLoggedIn() ? 'Logged In' : 'Not Logged In' ?></p>