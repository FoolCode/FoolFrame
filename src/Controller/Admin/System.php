<?php

namespace Foolz\FoolFrame\Controller\Admin;

use Foolz\FoolFrame\Model\System as S;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class System extends \Foolz\FoolFrame\Controller\Admin
{
    public function before()
    {
        parent::before();

        // set controller title
        $this->param_manager->setParam('controller_title', _i('System'));
    }

    public function security()
    {
        return $this->getAuth()->hasAccess('maccess.admin');
    }

    public function action_information()
    {
        $data = ['info' => S::getEnvironment($this->getContext())];

        $this->param_manager->setParam('method_title', _i('Information'));
        $this->builder->createPartial('body', 'system/information')
            ->getParamManager()->setParams($data);

        return new Response($this->builder->build());
    }
}
