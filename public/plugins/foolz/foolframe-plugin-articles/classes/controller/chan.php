<?php

namespace Foolz\Foolfuuka\Controller\Chan;

use \Foolz\Foolframe\Plugins\Articles\Model\Articles as A,
	\Foolz\Foolframe\Plugins\Articles\Model\ArticlesArticleNotFoundException;

class Articles extends \Foolz\Foolfuuka\Controller\Chan
{

	public function action_articles($slug = null)
	{
		if(is_null($slug))
		{
			throw new \HttpNotFoundException;
		}

		try
		{
			$article = A::getBySlug($slug);
		}
		catch (ArticlesArticleNotFoundException $e)
		{
			throw new \HttpNotFoundException;
		}

		if ($article['url'])
		{
			\Response::redirect($article['url']);
		}

		$this->builder->getProps()->addTitle($article['title']);
		$this->param_manager->setParam('section_title', $article['title']);

		$this->builder->createPartial('body', 'markdown')
			->getParamManager()->setParam('content', $article['article']);

		return \Response::forge($this->builder->build());
	}
}