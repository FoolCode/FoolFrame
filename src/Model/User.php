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

    public $id = 0;
    public $username = '';
    public $password = '';
    public $group_id = 0;
    public $email = '';
    public $new_email = null;
    public $new_email_key = null;
    public $new_email_time = null;
    public $last_login = null;
    public $activated = false;
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
        if (is_array($data) && !Arr::is_assoc($data)) {
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
            $set['password'] = password_hash($set['password'], PASSWORD_BCRYPT, ['cost' => 10]);
        } else {
            unset($set['password']);
        }

        $query = $this->dc->qb()
            ->update($this->dc->p($this->config->get('foolz/foolframe', 'foolauth', 'table_name')))
            ->where('id = :id')
            ->setParameter(':id', $this->id);

        foreach ($set as $key => $item) {
            $query->set($this->dc->getConnection()->quoteIdentifier($key), $this->dc->getConnection()->quote($item));
        }


        $query->execute();
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return bool
     */
    public function isActivated()
    {
        return (bool) $this->activated;
    }

    /**
     * @return string
     */
    public function getActivationKey()
    {
        return $this->activation_key;
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @return string
     */
    public function getDeletionKey()
    {
        return $this->deletion_key;
    }

    /**
     * @return string
     */
    public function getDeletionTime()
    {
        return $this->deletion_time;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->display_name;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return int
     */
    public function getGroupId()
    {
        return $this->group_id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLastLogin()
    {
        return $this->last_login;
    }

    /**
     * @return string
     */
    public function getNewEmail()
    {
        return $this->new_email;
    }

    /**
     * @return string
     */
    public function getNewEmailKey()
    {
        return $this->new_email_key;
    }

    /**
     * @return string
     */
    public function getNewEmailTime()
    {
        return $this->new_email_time;
    }

    /**
     * @return string
     */
    public function getNewPasswordKey()
    {
        return $this->new_password_key;
    }

    /**
     * @return null|string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getPasswordCurrent()
    {
        return $this->password_current;
    }

    /**
     * @return array
     */
    public function getProfileFields()
    {
        return $this->profile_fields;
    }

    /**
     * @return string
     */
    public function getTwitter()
    {
        return $this->twitter;
    }
}
