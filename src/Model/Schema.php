<?php

namespace Foolz\FoolFrame\Model;

class Schema
{
    public static function load(Context $context, SchemaManager $sm)
    {
        // can't use $context connection here because it's not properly setup with prefix
        $dc = $sm->getConnection();
        $pf = $sm->getPrefix();

        $charset = 'utf8mb4';
        $collate = 'utf8mb4_unicode_ci';

        $schema = $sm->getCodedSchema();

        $plugins = $schema->createTable($pf.'plugins');
        $plugins->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $plugins->addColumn('slug', 'string', ['length' => 65]);
        $plugins->addColumn('enabled', 'boolean');
        $plugins->addColumn('revision', 'integer', ['unsigned' => true, 'notnull' => false, 'default' => null]);
        $plugins->setPrimaryKey(['id']);
        $plugins->addUniqueIndex(['slug'], 'slug_index');

        $preferences = $schema->createTable($pf.'preferences');
        if ($dc->getDriver()->getName() == 'pdo_mysql') {
            $preferences->addOption('charset', $charset);
            $preferences->addOption('collate', $collate);
        }
        $preferences->addColumn('name', 'string', ['length' => 64]);
        $preferences->addColumn('value', 'text', ['length' => 65532, 'notnull' => false]);
        $preferences->setPrimaryKey(['name']);

        $users = $schema->createTable($pf.'users');
        if ($dc->getDriver()->getName() == 'pdo_mysql') {
            $users->addOption('charset', $charset);
            $users->addOption('collate', $collate);
        }
        $users->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $users->addColumn('username', 'string', ['length' => 32]);
        $users->addColumn('password', 'string', ['length' => 255]);
        $users->addColumn('group_id', 'integer', ['unsigned' => true]);
        $users->addColumn('email', 'string', ['length' => 100]);
        $users->addColumn('last_login', 'integer', ['unsigned' => true, 'notnull' => false]);
        $users->addColumn('new_email', 'string', ['length' => 100, 'notnull' => false, 'default' => null]);
        $users->addColumn('new_email_key', 'string', ['length' => 128, 'notnull' => false, 'default' => null]);
        $users->addColumn('new_email_time', 'integer', ['unsigned' => true, 'notnull' => false, 'default' => null]);
        $users->addColumn('activated', 'boolean');
        $users->addColumn('activation_key', 'string', ['length' => 128, 'notnull' => false, 'default' => null]);
        $users->addColumn('new_password_key', 'string', ['length' => 128, 'notnull' => false, 'default' => null]);
        $users->addColumn('new_password_time', 'integer', ['unsigned' => true, 'notnull' => false, 'default' => null]);
        $users->addColumn('deletion_key', 'string', ['length' => 128, 'notnull' => false, 'default' => null]);
        $users->addColumn('deletion_time', 'integer', ['unsigned' => true, 'notnull' => false, 'default' => null]);
        $users->addColumn('profile_fields', 'text', ['length' => 65532]);
        $users->addColumn('bio', 'text', ['length' => 65532, 'notnull' => false]);
        $users->addColumn('twitter', 'string', ['length' => 32, 'notnull' => false]);
        $users->addColumn('display_name', 'string', ['length' => 32, 'notnull' => false]);
        $users->addColumn('created_at', 'integer', ['unsigned' => true]);
        $users->setPrimaryKey(['id']);
        $users->addUniqueIndex(['username', 'email'], 'username_email_index');

        $user_autologin = $schema->createTable($pf.'user_autologin');
        $user_autologin->addColumn('user_id', 'integer', ['unsigned' => true]);
        $user_autologin->addColumn('login_id', 'string', ['length' => 255]);
        $user_autologin->addColumn('login_hash', 'string', ['length' => 255]);
        $user_autologin->addColumn('expiration', 'integer', ['unsigned' => true]);
        $user_autologin->addColumn('last_ip', 'decimal', ['precision' => 39, 'scale' => 0]);
        $user_autologin->addColumn('user_agent', 'string', ['length' => 150]);
        $user_autologin->addColumn('last_login', 'integer', ['unsigned' => true]);
        $user_autologin->setPrimaryKey(['login_hash']);
        $user_autologin->addIndex(['user_id'], 'user_id_index');
        $user_autologin->addUniqueIndex(['user_id', 'login_id', 'login_hash'], 'user_id_login_id_login_hash_index');

        $user_login_attempts = $schema->createTable($pf.'user_login_attempts');
        $user_login_attempts->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $user_login_attempts->addColumn('username', 'string', ['length' => 32]);
        $user_login_attempts->addColumn('time', 'integer', ['unsigned' => true]);
        $user_login_attempts->addColumn('ip', 'decimal', ['precision' => 39, 'scale' => 0]);
        $user_login_attempts->setPrimaryKey(['id']);
        $user_login_attempts->addIndex(['username', 'time'], 'username_time_index');
    }
}
