<?php

namespace Foolz\Foolframe\Controller\Admin;

use \Foolz\Autoupgrade\Upgrade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class System extends \Foolz\Foolframe\Controller\Admin
{
    public function before()
    {
        if(!\Auth::has_access('maccess.admin')) {
            return $this->redirectToLogin();
        }

        parent::before();

        // set controller title
        $this->param_manager->setParam('controller_title', _i('System'));
    }

    public function action_information()
    {
        $data = ['info' => \System::environment()];

        $this->param_manager->setParam('method_title', _i('Information'));
        $this->builder->createPartial('body', 'system/information')
            ->getParamManager()->setParams($data);

        return new Response($this->builder->build());
    }
}
