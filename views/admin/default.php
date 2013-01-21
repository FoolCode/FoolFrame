<!DOCTYPE html>
<html>
	<head>
		<title><?= Preferences::get('fu.gen.website_title').' '. __('Control Panel') ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

		<link rel="stylesheet" type="text/css" href="<?= \Uri::base().'assets/bootstrap2/css/bootstrap.min.css?v='.\Foolz\Config\Config::get('foolz/foolframe', 'package', 'main.version') ?>" />
		<link rel="stylesheet" type="text/css" href="<?= \Uri::base().'assets/admin/admin.css?v='.\Foolz\Config\Config::get('foolz/foolframe', 'package', 'main.version') ?>" />
		<script type="text/javascript" src="<?= \Uri::base().'assets/js/jquery.js?v='. \Foolz\Config\Config::get('foolz/foolframe', 'package', 'main.version') ?>"></script>
		<script type="text/javascript" src="<?= \Uri::base().'assets/bootstrap2/js/bootstrap.js?v='.\Foolz\Config\Config::get('foolz/foolframe', 'package', 'main.version') ?>"></script>
		<link rel="stylesheet" type="text/css" href="<?= \Uri::base().'assets/font-awesome/css/font-awesome.css?v='.\Foolz\Config\Config::get('foolz/foolframe', 'package', 'main.version') ?>" />
		<!--[if lt IE 8]>
			<link href="<?= \Uri::base().'assets/font-awesome/css/font-awesome-ie7.css?v='.\Foolz\Config\Config::get('foolz/foolframe', 'package', 'main.version') ?>" rel="stylesheet" type="text/css" />
		<![endif]-->
		<script type="text/javascript" src="<?= \Uri::base().'assets/admin/admin.js?v='.\Foolz\Config\Config::get('foolz/foolframe', 'package', 'main.version') ?>"></script>
	</head>

	<body>

		<?= $navbar ?>

		<div class="container-fluid">
			<div class="row-fluid">
				<div style="width:16%" class="pull-left">
					<?= $sidebar ?>
				</div>

				<div style="width:82%" class="pull-right">

					<ul class="breadcrumb">
						<li><?= $controller_title ?></li>

						<?php
						if (isset($method_title) && is_array($method_title))
						{
							$count = 1;
							$total = count($method_title);
							foreach ($method_title as $title)
							{
								echo ' <span class="divider">/</span> ';

								if ($count == $total)
								{
									echo '<li class="active">'.$title.'</li>';
								}
								else
								{
									echo '<li>'.$title.'</li>';
								}

								$count++;
							}
						}
						elseif (isset($method_title))
						{
							echo ' <span class="divider">/</span> <li class="active">'.$method_title.'</li>';
						}
						?>
					</ul>

					<div class="alerts">
						<?php $notices = array_merge(\Notices::get(), \Notices::flash()); ?>
						<?php foreach ($notices as $notice) : ?>
							<div class="alert alert-"<?= $notice['level'] ?>">
								<?php if (is_array($notice['message'])) : ?>
									<ul>
										<?php foreach ($notice['message'] as $message) : ?>
											<li><?= htmlentities($message, ENT_COMPAT | ENT_IGNORE, 'UTF-8') ?></li>
										<?php endforeach; ?>
									</ul>
								<?php else : ?>
									<?= htmlentities($notice['message'], ENT_COMPAT | ENT_IGNORE, 'UTF-8') ?>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>

					<?php if (isset($main_content_view)) : ?>
						<?= $main_content_view ?>
					<?php endif; ?>

					<footer class="footer">
						<p style="padding-left: 20px;">
							<?= \Foolz\Config\Config::get('foolz/foolframe', 'package', 'main.name') ?> Version <?= \Foolz\Config\Config::get('foolz/foolframe', 'package', 'main.version') ?>
							<?php if (\Auth::member('admin') && (\Foolz\Config\Config::get('foolz/foolframe', 'package', 'main.version') != \Preferences::get('ff.cron.latest_version') && \Preferences::get('ff.cron.latest_version'))) : ?>
								- <a href="<?= Uri::create('admin/system/upgrade') ?>"><?= \Str::tr(__('New Version Available: :version'), array('version' => \Preferences::get('ff.cron.latest_version'))) ?></a>
							<?php endif; ?>
						</p>
					</footer>
				</div>
				<div style="clear:both"></div>
			</div>
		</div>

		<?= \Security::js_set_token(); ?>
	</body>
</html>