<?php
return array (
  'main' =>
  array (
    'version' => '0.1-dev-0',
    'name' => 'FoolFrame',
    'identifier' => 'ff',
    'class_name' => 'Foolframe',
    'git_tags_url' => 'https://api.github.com/repos/foolrulez/foolfuuka/tags',
    'git_changelog_url' => 'https://raw.github.com/foolrulez/FoOlFuuka/master/CHANGELOG.md',
  ),
  'install' =>
  array (
    'installed' => true,
    'requirements' =>
    array (
      'min_php_version' => '5.3.0',
      'min_mysql_version' => '5.5.0',
    ),
  ),
  'directories' =>
  array (
    'themes' => '/vagrant/fuelphp/FoolFrame/public/foolframe/themes/',
    'plugins' => '/vagrant/fuelphp/FoolFrame/public/foolframe/plugins/',
  ),
  'config' =>
  array (
    'cookie_prefix' => 'foolframe_Ega_',
  ),
  'modules' =>
  array (
    'installed' =>
    array (
      'ff' => 'foolz/foolframe',
      'fp' => 'foolz/foolpod',
    ),
  ),
  'preferences' =>
  array (
    'gen' =>
    array (
      'website_title' => 'FoolFrame',
      'index_title' => 'FoolFrame',
    ),
    'lang' =>
    array (
      'default' => 'en_EN',
      'available' =>
      array (
        'en_EN' => 'English',
        'fr_FR' => 'French',
        'it_IT' => 'Italian',
        'pt_PT' => 'Portuguese',
      ),
    ),
  ),
);