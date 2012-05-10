<h2><?= text('index:view:page-title'); ?></h2>
<?= sml()->printMessage(); ?>
<p><em><?= text('index:view:intro'); ?></em></p>

<?= user()->isLoggedIn() ? 'Is Logged In' : 'Are Not Logged In'; ?>