<?php

namespace Foolz\Foolfuuka\Controller\Chan;

use \Foolz\Foolframe\Plugins\Articles\Model\Articles as A;
use \Foolz\Foolframe\Plugins\Articles\Model\ArticlesArticleNotFoundException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class Articles extends \Foolz\Foolfuuka\Controller\Chan
{
    /**
     * @var A
     */
    protected $articles;

    public function before()
    {
        parent::before();
        $this->articles = new A($this->getContext());
    }


    public function action_articles($slug = null)
    {
        if(is_null($slug)) {
            return $this->action_index();
        }

        try {
            $article = $this->articles->getBySlug($slug);
        } catch (ArticlesArticleNotFoundException $e) {
            return $this->action_404();
        }

        if ($article['url']) {
            return new RedirectResponse($article['url']);
        }

        $this->setLastModified($article['timestamp']);

        if (!$this->response->isNotModified($this->request)) {
            $this->builder->getProps()->addTitle($article['title']);
            $this->param_manager->setParam('section_title', $article['title']);

            $this->builder->createPartial('body', 'markdown')
                ->getParamManager()->setParam('content', $article['content']);

            $this->response->setContent($this->builder->build());
        }

        return $this->response;
    }

    public function action_index()
    {
        $articles = $this->articles->getAll();

        $this->builder->getProps()->addTitle(_('Articles'));
        $this->param_manager->setParam('section_title', _('Articles'));

        ob_start();

        include __DIR__.'/../../views/articles.php';

        $string = ob_get_clean();
        $partial = $this->builder->createPartial('body', 'plugin');
        $partial->getParamManager()->setParam('content', $string);

        return $this->response->setContent($this->builder->build());
    }
}
