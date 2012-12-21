<?php 
return array (
  'db_connection' => NULL,
  'table_name' => 'users',
  'table_autologin_name' => 'user_autologin',
  'table_login_attempts_name' => 'user_login_attempts',
  'table_columns' => 
  array (
    0 => '*',
  ),
  'guest_login' => true,
  'groups' => 
  array (
    0 => 
    array (
      'name' => 'Guests',
      'roles' => 
      array (
      ),
    ),
    1 => 
    array (
      'name' => 'Users',
      'roles' => 
      array (
        0 => 'user',
      ),
    ),
    50 => 
    array (
      'name' => 'Moderators',
      'roles' => 
      array (
        0 => 'mod',
      ),
    ),
    100 => 
    array (
      'name' => 'Administrators',
      'roles' => 
      array (
        0 => 'admin',
      ),
    ),
  ),
  'roles' => 
  array (
    'user' => 
    array (
      'access' => 
      array (
        0 => 'user',
        1 => 'member',
      ),
      'maccess' => 
      array (
        0 => 'user',
      ),
    ),
    'mod' => 
    array (
      'access' => 
      array (
        0 => 'mod',
      ),
      'maccess' => 
      array (
        0 => 'user',
        1 => 'mod',
      ),
      'users' => 
      array (
        0 => 'access',
      ),
    ),
    'admin' => 
    array (
      'access' => 
      array (
        0 => 'admin',
      ),
      'maccess' => 
      array (
        0 => 'user',
        1 => 'mod',
        2 => 'admin',
      ),
      'users' => 
      array (
        0 => 'access',
        1 => 'change_credentials',
        2 => 'change_group',
      ),
    ),
  ),
  'login_hash_salt' => 'zS0ccJzcPXVCnptGTXoVXnBT',
  'salt' => 'N7m1sqmLYvJrBQdXTNKEJXfu',
  'username_post_key' => 'username',
  'password_post_key' => 'password',
  'attempts_to_lock' => 10,
);