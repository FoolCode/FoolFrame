<?php

use Foolz\Foolframe\Model\DoctrineConnection;
use Foolz\Foolframe\Model\Legacy\DoctrineConnection as DC;
use Foolz\Plugin\Event;
use Symfony\Component\Routing\RouteCollection;

Event::forge('Foolz\Plugin\Plugin::execute.foolz/foolframe-plugin-articles')
    ->setCall(function($result) {
        /* @var $context \Foolz\Foolframe\Model\Context */
        $context = $result->getParam('context');

        \Autoloader::add_classes(array(
            'Foolz\Foolframe\Plugins\Articles\Model\Articles' => __DIR__.'/classes/model/articles.php',
            'Foolz\Foolframe\Controller\Admin\Articles' => __DIR__.'/classes/controller/admin.php',
            'Foolz\Foolfuuka\Controller\Chan\Articles' => __DIR__.'/classes/controller/chan.php'
        ));

        $context->getContainer()
            ->register('foolframe-plugin.articles', 'Foolz\Foolframe\Plugins\Articles\Model\Articles')
            ->addArgument($context);

        Event::forge('Foolz\Foolframe\Model\Context.handleWeb.has_request')
            ->setCall(function($result) use ($context) {
                // don't add the admin panels if the user is not an admin
                if ($context->getService('auth')->hasAccess('maccess.admin')) {
                    $context->getRouteCollection()->add(
                        'foolframe.plugin.articles.admin', new \Symfony\Component\Routing\Route(
                            '/admin/articles/{_suffix}',
                            [
                                '_suffix' => 'manage',
                                '_controller' => '\Foolz\Foolframe\Controller\Admin\Articles::*'
                            ],
                            [
                                '_suffix' => '.*'
                            ]
                        )
                    );

                    \Plugins::registerSidebarElement('admin', 'articles', array(
                            'name' => _i('Articles'),
                            'default' => 'manage',
                            'position' => array(
                                'beforeafter' => 'before',
                                'element' => 'account'
                            ),
                            'level' => 'admin',
                            'content' => array(
                                'manage' => array('level' => 'admin', 'name' => _i('Articles'), 'icon' => 'icon-font'),
                            )
                        )
                    );
                }

                $context->getRouteCollection()->add(
                    'foolframe.plugin.articles.chan', new \Symfony\Component\Routing\Route(
                        '/_/articles/{_suffix}',
                        [
                            '_suffix' => '',
                            '_controller' => '\Foolz\Foolfuuka\Controller\Chan\Articles::articles'
                        ],
                        [
                            '_suffix' => '.*'
                        ]
                    )
                );

                Event::forge('foolframe.themes.generic_top_nav_buttons')
                    ->setCall(function($result) use ($context) {
                        $context->getService('foolframe-plugin.articles')->getNav('top', $result);
                    })
                    ->setPriority(3);

                Event::forge('foolframe.themes.generic_bottom_nav_buttons')
                    ->setCall(function($result) use ($context) {
                        $context->getService('foolframe-plugin.articles')->getNav('bottom', $result);
                    })
                    ->setPriority(3);

                Event::forge('foolframe.themes.generic.index_nav_elements')
                    ->setCall(function($result) use ($context) {
                        $context->getService('foolframe-plugin.articles')->getIndex($result);
                    })
                    ->setPriority(3);
            });
    });

Event::forge('Foolz\Foolframe\Model\Plugin::schemaUpdate.foolz/foolframe-plugin-articles')
    ->setCall(function($result) {
        /** @var $context \Foolz\Foolframe\Model\Context */
        $context = $result->getParam('context');
        /** @var DoctrineConnection $dc */
        $dc = $context->getService('doctrine');

        /** @var $schema \Doctrine\DBAL\Schema\Schema */
        /** @var $table \Doctrine\DBAL\Schema\Table */
        $schema = $result->getParam('schema');
        $table = $schema->createTable($dc->p('plugin_ff_articles'));
        if ($dc->getConnection()->getDriver()->getName() == 'pdo_mysql') {
            $table->addOption('charset', 'utf8mb4');
            $table->addOption('collate', 'utf8mb4_unicode_ci');
        }
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('slug', 'string', ['length' => 128]);
        $table->addColumn('title', 'string', ['length' => 256]);
        $table->addColumn('url', 'string', ['length' => 256]);
        $table->addColumn('content', 'text', ['length' => 65532]);
        $table->addColumn('hidden', 'smallint', ['unsigned' => true, 'default' => 0]);
        $table->addColumn('top', 'smallint', ['unsigned' => true, 'default' => 0]);
        $table->addColumn('bottom', 'smallint', ['unsigned' => true, 'default' => 0]);
        $table->addColumn('timestamp', 'integer', ['unsigned' => true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['slug'], DC::p('plugin_ff_articles_slug_index'));
        $table->addIndex(['title'], DC::p('plugin_ff_articles_title_index'));
    });
