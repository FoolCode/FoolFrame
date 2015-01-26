<?php

namespace Foolz\FoolFrame\Controller\Admin;

use Foolz\FoolFrame\Model\PluginException;
use Foolz\FoolFrame\Model\Plugins as PluginsModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Plugins extends \Foolz\FoolFrame\Controller\Admin
{
    /**
     * @var PluginsModel
     */
    protected $plugins;

    public function before()
    {
        parent::before();

        $this->plugins = $this->getContext()->getService('plugins');

        // set controller title
        $this->param_manager->setParam('controller_title', _i('Plugins'));
    }

    public function security()
    {
        return $this->getAuth()->hasAccess('maccess.admin');
    }

    function action_manage()
    {
        $data = [];
        $data['plugins'] = $this->plugins->getAll();

        $this->param_manager->setParam('method_title', _i('Manage'));
        $this->builder->createPartial('body', 'plugins/manage')
            ->getParamManager()->setParams($data);

        return new Response($this->builder->build());
    }

    function action_action()
    {
        if ($this->getPost() && !$this->security->checkCsrfToken($this->getRequest())) {
            $this->notices->setFlash('warning', _i('The security token wasn\'t found. Try resubmitting.'));
            return $this->redirect('admin/plugins/manage');
        }

        if (!$slug = $this->getPost('name')) {
            throw new NotFoundHttpException;
        }

        if (!$this->getPost('action') || !in_array($this->getPost('action'), array('enable', 'disable', 'remove'))) {
            throw new NotFoundHttpException;
        }

        $action = $this->getPost('action');

        $plugin = $this->plugins->getPlugin($slug);

        if (!$plugin) {
            throw new NotFoundHttpException;
        }

        switch ($action) {
            case 'enable':
                try {
                    $this->plugins->enable($slug);
                } catch (PluginException $e) {
                    $this->notices->setFlash('error', _i('The plugin %s couldn\'t be enabled.', $plugin->getJsonConfig('extra.name')));
                    break;
                }

                $this->notices->setFlash('success', _i('The %s plugin is now enabled.', $plugin->getJsonConfig('extra.name')));

                break;

            case 'disable':
                try {
                    $this->plugins->disable($slug);
                } catch (PluginException $e) {
                    $this->notices->setFlash('error', _i('The %s plugin couldn\'t be enabled.', $plugin->getJsonConfig('extra.name')));
                    break;
                }

                $this->notices->setFlash('success', _i('The %s plugin is now disabled.', $plugin->getJsonConfig('extra.name')));
                break;

            case 'upgrade':
                break;

            case 'remove':
                try {
                    $this->plugins->remove($slug);
                } catch (PluginException $e) {
                    $this->notices->setFlash('error', _i('The :slug plugin couldn\'t be removed.', $plugin->getJsonConfig('extra.name')));
                    break;
                }
                $this->notices->setFlash('success', _i('The :slug plugin was removed.', $plugin->getJsonConfig('extra.name')));
                break;
        }

        return $this->redirect('admin/plugins/manage');
    }
}
