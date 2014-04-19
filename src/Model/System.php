<?php

namespace Foolz\Foolframe\Model;

class System
{
    public static function environment()
    {
        $environment = [];

        $environment['server'] = [
            'title' => _i('Server Information'),
            'data' => [
                [
                    'title' => _i('Web Server Software'),
                    'value' => $_SERVER['SERVER_SOFTWARE'],
                    'alert' => [
                        'type' => 'warning',
                        'condition' => (bool) preg_match('/nginx/i', $_SERVER['SERVER_SOFTWARE']),
                        'title' => 'Warning',
                        'string' => _i('The nginx web server has its own internal file size limit variable for uploads. It is recommended that this value be set at the same value set in the PHP configuration file.')
                    ]
                ],
                [
                    'title' => _i('PHP Version'),
                    'value' => PHP_VERSION,
                    'alert' => [
                        'type' => 'important',
                        'condition' => (version_compare(PHP_VERSION, '5.4.0') < 0),
                        'title' => _i('Please Update Immediately'),
                        'string' => _i('The minimum requirements to run this software is 5.4.0.')
                    ]
                ]
            ]
        ];

        $environment['software'] = [
            'title' => _i('Software Information'),
            'data' => [
                [
                    'title' => _i('FoolFrame Version'),
                    'value' => Legacy\Config::get('foolz/foolframe', 'package', 'main.version'),
                    'alert' => [
                        'type' => 'info',
                        'condition' => true,
                        'title' => _i('New Update Available'),
                        'string' => _i('There is a new version of the software available for download.')
                    ]
                ]
            ]
        ];

        $environment['php-configuration'] = [
            'title' => _i('PHP Configuration'),
            'data' => [
                [
                    'title' => _i('Config Location'),
                    'value' => php_ini_loaded_file(),
                    'description' => _i('This is the path to the location of the php.ini configuration file.')
                ],
                [
                    'title' => 'allow_url_fopen',
                    'value' => (ini_get('allow_url_fopen') ? _i('On') : _i('Off')),
                    'description' => _i('This option enables the URL-aware fopen wrappers that allows access to remote files using the FTP or HTTP protocol.'),
                    'alert' => [
                        'type' => 'important',
                        'condition' => (bool) ! ini_get('allow_url_fopen'),
                        'title' => _i('Critical'),
                        'string' => _i('The PHP configuration on the server currently has URL-aware fopen wrappers disabled. The software will be operating at limited functionality.')
                    ]
                ],
                [
                    'title' => 'max_execution_time',
                    'value' => ini_get('max_execution_time'),
                    'description' => _i('This sets the maximum time in seconds a script is allowed to run before it is terminated by the parser.'),
                    'alert' => [
                        'type' => 'warning',
                        'condition' => (bool) (intval(ini_get('max_execution_time')) < 60),
                        'title' => _i('Warning'),
                        'string' => _i('Your current value for maximum execution time is below the suggested value.')
                    ]
                ],
                [
                    'title' => 'file_uploads',
                    'value' => (ini_get('file_uploads') ? _i('On') : _i('Off')),
                    'description' => _i('This sets whether or not to allow HTTP file uploads.'),
                    'alert' => [
                        'type' => 'important',
                        'condition' => (bool) ! ini_get('file_uploads'),
                        'title' => _i('Critical'),
                        'string' => _i('The PHP configuration on the server currently has file uploads disabled. This option must be enabled for the software to fully function.')
                    ]
                ],
                [
                    'title' => 'post_max_size',
                    'value' => ini_get('post_max_size'),
                    'description' => _i('This sets the maximum size of POST data allowed.'),
                    'alert' => [
                        'type' => 'warning',
                        'condition' => (bool) (intval(substr(ini_get('post_max_size'), 0, -1)) < 16),
                        'title' => _i('Warning'),
                        'string' => _i('Your current value for maximum POST data size is below the suggested value.')
                    ]
                ],
                [
                    'title' => 'upload_max_filesize',
                    'value' => ini_get('upload_max_filesize'),
                    'description' => _i('This sets the maximum size allowed to be uploaded.'),
                    'alert' => [
                        'type' => 'warning',
                        'condition' => (bool) (intval(substr(ini_get('upload_max_filesize'), 0, -1)) < 16),
                        'title' => _i('Warning'),
                        'string' => _i('Your current value for maximum upload file size is below the suggested value.')
                    ]
                ],
                [
                    'title' => 'max_file_uploads',
                    'value' => ini_get('max_file_uploads'),
                    'description' => _i('This sets the maximum number of files allowed to be uploaded concurrently.'),
                    'alert' => [
                        'type' => 'warning',
                        'condition' => (bool) (intval(ini_get('max_file_uploads')) < 60),
                        'title' => _i('Warning'),
                        'string' => _i('Your current value for maximum number of concurrent uploads is below the suggested value.')
                    ]
                ]
            ]
        ];

        $environment['php-extensions'] = [
            'title' => _i('PHP Extensions'),
            'data' => [
                [
                    'title' => 'APC',
                    'value' => (extension_loaded('apc') ? _i('Installed') : _i('Unavailable')),
                    'alert' => [
                        'type' => 'warning',
                        'condition' => (bool) ! extension_loaded('apc'),
                        'title' => _i('Warning'),
                        'string' => _i('Your PHP environment shows that you do not have the "%s" extension installed. This may limit the functionality of the software.', 'APC'	)
                    ]
                ],
                [
                    'title' => 'cURL',
                    'value' => (extension_loaded('curl') ? _i('Installed') : _i('Unavailable')),
                    'alert' => [
                        'type' => 'important',
                        'condition' => (bool) ! extension_loaded('curl'),
                        'title' => _i('Critical'),
                        'string' => _i('Your PHP environment shows that you do not have the "%s" extension installed. This may limit the functionality of the software.', 'cURL')
                    ]
                ],
                [
                    'title' => 'FileInfo',
                    'value' => (extension_loaded('fileinfo') ? _i('Installed') : _i('Unavailable')),
                    'alert' => [
                        'type' => 'important',
                        'condition' => (bool) ! extension_loaded('fileinfo'),
                        'title' => _i('Critical'),
                        'string' => _i('Your PHP environment shows that you do not have the "%s" extension installed. This may limit the functionality of the software.', 'FileInfo')
                    ]
                ],
                [
                    'title' => 'JSON',
                    'value' => (extension_loaded('json') ? _i('Installed') : _i('Unavailable')),
                    'alert' => [
                        'type' => 'important',
                        'condition' => (bool) ! extension_loaded('json'),
                        'title' => _i('Critical'),
                        'string' => _i('Your PHP environment shows that you do not have the "%s" extension installed. This may limit the functionality of the software.', 'JSON')
                    ]
                ],
                [
                    'title' => 'Multi-byte String',
                    'value' => (extension_loaded('mbstring') ? _i('Installed') : _i('Unavailable')),
                    'alert' => [
                        'type' => 'important',
                        'condition' => (bool) ! extension_loaded('mbstring'),
                        'title' => _i('Critical'),
                        'string' => _i('Your PHP environment shows that you do not have the "%s" extension installed. This may limit the functionality of the software.', 'Multi-byte String')
                    ]
                ],
                [
                    'title' => 'MySQLi',
                    'value' => (extension_loaded('mysqli') ? _i('Installed') : _i('Unavailable')),
                    'alert' => [
                        'type' => 'important',
                        'condition' => (bool) ! extension_loaded('mysqli'),
                        'title' => _i('Critical'),
                        'string' => _i('Your PHP environment shows that you do not have the "%s" extension installed. This may limit the functionality of the software.', 'MySQLi')
                    ]
                ],
                [
                    'title' => 'PDO MySQL',
                    'value' => (extension_loaded('pdo_mysql') ? _i('Installed') : _i('Unavailable')),
                    'alert' => [
                        'type' => 'important',
                        'condition' => (bool) ! extension_loaded('pdo_mysql'),
                        'title' => _i('Critical'),
                        'string' => _i('Your PHP environment shows that you do not have the "%s" extension installed. This may limit the functionality of the software.', 'PDO MySQL')
                    ]
                ]
            ]
        ];

        $environment = \Foolz\Plugin\Hook::forge('Foolz\Foolframe\Model\System::environment.result')
            ->setParam('environment', $environment)
            ->execute()
            ->get($environment);

        usort($environment['php-extensions']['data'], array('System', 'sortByTitle'));

        return $environment;
    }

    public static function sortByTitle($a = [], $b = [])
    {
        return strcasecmp($a['title'], $b['title']);
    }
}
