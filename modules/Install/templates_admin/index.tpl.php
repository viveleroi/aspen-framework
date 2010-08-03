<h2><?php print text('s0:title'); ?></h2>

<p><?php print text('s0:intro'); ?></p>
<p><?php print text('s0:phpvers'); ?>: <?php print phpversion() ?> <?php print (version_compare(PHP_VERSION, app()->config('minimum_version_php'), '>=') ? '<span style="color: green">Ok</span>' : '<span style="color: red">Not Supported</span>') ?></p>
<p><?php print text('s0:mysqlloaded'); ?>: <?php print (extension_loaded('mysql') ? '<span style="color: green">Ok</span>' : '<span style="color: red">Not Supported</span>') ?></p>

<p><?php print $this->link( text('s0:refresh') ) ?></p>