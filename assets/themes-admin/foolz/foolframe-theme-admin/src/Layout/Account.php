<?php

namespace Foolz\FoolFrame\Theme\Admin\Layout;

use Foolz\FoolFrame\Model\Legacy\Preferences;
use Foolz\FoolFrame\Model\Notices;

class Account extends \Foolz\FoolFrame\View\View
{
    public function toString()
    { ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?= _i('Login').' - '.$this->getPreferences()->get('foolframe.gen.website_title') ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link href="<?= $this->getAssetManager()->getAssetLink('bootstrap2/css/bootstrap.css') ?>" rel="stylesheet">
        <style type="text/css">
            body {
                padding-top: 40px;
                padding-bottom: 40px;
                background-color: #f5f5f5;
            }

            .alert {
                max-width: 300px;
                margin: 0 auto 20px;
            }

            .form-account {
                max-width: 300px;
                padding: 19px 29px 29px;
                margin: 0 auto 20px;
                background-color: #fff;
                border: 1px solid #e5e5e5;
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                border-radius: 5px;
                -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                -moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                box-shadow: 0 1px 2px rgba(0,0,0,.05);
            }
            .form-account .form-account-heading,
            .form-account .checkbox {
                margin-bottom: 10px;
            }
            .form-account input[type="text"],
            .form-account input[type="password"] {
                font-size: 16px;
                height: auto;
                margin-bottom: 15px;
                padding: 7px 9px;
            }
        </style>
        <link href="<?= $this->getAssetManager()->getAssetLink('bootstrap2/css/bootstrap-responsive.css') ?>" rel="stylesheet">

        <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
            <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
    </head>

    <body>
        <div class="container">
            <?= $this->getBuilder()->isPartial('body') ? $this->getBuilder()->getPartial('body')->build() : '' ?>

            <div class="alerts">
                <?php
                    /** @var Notices $notices */
                    $notices = $this->getBuilderParamManager()->getParam('notices');
                    $notices = array_merge($notices->get(), $notices->getFlash());
                ?>
                <?php foreach ($notices as $notice) : ?>
                    <div class="alert alert-"<?= $notice['level'] ?>">
                        <?php if (is_array($notice['message'])) : ?>
                            <ul>
                                <?php foreach ($notice['message'] as $message) : ?>
                                    <li><?= htmlentities($message, ENT_COMPAT | ENT_IGNORE, 'UTF-8') ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else : ?>
                            <?= htmlentities($notice['message'], ENT_COMPAT | ENT_IGNORE, 'UTF-8') ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?= $this->getSecurity()->getJsCsrfToken(); ?>
    </body>
</html>
<?php
    }
}
