<?php

namespace Foolz\Foolframe\Model\Auth;

use Foolz\Foolframe\Model\Context;
use Foolz\Foolframe\Model\DoctrineConnection;
use Foolz\Foolframe\Model\Model;
use Foolz\Foolframe\Model\Preferences;

class FoolUserUpdateException extends \Exception {}

class FoolUserWrongUsernameOrPassword extends \Exception {}
class FoolUserLimitExceeded extends \Exception {}
class FoolUserWrongPassword extends \Exception {}
class FoolUserWrongEmail extends \Exception {}
class FoolUserWrongKey extends \Exception {}
class FoolUserEmailExists extends \Exception {}

/**
 * FoolAuth basic login driver
 *
 * @package     Fuel
 * @subpackage  Auth
 */
class Auth extends Model
{
    /**
     * @var DoctrineConnection
     */
    protected $dc;

    /**
     * @var Preferences
     */
    protected $preferences;
    
    public function __construct(Context $context) {
        parent::__construct($context);

        $this->dc = $context->getService('doctrine');
        $this->preferences = $context->getService('doctrine');
    }
    
    /**
     * Checks how many attempts have been made to login
     *
     * @param  string $username the submitted username
     * @return int the amount of attempts before successful login
     */
    public function count_attempts($username)
    {
        return $this->dc->qb()
            ->select('COUNT(*) as count')
            ->from($this->dc->p('user_login_attempts'), 'lt')
            ->where('lt.username = :username')
            ->setParameter(':username', $username)
            ->execute()
            ->fetch()['count'];
    }


    /**
     * Reset attempts have been made to login
     *
     * @param  string $username the submitted username
     */
    public function reset_attempts($username)
    {
        $this->dc->qb()
            ->delete($this->dc->p('user_login_attempts'))
            ->where('username = :username')
            ->setParameter(':username', $username)
            ->execute();
    }

    /**
     * Create new user
     *
     * @param   string
     * @param   string
     * @param   string  must contain valid email address
     * @param   int     group id
     * @param   Array
     * @return  bool
     */
    public function createUser($username, $password, $email, $group = 1, Array $profile_fields = array())
    {
        $password = trim($password);
        $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);

        if (empty($username) or empty($password) or empty($email))
        {
            throw new FoolUserUpdateException('Username, password and email address can\'t be empty.', 1);
        }

        $same_users = $this->dc->qb()
            ->select('*')
            ->from($this->dc->p('users'), 'l')
            ->where('l.username = :username')
            ->orWhere('l.email = :email')
            ->setParameter(':username', $username)
            ->setParameter(':email', $email)
            ->execute()
            ->fetchAll();

        if (count($same_users) > 0)
        {
            if (in_array(strtolower($email), array_map('strtolower', current($same_users))))
            {
                throw new FoolUserUpdateException('Email address already exists', 2);
            }
            else
            {
                throw new FoolUserUpdateException('Username already exists', 3);
            }
        }

        $activated = (bool) $this->preferences->get('foolframe.auth.disable_registration_email');
        $activation_key = null;

        if ( ! $activated)
        {
            // get a string for validation email
            $activation_key = password_hash()$this->hash_password((string) \Str::random('sha1'));
        }

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        if ( ! $hashed_password)
        {
            throw new \FoolUserUpdateException('Issues hashing the password', 4);
        }

        $user = array(
            'username'        => (string) $username,
            'password'        => $hashed_password,
            'email'           => $email,
            'group_id'        => (int) $group,
            'activated'       => (int) $activated,
            'activation_key'  => $activation_key,
            'profile_fields'  => serialize($profile_fields),
            'created_at'      => \Date::forge()->get_timestamp()
        );

        $result = $this->dc->getConnection()->insert($this->dc->p('users'), $user);

        return ($result) ? array($this->dc->getConnection()->lastInsertId($this->dc->p('users_id_seq')), $activation_key) : false;
    }

    /**
     * Update the available columns for profile
     *
     * @param array $data
     * @return boolean
     */
    public function update_profile(Array $data)
    {
        // select only what we can insert
        $data = \Arr::filter_keys($data, array('bio', 'twitter', 'display_name'));

        $query = $this->dc->qb()
            ->update($this->dc->p('users'))
            ->where('id = :id')
            ->setParameter(':id', $this->user['id']);

        foreach ($data as $key => $item)
        {
            $query->set($this->dc->getConnection()->quoteIdentifier($key), $this->dc->getConnection()->quote($item));
        }

        $query->execute();

        return true;
    }


    public function get_profile()
    {
        return $this->dc->qb()
            ->select('bio, twitter, display_name')
            ->from($this->dc->p('users'), 'l')
            ->where('id = :id')
            ->setParameter(':id', $this->user['id'])
            ->execute()
            ->fetch();
    }


    /**
     * Activates the user account
     *
     * @param   string $id
     * @param   string $activation_key
     * @return  bool
     */
    public function activate_user($id, $activation_key)
    {
        // try activating
        $affected_rows = $this->dc->qb()
            ->update($this->dc->p('users'))
            ->set('activated', ':activated')
            ->where('id = :id')
            ->andWhere('activation_key = :activation_key')
            ->setParameter(':activated', 1)
            ->setParameter(':id', $id)
            ->setParameter(':activation_key', $this->hash_password($activation_key))
            ->execute();

        return $affected_rows ? true : false;
    }

    /**
     * Change a user's password with id and password_key
     *
     * @param   string
     * @param   string
     * @param   string  username or null for current user
     * @return  bool
     */
    public function change_password($id, $new_password_key, $new_password)
    {
        $new_password = password_hash($new_password, PASSWORD_BCRYPT);

        $affected_rows = $this->dc->qb()
            ->update($this->dc->p('users'))
            ->where('id = :id')
            ->andWhere('new_password_key = :new_password_key')
            ->andWhere('new_password_time > :new_password_time')
            ->set('new_password_key', 'null')
            ->set('new_password_time', 'null')
            ->set('password', ':new_password')
            ->setParameter(':id', $id)
            ->setParameter(':new_password', $new_password)
            ->setParameter(':new_password_key', $this->hash_password($new_password_key))
            ->setParameter(':new_password_time', time() - 900)
            ->execute();

        if ( ! $affected_rows)
        {
            throw new FoolUserWrongKey;
        }

        $this->logout();
        $this->force_login($id);
        $this->reset_attempts($this->user['username']);

        return true;
    }


    /**
     * Checks if the pair id/password_key is valid without altering rows
     *
     * @param   int     $id
     * @param   string  $password_key
     * @return  bool
     */
    public function check_new_password_key($id, $password_key)
    {
        $count = $this->dc->qb()
            ->select('COUNT(*) as count')
            ->from($this->dc->p('users'), 'l')
            ->where('id = :id')
            ->andWhere('new_password_key = :new_password_key')
            ->andWhere('new_password_time > :new_password_time')
            ->setParameter('id', $id)
            ->setParameter('new_password_key', $this->hash_password($password_key))
            ->setParameter('new_password_time', time() - 900)
            ->execute()
            ->fetch()['count'];

        return $count > 0;
    }


    /**
     * Generates a code for reaching the change password page
     *
     * @param   string  $email
     * @return  string
     */
    public function create_forgotten_password_key($email)
    {
        $new_password_key = sha1(uniqid().$email.time());

        $affected_rows = $this->dc->qb()
            ->update($this->dc->p('users'))
            ->set('new_password_key', ':new_password_key')
            ->set('new_password_time', time())
            ->where('email = :email')
            ->setParameter(':new_password_key', $this->hash_password($new_password_key))
            ->setParameter(':email', $email)
            ->execute();

        if ( ! $affected_rows)
        {
            throw new FoolUserWrongEmail;
        }

        return $new_password_key;
    }


    /**
     * Generates a code for confirming email change
     *
     * @param   string  $email
     * @return  string
     */
    public function create_change_email_key($email, $password)
    {
        $check_email = $this->dc->qb()
            ->select('*')
            ->from($this->dc->p('users'), 'l')
            ->where('email = :email')
            ->orWhere('(id <> :user_id AND new_email = :email)')
            ->setParameter(':user_id', $this->user['id'])
            ->setParameter(':email', $email)
            ->execute()
            ->fetch();

        if ($check_email)
        {
            throw new FoolUserEmailExists;
        }

        $new_email_key = sha1(uniqid()..$email.time());

        $check_password = $this->dc->qb()
            ->select('*')
            ->from($this->dc->p('users'), 'l')
            ->where('id = :id')
            ->andWhere('password = :password')
            ->setParameter(':id', $this->user['id'])
            ->setParameter(':password', $this->hash_password($password))
            ->execute()
            ->fetch();

        if ( ! $check_password)
        {
            throw new FoolUserWrongPassword;
        }

        $this->dc->qb()
            ->update($this->dc->p('users'))
            ->set('new_email', ':new_email')
            ->set('new_email_key', ':new_email_key')
            ->set('new_email_time', time())
            ->where('id = :id')
            ->setParameter(':id', $this->user['id'])
            ->setParameter(':new_email', $email)
            ->setParameter(':new_email_key', $new_email_key)
            ->execute();

        return $new_email_key;
    }

    /**
     * Checks if the pair id/password_key is valid without altering rows
     *
     * @param   int     $id
     * @param   string  $email_key
     * @return  bool
     */
    public function change_email($id, $email_key)
    {
        $user = $this->dc->qb()
            ->select('*')
            ->from($this->dc->p('users'), 'l')
            ->where('id = :id')
            ->andWhere('new_email_key = :new_email_key')
            ->andWhere('new_email_time > :new_email_time')
            ->setParameter(':id', $id)
            ->setParameter(':new_email_key', $this->hash_password($email_key))
            ->setParameter(':new_email_time', time() - 86400)
            ->execute()
            ->fetch();

        if ( ! $user)
        {
            throw new FoolUserWrongKey;
        }

        $this->dc->qb()
            ->update($this->dc->p('users'))
            ->set('email', ':email')
            ->set('new_email', 'null')
            ->set('new_email_key', 'null')
            ->set('new_email_time', 'null')
            ->where('id = :id')
            ->setParameter(':id', $id)
            ->setParameter(':email', $user['new_email'])
            ->execute();

        $this->logout();
        $this->force_login($id);

        return true;
    }


    /**
     * Generates a code for confirming account deletion
     *
     * @param   string  $email
     * @return  string
     */
    public function create_account_deletion_key($password)
    {
        $check_password = $this->dc->qb()
            ->select('COUNT(*) as count')
            ->from($this->dc->p('users'), 'l')
            ->where('id = :id')
            ->andWhere('password = :password')
            ->setParameter(':id', $this->user['id'])
            ->setParameter(':password', $this->hash_password($password))
            ->execute()
            ->fetch()['count'];

        if ( ! $check_password)
        {
            throw new FoolUserWrongPassword;
        }

        $key = sha1(uniqid().time());

        $this->dc->qb()
            ->update($this->dc->p('users'))
            ->set('deletion_key', ':deletion_key')
            ->set('deletion_time', time())
            ->where('id = :id')
            ->setParameter(':id', $this->user['id'])
            ->setParameter(':deletion_key', $this->hash_password($key))
            ->execute();

        return $key;
    }


    /**
     * Deletes a given user
     *
     * @param   string
     * @param   string
     * @return  bool
     */
    public function deleteAccount($id, $key)
    {
        $affected_rows = $this->dc->qb()
            ->delete($this->dc->p('users'))
            ->where('id = :id')
            ->andWhere('deletion_key = :deletion_key')
            ->andWhere('deletion_time > :deletion_time')
            ->setParameter(':id', $id)
            ->setParameter(':deletion_key', $this->hash_password($key))
            ->setParameter(':deletion_time', time() - 900)
            ->execute();

        if ( ! $affected_rows)
        {
            throw new FoolUserWrongKey;
        }

        $this->logout();
        return true;
    }
}