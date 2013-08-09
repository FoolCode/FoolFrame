<?php
/**
 * Created by JetBrains PhpStorm.
 * User: woxxy
 * Date: 07/08/13
 * Time: 21:26
 * To change this template use File | Settings | File Templates.
 */

namespace Foolz\Foolframe\Model\Auth;


use Foolz\Foolframe\Model\Context;
use Foolz\Foolframe\Model\Model;
use Symfony\Component\Security\Core\User\UserInterface;

class User extends Model implements UserInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string;
     */
    protected $password;

    /**
     * @var
     */
    protected $role;

    const ROLE_GUEST = 0;
    const ROLE_USER = 1;
    const ROLE_MOD = 2;
    const ROLE_ADMIN = 3;

    public function __construct(Context $context, $id, $username, $password, $role)
    {
        parent::__construct($context);

        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return Role[] The user roles
     */
    public function getRoles()
    {
        $roles = [];

        switch ($this->role) {
            case 0:
                $roles[] = 'ROLE_GUEST';
                break;
            case 1:
                $roles[] = 'ROLE_USER';
                break;
            case 2:
                $roles[] = 'ROLE_MOD';
                break;
            case 3:
                $roles[] = 'ROLE_ADMIN';
                break;
        }

        return $roles;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     *
     * @return void
     */
    public function eraseCredentials()
    {}
}