<?php

namespace Foolz\Foolframe\Model\Auth;

class HashingException extends \Exception {}
class WrongUsernameOrPasswordException extends \Exception {}
class LimitExceededException extends \Exception {}
class WrongPasswordException extends \Exception {}
class WrongEmailException extends \Exception {}
class WrongKeyException extends \Exception {}
class EmailExistsException extends \Exception {}
class UpdateException extends \Exception {}

namespace Foolz\Foolframe\Model;

use Foolz\Foolframe\Model\Auth\EmailExistsException;
use Foolz\Foolframe\Model\Auth\HashingException;
use Foolz\Foolframe\Model\Auth\LimitExceededException;
use Foolz\Foolframe\Model\Auth\UpdateException;
use Foolz\Foolframe\Model\Auth\WrongEmailException;
use Foolz\Foolframe\Model\Auth\WrongKeyException;
use Foolz\Foolframe\Model\Auth\WrongPasswordException;
use Foolz\Foolframe\Model\Auth\WrongUsernameOrPasswordException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

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

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var array
     */
    protected $groups = [];

    /**
     * @var array
     */
    protected $roles = [];

    /**
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);

        $this->dc = $context->getService('doctrine');
        $this->preferences = $context->getService('preferences');
        $this->config = $context->getService('config');

        // load an empty user with no rights
        $this->user = new User($this->getContext(), []);

        $this->groups = $this->config->get('foolz/foolframe', 'foolauth', 'groups');

        foreach ($this->config->get('foolz/foolframe', 'config', 'modules.installed') as $module) {
            foreach ($this->config->get($module, 'foolauth', 'roles') as $key => $item) {
                if (!isset($this->roles[$key])) {
                    $this->roles[$key] = $item;
                } else {
                    $this->roles[$key] += $item;
                }
            }
        }
    }

    /**
     * Hashes a password with BCRYPT and cost 10
     *
     * @param string $password The password to hash
     *
     * @return bool|string False if creating the password failed, string if hash is returned
     */
    protected function passwordHash($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }

    /**
     * @param $id
     * @throws WrongKeyException
     */
    public function authenticateWithId($id)
    {
        $user = $this->dc->qb()
            ->select('*')
            ->from($this->dc->p('users'), 'l')
            ->where('l.id = :id')
            ->setParameters([
                ':id' => $id
            ])
            ->execute()
            ->fetch();

        if (!$user) {
            throw new WrongKeyException();
        }

        $this->user = new User($this->getContext(), $user);
    }

    /**
     * @param string $username
     * @param string $password
     * @param bool $ip_decimal
     *
     * @throws WrongUsernameOrPasswordException
     * @throws LimitExceededException
     */
    public function authenticateWithUsernameAndPassword($username, $password, $ip_decimal = false)
    {
        if ($this->countAttempts($username) >= $this->config->get('foolz/foolframe', 'foolauth', 'attempts_to_lock')) {
            throw new LimitExceededException();
        }

        $user = $this->dc->qb()
            ->select('*')
            ->from($this->dc->p('users'), 'l')
            ->where('l.username = :username')
            ->setParameters([
                ':username' => $username
            ])
            ->execute()
            ->fetch();

        if (!$user) {
            throw new WrongUsernameOrPasswordException();
        }

        if (!password_verify($password, $user['password'])) {
            $this->dc->getConnection()->insert($this->dc->p('user_login_attempts'), [
                'username' => $username,
                'ip' => $ip_decimal ? : 0,
                'time' => time()
            ]);

            throw new WrongUsernameOrPasswordException();
        }

        $this->resetAttempts($username);

        $this->user = new User($this->getContext(), $user);
    }

    /**
     * @param string $remember_me
     *
     * @throws WrongKeyException
     */
    public function authenticateWithRememberMe($remember_me)
    {
        $remember_me = @unserialize($remember_me);

        if (!$remember_me) {
            throw new WrongKeyException();
        }

        if (!isset($remember_me['user_id']) || !isset($remember_me['login_id']) || !isset($remember_me['login_hash'])) {
            throw new WrongKeyException();
        }

        $user = $this->dc->qb()
            ->select('*')
            ->from($this->dc->p('user_autologin'), 'la')
            ->join('la', $this->dc->p('users'), 'l', 'la.user_id = l.id')
            ->where('la.user_id = :user_id')
            ->andWhere('la.login_id = :login_id')
            ->andWhere('la.expiration > :time')
            ->setParameters([
                ':user_id' => $remember_me['user_id'],
                ':login_id' => $remember_me['login_id'],
                ':time' => time()
            ])
            ->execute()
            ->fetch();

        if (!$user) {
            throw new WrongKeyException();
        }


        $hashed = hash('sha256', $remember_me['login_hash']);

        if (!$hashed || $hashed !== $user['login_hash']) {
            throw new WrongKeyException();
        }

        $this->user = new User($this->getContext(), $user);
    }

    /**
     * @param $ip_decimal
     * @param string $user_agent
     * @return string
     * @throws
     */
    public function createAutologinHash($ip_decimal, $user_agent = '')
    {
        $last_login = time();
        $login_id = sha1(uniqid() . $this->user->getUsername() . $last_login);
        $login_hash = sha1(uniqid('', true) . $this->user->getUsername() . $last_login);

        // autologin garbage collection
        if (time() % 100 == 0) {
            $this->dc->qb()
                ->delete($this->dc->p('user_autologin'))
                ->where('expiration < ' . time())
                ->execute();
        }

        $login_hash_hashed = hash('sha256', $login_hash);

        if (!$login_hash_hashed) {
            throw new HashingException();
        }

        $this->dc->getConnection()->insert($this->dc->p('user_autologin'), [
            'user_id' => $this->user->getId(),
            'login_id' => $login_id,
            'login_hash' => $login_hash_hashed,
            'expiration' => time() + (365 * 24 * 60 * 60), // 1 year
            'last_ip' => $ip_decimal,
            'user_agent' => $user_agent,
            'last_login' => time()
        ]);

        return serialize(['user_id' => $this->user->getId(), 'login_id' => $login_id, 'login_hash' => $login_hash]);
    }

    public function hasAccess($area) {
        if (!isset($this->groups[$this->getUser()->getGroupId()])) {
            throw new RuntimeException('Group not found');
        }

        $group = $this->groups[$this->getUser()->getGroupId()];
        $roles = $this->roles[$group['roles'][0]];

        $area = explode('.', $area);

        if (count($area) < 2) {
            return false;
        }

        if (isset($roles[$area[0]])) {
            return in_array($area[1], $roles[$area[0]]);
        }

        return false;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Checks how many attempts have been made to login
     *
     * @param  string $username the submitted username
     * @return int the amount of attempts before successful login
     */
    public function countAttempts($username)
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
    public function resetAttempts($username)
    {
        $this->dc->qb()
            ->delete($this->dc->p('user_login_attempts'))
            ->where('username = :username')
            ->setParameter(':username', $username)
            ->execute();
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $email
     * @param int $group
     * @param array $profile_fields
     * @return array|bool
     * @throws UpdateException
     */
    public function createUser($username, $password, $email, $group = 1, Array $profile_fields = array())
    {
        $password = trim($password);
        $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);

        if (empty($username) or empty($password) or empty($email)) {
            throw new UpdateException('Username, password and email address can\'t be empty.', 1);
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

        if (count($same_users) > 0) {
            if (in_array(strtolower($email), array_map('strtolower', current($same_users)))) {
                throw new UpdateException('Email address already exists', 2);
            } else {
                throw new UpdateException('Username already exists', 3);
            }
        }

        $activated = (bool) $this->preferences->get('foolframe.auth.disable_registration_email');
        $activation_key = null;
        $activation_key_hashed = null;

        if (!$activated) {
            // get a string for validation email
            $activation_key = sha1(uniqid() . time());
            $activation_key_hashed = $this->passwordHash($activation_key);

            if (!$activation_key_hashed) {
                throw new UpdateException('Issues hashing the activation key', 4);
            }
        }

        $hashed_password = $this->passwordHash($password);
        if (!$hashed_password) {
            throw new UpdateException('Issues hashing the password', 4);
        }

        $user = array(
            'username' => (string)$username,
            'password' => $hashed_password,
            'email' => $email,
            'group_id' => (int)$group,
            'activated' => (int)$activated,
            'activation_key' => $activation_key_hashed,
            'profile_fields' => serialize($profile_fields),
            'created_at' => time()
        );

        $result = $this->dc->getConnection()->insert($this->dc->p('users'), $user);

        return ($result) ? array($this->dc->getConnection()->lastInsertId($this->dc->p('users_id_seq')), $activation_key) : false;
    }

    /**
     * Activates the user account
     *
     * @param   string $id
     * @param   string $activation_key
     * @return  bool
     */
    public function activateUser($id, $activation_key)
    {
        $row = $this->dc->qb()
            ->select('*')
            ->from($this->dc->p('users'), 'l')
            ->where('id = :id')
            ->setParameter(':id', $id)
            ->execute()
            ->fetch();

        if (!$row || !password_verify($activation_key, $row['activation_key'])) {
            return false;
        }

        // try activating
        $affected_rows = $this->dc->qb()
            ->update($this->dc->p('users'))
            ->set('activated', ':activated')
            ->where('id = :id')
            ->setParameter(':activated', 1)
            ->setParameter(':id', $id)
            ->execute();

        return $affected_rows ? true : false;
    }

    public function changePassword($id, $new_password_key, $new_password)
    {
        if (!$this->checkNewPasswordKey($id, $new_password_key)) {
            throw new WrongKeyException;
        }

        $new_password = password_hash($new_password, PASSWORD_BCRYPT);

        if (!$new_password) {
            throw new HashingException;
        }

        $affected_rows = $this->dc->qb()
            ->update($this->dc->p('users'))
            ->where('id = :id')
            ->set('new_password_key', 'null')
            ->set('new_password_time', 'null')
            ->set('password', ':new_password')
            ->setParameter(':id', $id)
            ->setParameter(':new_password', $new_password)
            ->execute();

        if (!$affected_rows) {
            throw new WrongKeyException;
        }

        $this->authenticateWithId($id);
        $this->resetAttempts($this->user->getUsername());

        return true;
    }

    public function checkNewPasswordKey($id, $password_key)
    {
        $row = $this->dc->qb()
            ->select('*')
            ->from($this->dc->p('users'), 'l')
            ->where('id = :id')
            ->andWhere('new_password_time > :new_password_time')
            ->setParameter(':id', $id)
            ->setParameter(':new_password_time', time() - 900)
            ->execute()
            ->fetch();

        if (!$row || !password_verify($password_key, $row['new_password_key'])) {
            return false;
        }

        return true;
    }

    public function createForgottenPasswordKey($email)
    {
        $new_password_key = sha1(uniqid() . $email . time());
        $new_password_key_hashed = $this->passwordHash($new_password_key);

        if (!$new_password_key_hashed) {
            throw new HashingException;
        }

        $affected_rows = $this->dc->qb()
            ->update($this->dc->p('users'))
            ->set('new_password_key', ':new_password_key')
            ->set('new_password_time', time())
            ->where('email = :email')
            ->setParameter(':new_password_key', $new_password_key_hashed)
            ->setParameter(':email', $email)
            ->execute();

        if (!$affected_rows) {
            throw new WrongEmailException;
        }

        return $new_password_key;
    }

    public function createChangeEmailKey($email, $password)
    {
        $check_email = $this->dc->qb()
            ->select('*')
            ->from($this->dc->p('users'), 'l')
            ->where('email = :email')
            ->orWhere('(id <> :id AND new_email = :email)')
            ->setParameter(':id', $this->user->getId())
            ->setParameter(':email', $email)
            ->execute()
            ->fetch();

        if ($check_email) {
            throw new EmailExistsException;
        }

        $new_email_key = sha1(uniqid() . $email . time());
        $new_email_key_hashed = $this->passwordHash($new_email_key);

        if (!$new_email_key_hashed) {
            throw new HashingException;
        }

        $check_password = $this->dc->qb()
            ->select('*')
            ->from($this->dc->p('users'), 'l')
            ->where('id = :id')
            ->setParameter(':id', $this->user->getId())
            ->execute()
            ->fetch();

        if (!password_verify($password, $check_password['password'])) {
            throw new WrongPasswordException;
        }

        $this->dc->qb()
            ->update($this->dc->p('users'))
            ->set('new_email', ':new_email')
            ->set('new_email_key', ':new_email_key')
            ->set('new_email_time', time())
            ->where('id = :id')
            ->setParameter(':id', $this->user->getId())
            ->setParameter(':new_email', $email)
            ->setParameter(':new_email_key', $new_email_key_hashed)
            ->execute();

        return $new_email_key;
    }

    public function changeEmail($id, $email_key)
    {
        $user = $this->dc->qb()
            ->select('*')
            ->from($this->dc->p('users'), 'l')
            ->where('id = :id')
            ->andWhere('new_email_time > :new_email_time')
            ->setParameter(':id', $id)
            ->setParameter(':new_email_time', time() - 86400)
            ->execute()
            ->fetch();

        if (!$user || !password_verify($email_key, $user['new_email_key'])) {
            throw new WrongKeyException;
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

        $this->authenticateWithId($id);

        return true;
    }

    public function createAccountDeletionKey($password)
    {
        $check_password = $this->dc->qb()
            ->select('*')
            ->from($this->dc->p('users'), 'l')
            ->where('id = :id')
            ->setParameter(':id', $this->user->getId())
            ->execute()
            ->fetch();

        if (!$check_password || !password_verify($password, $check_password['password'])) {
            throw new WrongPasswordException;
        }

        $key = sha1(uniqid() . time());
        $key_hashed = $this->passwordHash($key);

        if (!$key_hashed) {
            throw new HashingException;
        }

        $this->dc->qb()
            ->update($this->dc->p('users'))
            ->set('deletion_key', ':deletion_key')
            ->set('deletion_time', time())
            ->where('id = :id')
            ->setParameter(':id', $this->user->getId())
            ->setParameter(':deletion_key', $key_hashed)
            ->execute();

        return $key;
    }

    public function deleteAccount($id, $key)
    {
        $row = $this->dc->qb()
            ->select('*')
            ->from($this->dc->p('users'), 'l')
            ->where('id = :id')
            ->andWhere('deletion_time > :deletion_time')
            ->setParameter(':id', $id)
            ->setParameter(':deletion_time', time() - 900)
            ->execute()
            ->fetch();

        if (!$row || !password_verify($key, $row['deletion_key'])) {
            throw new WrongKeyException;
        }

        $affected_rows = $this->dc->qb()
            ->delete($this->dc->p('users'))
            ->where('id = :id')
            ->setParameter(':id', $id)
            ->execute();

        if (!$affected_rows) {
            throw new WrongKeyException;
        }

        return true;
    }
}
