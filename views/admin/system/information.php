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
				'condition' => (version_compare(PHP_VERSION, '5.4.0') < 0),
				'string' => __('The minimum requirements to run the software is 5.4.0.')
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
				'string' => __('There is a new version of the software available for download.')
			]
		],
		[
			'title' => __('FoolFuuka Version'),
			'value' => \Foolz\Config\Config::get('foolz/foolfuuka', 'package', 'main.version'),
			'alert' => [
				'type' => 'info',
				'condition' => true,
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
				'string' => __('')
			]
		],
		[
			'title' => 'max_execution_time',
			'value' => ini_get('max_execution_time'),
			'description' => __('This sets the maximum time in seconds a script is allowed to run before it is terminated by the parser.'),
			'alert' => [
				'type' => 'warning',
				'condition' => (bool) (intval(ini_get('max_execution_time')) < 110),
				'string' => __('')
			]
		],
		[
			'title' => 'file_uploads',
			'value' => (ini_get('file_uploads') ? __('On') : __('Off')),
			'description' => __('This sets whether or not to allow HTTP file uploads.'),
			'alert' => [
				'type' => 'important',
				'condition' => (bool) ! ini_get('file_uploads'),
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
				'string' => __('')
			]
		],
		[
			'title' => 'upload_max_filesize',
			'value' => ini_get('upload_max_filesize'),
			'description' => __('This sets the maximum size allowed to be uploaded.'),
			'alert' => [
				'type' => 'warning',
				'condition' => (bool) (intval(substr(ini_get('upload_max_filesize'), 0, -1)) < 16),
				'string' => __('')
			]
		],
		[
			'title' => 'max_file_uploads',
			'value' => ini_get('max_file_uploads'),
			'description' => __('This sets the maximum number of files allowed to be uploaded concurrently.'),
			'alert' => [
				'type' => 'warning',
				'condition' => (bool) (intval(ini_get('max_file_uploads')) < 60),
				'string' => __('')
			]
		]
	]
];

$info['php-extensions'] = [
	'title' => __('PHP Extensions'),
	'data' => [
		[
			'title' => 'cURL',
			'value' => (extension_loaded('curl') ? __('Installed') : __('Unavailable'))
		],
		[
			'title' => 'GD2',
			'value' => (extension_loaded('gd') ? __('Installed') : __('Unavailable'))
		],
		[
			'title' => 'ImageMagick',
			'value' => (extension_loaded('gd') ? __('Installed') : __('Unavailable'))
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
				<th></th>
				<th><?= __('Value') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($item['data'] as $k => $i) : ?>
			<tr>
				<td class="span6">
					<?= $i['title'] ?>

					<span class="pull-right">
						<?php if (isset($i['alert']) && $i['alert']['condition'] === false) : ?>
							<i class="icon-ok text-success"></i>
						<?php elseif (isset($i['alert']) && $i['alert']['condition'] === true) : ?>
							<a href="#<?= $key ?>" rel="popover-right" data-content="<?= htmlspecialchars($i['alert']['string']) ?>">
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
				<td class="span6"><?= $i['value'] ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
<?php endforeach; ?>