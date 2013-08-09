<?php

namespace Foolz\Foolframe\Model\Auth;

use Foolz\Foolframe\Model\Context;
use Foolz\Foolframe\Model\DoctrineConnection;
use Foolz\Foolframe\Model\Model;

class RememberMe extends Model
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

    public function createLoginHash(User $user, $ip_decimal, $user_agent = '')
    {
        $last_login = time();
        $login_hash = sha1(uniqid().$user->getUsername().$last_login);

        // autologin garbage collection
        if (time() % 25 == 0)
        {
            $this->dc->qb()
                ->delete($this->dc->p('autologin'))
                ->where('expiration < '.time())
                ->execute();
        }

        $this->dc->getConnection()->insert($this->dc->p('autologin'), [
            'user_id' => $user->getId(),
            'login_hash' => password_hash($login_hash, PASSWORD_BCRYPT, ['cost' => 10]),
            'expiration' => time() + 604800, // 7 days
            'last_ip' => $ip_decimal,
            'user_agent' => $user_agent,
            'last_login' => time()
        ]);

        return $login_hash;
    }
}