<?php

namespace Foolz\Foolframe\Model;

class User extends Model
{
    /**
     * @var DoctrineConnection
     */
    protected $dc;

    /**
     * @var Config
     */
    protected $config;

    public $id = null;
    public $username = null;
    public $password = null;
    public $group_id = null;
    public $email = null;
    public $new_email = null;
    public $new_email_key = null;
    public $new_email_time = null;
    public $last_login = null;
    public $activated = null;
    public $activation_key = null;
    public $new_password_key = null;
    public $deletion_key = null;
    public $deletion_time = null;
    public $profile_fields = null;
    public $bio = null;
    public $twitter = null;
    public $display_name = null;
    public $created_at = null;

    public $password_current = null;

    private $editable_fields = [
        'username',
        'password',
        'group_id',
        'email',
        'profile_fields',
        'bio',
        'twitter',
        'display_name'
    ];

    public function __construct(Context $context, $data)
    {
        parent::__construct($context);

        $this->dc = $context->getService('doctrine');
        $this->config = $context->getService('config');

        foreach($data as $key => $item) {
            if ($key == 'password') {
                $key = 'password_current';
            }

            $this->$key = $item;
        }
    }

    public static function forge(Context $context, $data)
    {
        if (is_array($data) && !\Arr::is_assoc($data)) {
            $array = [];

            foreach($data as $item) {
                $array[] = static::forge($context, $item);
            }

            return $array;
        }

        return new User($context, $data);
    }

    public function save(Array $data = [])
    {
        foreach ($data as $key => $item) {
            $this->$key = $item;
        }

        $set = [];

        foreach($this->editable_fields as $filter) {
            $set[$filter] = $this->$filter;
        }

        if (!is_null($set['password']) && $set['password'] !== '') {
            $set['password'] = \Auth::hash_password($set['password']);
        } else {
            unset($set['password']);
        }

        $query = $this->dc->qb()
            ->update($this->dc->p($this->config->get('foolz/foolframe', 'foolauth', 'table_name')))
            ->where('id = :id')
            ->setParameter(':id', $this->id);

        foreach ($set as $key => $item) {
            $query->set($this->dc->forge()->quoteIdentifier($key), $this->dc->forge()->quote($item));
        }

        $query->execute();
    }
}
