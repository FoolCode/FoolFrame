<?php

namespace Foolz\FoolFrame\Model;

use Foolz\Cache\Cache;
use Foolz\FoolFrame\Model\Validation\Validator;
use Foolz\Plugin\Hook;
use Symfony\Component\HttpFoundation\Request;

class Preferences extends Model
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Foolz\Profiler\Profiler
     */
    protected $profiler;

    /**
     * @var DoctrineConnection
     */
    protected $dc;

    /**
     * @var Notices
     */
    protected $notices;

    /**
     * @var Security
     */
    protected $security;

    /**
     * @var array
     */
    protected $modules = [];

    /**
     * @var array
     */
    protected $preferences = [];

    /**
     * @var bool
     */
    protected $loaded = false;

    /**
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);

        $this->config = $context->getService('config');
        $this->profiler = $context->getService('profiler');
        $this->dc = $context->getService('doctrine');
        $this->security = $this->getContext()->getService('security');
    }

    /**
     * @param bool $reload
     * @return array|mixed
     */
    public function load($reload = false)
    {
        $this->profiler->log('Preferences::load Start');

        if ($reload === true) {
            Cache::item('foolframe.model.preferences.settings')->delete();
        }

        $this->modules = $this->config->get('foolz/foolframe', 'config', 'modules.installed');

        try {
            $this->preferences = Cache::item('foolframe.model.preferences.settings')->get();
        } catch (\OutOfBoundsException $e) {
            $preferences = $this->dc->qb()
                ->select('*')
                ->from($this->dc->p('preferences'), 'p')
                ->execute()
                ->fetchAll();

            foreach($preferences as $pref) {
                // fix the PHP issue where . is changed to _ in the $_POST array
                $this->preferences[$pref['name']] = $pref['value'];
            }

            Cache::item('foolframe.model.preferences.settings')->set($this->preferences, 3600);
        }

        $this->preferences = Hook::forge('Foolz\FoolFrame\Model\Preferences::load#var.preferences')
            ->setObject($this)
            ->setParam('preferences', $this->preferences)
            ->execute()
            ->get($this->preferences);

        $this->profiler->logMem('Preferences $preferences', $this->preferences);
        $this->profiler->log('Preferences::load End');

        $this->loaded = true;

        return $this->preferences;
    }

    public function get($setting, $fallback = null, $show_empty_string = false)
    {
        if (!$this->loaded) {
            $this->load();
        }

        if (isset($this->preferences[$setting]) && ($show_empty_string || $this->preferences[$setting] !== '')) {
            return $this->preferences[$setting];
        }

        if ($fallback !== null) {
            return $fallback;
        }

        $segments = explode('.', $setting);
        $identifier = array_shift($segments);
        $query = implode('.', $segments);

        return $this->config->get($this->modules[$identifier]['namespace'], 'package', 'preferences.'.$query);
    }

    public function set($setting, $value, $reload = true)
    {
        // if array, serialize value
        if (is_array($value)) {
            $value = serialize($value);
        }

        $count = $this->dc->qb()
            ->select('COUNT(*) as count')
            ->from($this->dc->p('preferences'), 'p')
            ->where('p.name = :name')
            ->setParameter(':name', $setting)
            ->execute()
            ->fetch()['count'];

        if ($count > 0) {
            $this->dc->qb()
                ->update($this->dc->p('preferences'))
                ->set('value', ':value')
                ->where('name = :name')
                ->setParameters([':value' => $value, ':name' => $setting])
                ->execute();
        } else {
            $this->dc->getConnection()->insert($this->dc->p('preferences'), ['name' => $setting, 'value' => $value]);
        }

        if ($reload) {
            return $this->load(true);
        }

        return $this->preferences;
    }

    /**
     * Save in the preferences table the name/value pairs
     *
     * @param array $data name => value
     */
    public function submit($data)
    {
        foreach ($data as $name => $value) {
            // in case it's an array of values from name="thename[]"
            if(is_array($value)) {
                // remove also empty values with array_filter
                // but we want to keep 0s
                $value = serialize(array_filter($value, function($var) {
                    if($var === 0) {
                        return true;
                    }

                    return $var;
                }));
            }

            $this->set($name, $value, false);
        }

        // reload those preferences
        $this->load(true);
    }

    /**
     * A lazy way to submit the preference panel input, saves some code in controller
     *
     * This function runs the custom validation function that uses the $form array
     * to first run the original FuelPHP validation and then the anonymous
     * functions included in the $form array. It sets a proper notice for the
     * admin interface on conclusion.
     *
     * @param Request $request
     * @param array $form
     * @param bool|array $input If it evaluates to false, content won't be submitted
     */
    public function submit_auto(Request $request, $form, $input = false)
    {
        if ($input) {
            $this->notices = $this->getContext()->getService('notices');
            if (!$this->security->checkCsrfToken($request)) {
                $this->notices->set('warning', _i('The security token wasn\'t found. Try resubmitting.'));
                return;
            }

            $post = [];

            foreach ($input as $key => $item) {
                // PHP doesn't allow periods in POST array
                $post[str_replace(',', '.', $key)] = $item;
            }

            $result = Validator::formValidate($form, $post);
            if (isset($result['error'])) {
                $this->notices->set('warning', $result['error']);
            } else {
                if (isset($result['warning'])) {
                    $this->notices->set('warning', $result['warning']);
                }

                $this->notices->set('success', _i('Preferences updated.'));
                $this->submit($result['success']);
            }
        }
    }
}
