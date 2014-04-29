<?php

use Foolz\Foolframe\Model\Auth;
use Foolz\Foolframe\Model\Autoloader;
use Foolz\Foolframe\Model\Context;
use Foolz\Foolframe\Model\Preferences;
use Foolz\Plugin\Event;
use Foolz\Plugin\Result;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class HHVM_SslTools
{
    public static function run()
    {
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
                /** @var Auth $auth */
                $auth = $context->getService('auth');

                $autoloader->addClassMap([
                    'Foolz\Foolframe\Plugins\SslTools\Model\SslTools' => __DIR__.'/classes/model/ssl_tools.php',
                    'Foolz\Foolframe\Controller\Admin\Plugins\SslTools' => __DIR__.'/classes/controller/admin/ssl_tools.php'
                ]);

                Event::forge('Foolz\Foolframe\Model\Context.handleWeb.has_auth')
                    ->setCall(function($result) use ($context, $auth) {
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

                            Event::forge('Foolz\Foolframe\Controller\Admin.before.sidebar.add')
                                ->setCall(function($result) {
                                    $sidebar = $result->getParam('sidebar');
                                    $sidebar[]['plugins'] = [
                                        'content' => array('ssl_tools/manage' => array('level' => 'admin', 'name' => _i('SSL Tools'), 'icon' => 'icon-lock'))
                                    ];
                                    $result->setParam('sidebar', $sidebar);
                                });
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
                        $context = $result->getObject();

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
                    ->setCall(function(Result $result) use ($auth) {
                        /** @var Request $request */
                        $obj = $result->getObject();
                        /** @var Request $request */
                        $request = $result->getParam('request');
                        /** @var Preferences $preferences */
                        $preferences = $obj->getService('preferences');

                        if (!$request->isSecure()) {
                            if ($preferences->get('foolframe.plugins.ssl_tools.force_everyone')
                                || ($preferences->get('foolframe.plugins.ssl_tools.force_for_logged') && $auth->hasAccess('maccess.user'))
                            )
                            {
                                // redirect to itself
                                $result->set(new RedirectResponse('https://'.$request->getHttpHost().$request->getRequestUri()));
                                return;
                            }
                        }
                    });
            });

    }
}

HHVM_SslTools::run();
