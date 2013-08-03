<?php

namespace Foolz\Foolframe\Model;

class UsersWrongIdException extends \Exception {}

class Users extends Model
{
    /**
     * @var DoctrineConnection
     */
    protected $dc;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);

        $this->dc = $context->getService('doctrine');
        $this->config = $context->getService('config');
    }

    /**
     * Gets the current user
     *
     * @return object
     */
    public function getUser()
    {
        $id = \Auth::get_user_id();
        $id = $id[1];

        $result = $this->dc->qb()
            ->select('*')
            ->from($this->dc->p($this->config->get('foolz/foolframe', 'foolauth', 'table_name')), 't')
            ->where('t.id = :id')
            ->setParameter(':id', $id)
            ->execute()
            ->fetch();

        if (!$result) {
            throw new UsersWrongIdException;
        }

        return User::forge($this->getContext(), $result);
    }

    /**
     * Gets single user database row by selected row
     *
     * @param  int  $id
     * @return object
     */
    public function getUserBy($field, $id)
    {
        $result = $this->dc->qb()
            ->select('*')
            ->from($this->dc->p($this->config->get('foolz/foolframe', 'foolauth', 'table_name')), 't')
            ->where($field.' = '.$this->dc->forge()->quote($id))
            ->execute()
            ->fetch();

        if (!$result) {
            throw new UsersWrongIdException;
        }

        return User::forge($this->getContext(), $result);
    }

    /**
     * Gets all user limited with page and limit
     *
     * @param  int  $page
     * @param  into $limit
     * @return object
     */
    public function getAll($page = 1, $limit = 40)
    {
        $users = $this->dc->qb()
            ->select('*')
            ->from($this->dc->p($this->config->get('foolz/foolframe', 'foolauth', 'table_name')), 't')
            ->setMaxResults($limit)
            ->setFirstResult(($page * $limit) - $limit)
            ->execute()
            ->fetchAll();

        $users = User::forge($this->getContext(), $users);

        $count = $this->dc->qb()
            ->select('COUNT(*) as count')
            ->from($this->dc->p($this->config->get('foolz/foolframe', 'foolauth', 'table_name')), 't')
            ->execute()
            ->fetch();

        return ['result' => $users, 'count' => $count['count']];
    }
}
