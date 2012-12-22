<?php

namespace Foolframe\Plugins\Articles;

if (!defined('DOCROOT'))
	exit('No direct script access allowed');

class Controller_Plugin_Ff_Articles_Chan extends \Foolfuuka\Controller_Chan
{
	
	public function action_articles($slug = null)
	{
		if(is_null($slug))
		{
			throw new \HttpNotFoundException;
		}

		try
		{
			$article = Articles::get_by_slug($slug);
		}
		catch (ArticlesArticleNotFoundException $e)
		{
			throw new \HttpNotFoundException;
		}

		if($article->url)
		{
			\Response::redirect($article->url);
		}

		$this->_theme->set_title(e($article->title) . ' « ' . \Preferences::get('ff.gen.website_title'));
		$this->_theme->bind('section_title', $article->title);
		$this->_theme->bind('content', $article->article);
		return \Response::forge($this->_theme->build('markdown'));
	}
}