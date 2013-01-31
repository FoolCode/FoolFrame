<?php
$info = [];

$info['server'] = [
	'title' => __('Server Information'),
	'data' => [
		[
			'title' => __('Web Server Software'),
			'value' => $_SERVER['SERVER_SOFTWARE']
		],
		[
			'title' => __('PHP Version'),
			'value' => PHP_VERSION,
			'alert' => [
				'type' => 'important',
				'condition' => (version_compare(PHP_VERSION, '5.4.0') > 0),
				'title' => __('Please Update Immediately'),
				'string' => __('The minimum requirements to run this software is 5.4.0.')
			]
		]
	]
];

$info['software'] = [
	'title' => _('Software Information'),
	'data' => [
		[
			'title' => __('FoolFrame Version'),
			'value' => \Foolz\Config\Config::get('foolz/foolframe', 'package', 'main.version'),
			'alert' => [
				'type' => 'info',
				'condition' => false,
				'title' => __('New Update Available'),
				'string' => __('There is a new version of the software available for download.')
			]
		],
		[
			'title' => __('FoolFuuka Version'),
			'value' => \Foolz\Config\Config::get('foolz/foolfuuka', 'package', 'main.version'),
			'alert' => [
				'type' => 'info',
				'condition' => true,
				'title' => __('New Update Available'),
				'string' => __('There is a new version of the software available for download.')
			]
		]
	]
];

$info['php-configuration'] = [
	'title' => __('PHP Configuration'),
	'data' => [
		[
			'title' => _('Config Location'),
			'value' => php_ini_loaded_file(),
			'description' => __('This is the path to the location of the php.ini configuration file.')
		],
		[
			'title' => 'allow_url_fopen',
			'value' => (ini_get('allow_url_fopen') ? __('On') : __('Off')),
			'description' => __('This option enables the URL-aware fopen wrappers that allows access to remote files using the FTP or HTTP protocol.'),
			'alert' => [
				'type' => 'important',
				'condition' => (bool) ! ini_get('allow_url_fopen'),
				'title' => __('Critical'),
				'string' => __('The PHP configuration on the server currently has URL-aware fopen wrappers disabled. The software will be operating at limited functionality.')
			]
		],
		[
			'title' => 'max_execution_time',
			'value' => ini_get('max_execution_time'),
			'description' => __('This sets the maximum time in seconds a script is allowed to run before it is terminated by the parser.'),
			'alert' => [
				'type' => 'warning',
				'condition' => (bool) (intval(ini_get('max_execution_time')) < 60),
				'title' => __('Warning'),
				'string' => __('Your current value for maximum execution time is below the suggested value.')
			]
		],
		[
			'title' => 'file_uploads',
			'value' => (ini_get('file_uploads') ? __('On') : __('Off')),
			'description' => __('This sets whether or not to allow HTTP file uploads.'),
			'alert' => [
				'type' => 'important',
				'condition' => (bool) ! ini_get('file_uploads'),
				'title' => __('Critical'),
				'string' => __('The PHP configuration on the server currently has file uploads disabled. This option must be enabled for the software to fully function.')
			]
		],
		[
			'title' => 'post_max_size',
			'value' => ini_get('post_max_size'),
			'description' => __('This sets the maximum size of POST data allowed.'),
			'alert' => [
				'type' => 'warning',
				'condition' => (bool) (intval(substr(ini_get('post_max_size'), 0, -1)) < 16),
				'title' => __('Warning'),
				'string' => __('Your current value for maximum POST data size is below the suggested value.')
			]
		],
		[
			'title' => 'upload_max_filesize',
			'value' => ini_get('upload_max_filesize'),
			'description' => __('This sets the maximum size allowed to be uploaded.'),
			'alert' => [
				'type' => 'warning',
				'condition' => (bool) (intval(substr(ini_get('upload_max_filesize'), 0, -1)) < 16),
				'title' => __('Warning'),
				'string' => __('Your current value for maximum upload file size is below the suggested value.')
			]
		],
		[
			'title' => 'max_file_uploads',
			'value' => ini_get('max_file_uploads'),
			'description' => __('This sets the maximum number of files allowed to be uploaded concurrently.'),
			'alert' => [
				'type' => 'warning',
				'condition' => (bool) (intval(ini_get('max_file_uploads')) < 60),
				'title' => __('Warning'),
				'string' => __('Your current value for maximum number of concurrent uploads is below the suggested value.')
			]
		]
	]
];

$info['php-extensions'] = [
	'title' => __('PHP Extensions'),
	'data' => [
		[
			'title' => 'cURL',
			'value' => (extension_loaded('curl') ? __('Installed') : __('Unavailable')),
			'alert' => [
				'type' => 'important',
				'condition' => (bool) ! extension_loaded('curl'),
				'title' => __('Critical'),
				'string' => __('Your PHP environment shows that you do not have cURL installed. This will limited the functionality of the software.')
			]
		],
		[
			'title' => 'GD2',
			'value' => (extension_loaded('gd') ? __('Installed') : __('Unavailable')),
			'alert' => [
				'type' => 'warning',
				'condition' => (bool) ! extension_loaded('gd'),
				'title' => __('Warning'),
				'string' => __('Your PHP environment shows that you do not have GD2 installed. This will limited the functionality of the software.')
			]
		],
		[
			'title' => 'ImageMagick',
			'value' => (false ? __('Installed') : __('Unavailable')),
			'alert' => [
				'type' => 'warning',
				'condition' => (bool) true,
				'title' => __('Warning'),
				'string' => __('Your PHP environment shows that you do not have ImageMagick installed. This will limited the functionality of the software.')
			]
		]
	]
];
?>

<?php foreach ($info as $key => $item) : ?>
<div class="admin-container">
	<div class="admin-container-header" id="<?= $key ?>"><?= $item['title'] ?></div>
	<table class="table table-hover table-condensed">
		<thead>
			<tr>
				<th class="span6"></th>
				<th class="span6"><?= __('Value') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($item['data'] as $k => $i) : ?>
			<tr>
				<td>
					<?php if (isset($i['description'])) : ?>
					<span data-placement="bottom" title="<?= htmlspecialchars($i['description']) ?>">
						<?= $i['title'] ?>
					</span>
					<?php else : ?>
					<?= $i['title'] ?>
					<?php endif; ?>

					<span class="pull-right">
						<?php if (isset($i['alert']) && $i['alert']['condition'] === false) : ?>
							<i class="icon-ok text-success"></i>
						<?php elseif (isset($i['alert']) && $i['alert']['condition'] === true) : ?>
							<a href="#<?= $key ?>" rel="popover" data-placement="right" data-trigger="hover" data-title="<?= htmlspecialchars(__($i['alert']['title'])) ?>" data-content="<?= htmlspecialchars(__($i['alert']['string'])) ?>">
								<?php if ($i['alert']['type'] == 'info') : ?>
									<i class="icon-exclamation-sign text-info"></i>
								<?php elseif ($i['alert']['type'] == 'warning') : ?>
									<i class="icon-warning-sign text-warning"></i>
								<?php elseif ($i['alert']['type'] == 'important') : ?>
									<i class="icon-remove text-error"></i>
								<?php endif; ?>
							</a>
						<?php endif; ?>
					</span>
				</td>
				<td><?= $i['value'] ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
<?php endforeach; ?>