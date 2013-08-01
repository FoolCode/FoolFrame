<?php

namespace Foolz\Foolframe\Theme\Admin\Layout;

use Foolz\Foolframe\Model\Notices;

class Base extends \Foolz\Theme\View
{

    public function toString()
    { ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?= $this->getBuilder()->getProps()->getTitle() ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" type="text/css" href="<?= $this->getAssetManager()->getAssetLink('bootstrap2/css/bootstrap.min.css') ?>" />
        <link rel="stylesheet" type="text/css" href="<?= $this->getAssetManager()->getAssetLink('bootstrap2/css/bootstrap-responsive.min.css') ?>" />
        <link rel="stylesheet" type="text/css" href="<?= $this->getAssetManager()->getAssetLink('admin.css') ?>" />
        <script type="text/javascript" src="<?= $this->getAssetManager()->getAssetLink('jquery.js') ?>"></script>
        <script type="text/javascript" src="<?= $this->getAssetManager()->getAssetLink('bootstrap2/js/bootstrap.min.js') ?>"></script>
        <link rel="stylesheet" type="text/css" href="<?= $this->getAssetManager()->getAssetLink('font-awesome/css/font-awesome.css') ?>" />
        <!--[if lt IE 8]>
            <link href="<?= $this->getAssetManager()->getAssetLink('font-awesome/css/font-awesome-ie7.css') ?>" rel="stylesheet" type="text/css" />
        <![endif]-->
        <script type="text/javascript" src="<?= $this->getAssetManager()->getAssetLink('admin.js') ?>"></script>
        <style type="text/css">
            .admin-container {
                position: relative;
                margin: 15px 0;
                padding: 15px 15px 10px;
                background-color: #fff;
                border: 1px solid #ddd;
                -webkit-border-radius: 4px;
                -moz-border-radius: 4px;
                border-radius: 4px;
            }

            .admin-container-header {
                position: relative;
                top: -16px;
                left: -16px;
                padding: 3px 7px;
                font-size: 12px;
                font-weight: bold;
                background-color: #f5f5f5;
                border: 1px solid #ddd;
                color: #9da0a4;
                -webkit-border-radius: 4px 0 4px 0;
                -moz-border-radius: 4px 0 4px 0;
                border-radius: 4px 0 4px 0;
            }

            .sidebar-nav {
                padding: 9px 0;
            }
        </style>
    </head>

    <body>
        <?= $this->getBuilder()->isPartial('navbar') ? $this->getBuilder()->getPartial('navbar')->build() : '' ?>

        <div class="container-fluid">
            <div class="row-fluid">
                <div class="span3">
                    <?= $this->getBuilder()->isPartial('sidebar') ? $this->getBuilder()->getPartial('sidebar')->build() : '' ?>
                </div>

                <div class="span9">
                    <ul class="breadcrumb">
                        <li><?= $this->getBuilderParamManager()->getParam('controller_title') ?></li>

                        <?php
                        $method_title = $this->getBuilderParamManager()->getParam('method_title', false);
                        if (is_array($method_title)) {
                            $count = 1;
                            $total = count($method_title);
                            foreach ($method_title as $title) {
                                echo ' <span class="divider">/</span> ';

                                if ($count == $total) {
                                    echo '<li class="active">'.htmlentities($title).'</li>';
                                } else {
                                    echo '<li>'.htmlentities($title).'</li>';
                                }

                                $count++;
                            }
                        } elseif ($method_title) {
                            echo ' <span class="divider">/</span> <li class="active">'.htmlentities($method_title).'</li>';
                        }
                        ?>
                    </ul>

                    <div class="alerts">
                        <?php $notices = array_merge(Notices::get(), Notices::getFlash()); ?>
                        <?php foreach ($notices as $notice) : ?>
                            <div class="alert alert-<?= $notice['level'] ?>">
                                <?php if (is_array($notice['message'])) : ?>
                                    <ul>
                                        <?php foreach ($notice['message'] as $message) : ?>
                                            <li><?= nl2br(htmlentities($message, ENT_COMPAT | ENT_IGNORE, 'UTF-8')) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else : ?>
                                    <?= nl2br(htmlentities($notice['message'], ENT_COMPAT | ENT_IGNORE, 'UTF-8')) ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?= $this->getBuilder()->isPartial('body') ? $this->getBuilder()->getPartial('body')->build() : '' ?>
                </div>
                <div style="clear:both"></div>
            </div>
        </div>

        <?= \Security::js_set_token(); ?>
    </body>
</html>
<?php
    }
}
