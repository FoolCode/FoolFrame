<?php
/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2011 Fuel Development Team
 * @link       http://fuelphp.com
 */

/**
 * NOTICE:
 *
 * If you need to make modifications to the default configuration, copy
 * this file to your app/config folder, and make them in there.
 *
 * This will allow you to upgrade fuel without losing your custom config.
 */

return [
    /**
     * DB connection, leave null to use default
     */
    'db_connection' => null,

    /**
     * DB table name for the user table
     */
    'table_name' => 'users',

    /**
     * DB table name for the autologin table
     */
    'table_autologin_name' => 'user_autologin',

    /**
     * DB table name for the login attempts table
     */
    'table_login_attempts_name' => 'user_login_attempts',

    /**
     * Choose which columns are selected, must include: username, password, email, last_login,
     * login_hash, group & profile_fields
     */
    'table_columns' => ['*'],

    /**
     * This will allow you to use the group & acl driver for non-logged in users
     */
    'guest_login' => true,

    /**
     * Groups as id => [name => <string>, roles => <array>]
     */
    'groups' => [
        0    => ['name' => 'Guest', 'roles' => []],
        1    => ['name' => 'User', 'roles' => ['user']],
        50   => ['name' => 'Moderator', 'roles' => ['mod']],
        100  => ['name' => 'Administrator', 'roles' => ['admin']],
    ],

    /**
     * Roles as name => [location => rights]
     */
    'roles' => [
        'user' => [
            'access'  => ['user', 'member'],
            'maccess' => ['user']
        ],
        'mod' => [
            'access'  => ['mod'],
            'maccess' => ['user', 'mod'],
            'users'   => ['access']
        ],
        'admin' => [
            'access'  => ['admin'],
            'maccess' => ['user', 'mod', 'admin'],
            'users'   => ['access', 'change_credentials', 'change_group']
        ],
    ],

    /**
     * Salt for the login hash
     */
    'login_hash_salt' => 'put_some_salt_in_here',

    'salt' => 'salt_free',

    /**
     * $_POST key for login username
     */
    'username_post_key' => 'username',

    /**
     * $_POST key for login password
     */
    'password_post_key' => 'password',

    /**
     * The amount of tries before an account is locked
     */
    'attempts_to_lock' => 10
];
