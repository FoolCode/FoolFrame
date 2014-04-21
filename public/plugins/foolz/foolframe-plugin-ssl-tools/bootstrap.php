<?php

use Foolz\Foolframe\Model\Auth;
use Foolz\Foolframe\Model\Autoloader;
use Foolz\Foolframe\Model\Context;
use Foolz\Plugin\Event;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

Event::forge('Foolz\Plugin\Plugin::execute.foolz/foolframe-plugin-ssl-tools')
    ->setCall(function($result) {
        // this plugin works with indexes that don't exist in CLI
        if (PHP_SAPI === 'cli') {
            return false;
        }

        /* @var Context $context */
        $context = $result->getParam('context');
        /** @var Autoloader $autoloader */
        $autoloader = $context->getService('autoloader');

        $autoloader->addClassMap([
            'Foolz\Foolframe\Plugins\SslTools\Model\SslTools' => __DIR__.'/classes/model/ssl_tools.php',
            'Foolz\Foolframe\Controller\Admin\Plugins\SslTools' => __DIR__.'/classes/controller/admin/ssl_tools.php'
        ]);

        Event::forge('Foolz\Foolframe\Model\Context.handleWeb.has_auth')
            ->setCall(function($result) use ($context) {
                /** @var Auth $auth */
                $auth = $context->getService('auth');
                // don't add the admin panels if the user is not an admin
                if ($auth->hasAccess('maccess.admin')) {
                    $context->getRouteCollection()->add(
                        'foolframe.plugin.ssl_tools.admin', new \Symfony\Component\Routing\Route(
                            '/admin/plugins/ssl_tools/{_suffix}',
                            [
                                '_suffix' => 'manage',
                                '_controller' => '\Foolz\Foolframe\Controller\Admin\Plugins\SslTools::manage'
                            ],
                            [
                                '_suffix' => '.*'
                            ]
                        )
                    );

                    \Plugins::registerSidebarElement('admin', 'plugins', array(
                        'content' => array('ssl_tools/manage' => array('level' => 'admin', 'name' => _i('SSL Tools'), 'icon' => 'icon-lock'))
                    ));
                }
            });


        $context->getContainer()
            ->register('foolframe-plugin.ssl_tools', 'Foolz\Foolframe\Plugins\SslTools\Model\SslTools')
            ->addArgument($context);

        /** @var \Foolz\Foolframe\Plugins\SslTools\Model\SslTools $ssl_tools */
        $ssl_tools = $context->getService('foolframe-plugin.ssl_tools');

        Event::forge('Foolz\Foolframe\Model\Context.handleWeb.has_request')
            ->setCall(function($result) use ($ssl_tools) {
                $request = $result->getParam('request');
                $context = $this;

                Event::forge('foolframe.themes.generic_top_nav_buttons')
                    ->setCall(function($result) use ($context, $request, $ssl_tools) {
                        $ssl_tools->nav($context, $request, 'top', $result);
                    })
                    ->setPriority(4);

                Event::forge('foolframe.themes.generic_bottom_nav_buttons')
                    ->setCall(function($result) use ($context, $request, $ssl_tools) {
                        $ssl_tools->nav($context, $request, 'bottom', $result);
                    })
                    ->setPriority(4);
            });

        Event::forge('Foolz\Foolframe\Model\Context.handleWeb.override_response')
            ->setCall(function($result) use ($auth) {
                /** @var Request $request */
                $request = $result->getParam('request');
                $this->preferences = $this->getService('preferences');

                if (!$request->isSecure()) {
                    if ($this->preferences->get('foolframe.plugins.ssl_tools.force_everyone')
                        || ($this->preferences->get('foolframe.plugins.ssl_tools.force_for_logged') && $auth->hasAccess('maccess.user'))
                    )
                    {
                        // redirect to itself
                        $this->set(new RedirectResponse('https://'.$request->getHttpHost().$request->getRequestUri()));
                        return;
                    }
                }
            });
    });
