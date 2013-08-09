<?php

namespace Foolz\Foolframe\Model\Auth;

use Foolz\Foolframe\Model\Context;
use Foolz\Foolframe\Model\DoctrineConnection;
use Foolz\Foolframe\Model\Model;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider extends Model implements UserProviderInterface
{
    /**
     * @var DoctrineConnection
     */
    protected $dc;

    public function __construct(Context $context)
    {
        parent::__construct($context);

        $this->dc = $context->getService('doctrine');
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @see UsernameNotFoundException
     *
     * @throws UsernameNotFoundException if the user is not found
     *
     */
    public function loadUserByUsername($username)
    {
        /** @var \Doctrine\DBAL\Driver\Statement $user_db */
        $user_db = $this->dc->qb()
            ->select('*')
            ->from($this->dc->p('users'), 'l')
            ->where('username = :username')
            ->setParameter(':username', $username)
            ->execute()
            ->fetch();

        if (!$user_db) {
            throw new UsernameNotFoundException();
        }

        return new User($this->getContext(), $user_db['id'], $user_db['username'], $user_db['password'], $user_db['group_id']);
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        try {
            return $this->loadUserByUsername($user->getUsername());
        } catch (UsernameNotFoundException $e) {
            throw new UnsupportedUserException;
        }
    }

    /**
     * Whether this provider supports the given user class
     *
     * @param string $class
     *
     * @return Boolean
     */
    public function supportsClass($class)
    {
        return $class === 'Foolz\Foolframe\Model\Auth\User';
    }
}