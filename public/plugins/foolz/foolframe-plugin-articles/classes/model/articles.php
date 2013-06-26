<?php

namespace Foolz\Foolframe\Plugins\Articles\Model;

use \Foolz\Foolframe\Model\DoctrineConnection as DC;
use \Foolz\Cache\Cache;

class ArticlesArticleNotFoundException extends \Exception {};

class Articles
{
	public static function remove($id)
	{
		// this might throw ArticlesArticleNotFound, catch in controller
		static::getById($id);

		DC::qb()
			->delete(DC::p('plugin_ff_articles'))
			->where('id = :id')
			->setParameter(':id', $id)
			->execute();

		static::clear_cache();
	}

	public static function clear_cache()
	{
		Cache::item('foolframe.plugin.articles.model.get_index')->delete();
		Cache::item('foolframe.plugin.articles.model.get_nav_top')->delete();
		Cache::item('foolframe.plugin.articles.model.get_nav_bottom')->delete();
	}

	/**
	 * Grab the whole table of articles
	 */
	public static function getAll()
	{
		$query = DC::qb()
			->select('*')
			->from(DC::p('plugin_ff_articles'), 'a');

		if ( ! \Auth::has_access('maccess.mod'))
		{
			$query->where('(top = 1 OR bottom = 1)');
		}

		$result = $query->execute()
			->fetchAll();

		return $result;
	}

	public static function getById($id)
	{
		$query = DC::qb()
			->select('*')
			->from(DC::p('plugin_ff_articles'), 'a')
			->where('id = :id')
			->setParameter(':id', $id);

		if ( ! \Auth::has_access('maccess.mod'))
		{
			$query->andWhere('(top = 1 OR bottom = 1)');
		}

		$result = $query->execute()
			->fetch();

		if ( ! count($result))
		{
			throw new ArticlesArticleNotFoundException;
		}

		return $result;
	}

	public static function getBySlug($slug)
	{
		$query = DC::qb()
			->select('*')
			->from(DC::p('plugin_ff_articles'), 'a')
			->where('slug = :slug')
			->setParameter(':slug', $slug);

		if ( ! \Auth::has_access('maccess.mod'))
		{
			$query->andWhere('(top = 1 OR bottom = 1)');
		}

		$result = $query->execute()
			->fetch();

		if ( ! $result)
		{
			throw new ArticlesArticleNotFoundException(_i('The article you were looking for does not exist.'));
		}

		return $result;
	}

	public static function getTop($result)
	{
		return static::getNav('top', $result);
	}

	public static function getBottom($result)
	{
		return static::getNav('bottom', $result);
	}

	protected static function getNav($where, $result)
	{
		$nav = $result->getParam('nav');

		try
		{
			$res = Cache::item('foolframe.plugin.articles.model.get_nav_'.$where)->get();
		}
		catch (\OutOfBoundsException $e)
		{
			$res = DC::qb()
				->select('slug, title')
				->from(DC::p('plugin_ff_articles'), 'a')
				->where($where.' = 1')
				->execute()
				->fetchAll();

			Cache::item('foolframe.plugin.articles.model.get_nav_'.$where)->set($res, 3600);
		}

		if( ! count($res))
		{
			return;
		}

		foreach($res as $article)
		{
			$nav[] = array('href' => \Uri::create('_/articles/' . $article['slug']), 'text' => e($article['title']));
		}

		$result->setParam('nav', $nav)->set($nav);
	}

	public static function getIndex($result)
	{
		$nav = $result->getParam('nav');

		try
		{
			$res = Cache::item('foolframe.plugin.articles.model.get_index')->get();
		}
		catch (\OutOfBoundsException $e)
		{
			$res = DC::qb()
				->select('slug, title')
				->from(DC::p('plugin_ff_articles'), 'a')
				->execute()
				->fetchAll();

			Cache::item('foolframe.plugin.articles.model.get_index')->set($res, 3600);
		}

		if( ! count($res))
		{
			return;
		}

		$nav['articles'] = array('title' => _i('Articles'), 'elements' => array());

		foreach($res as $article)
		{
			$nav['articles']['elements'][] = array(
				'href' => \Uri::create('_/articles/' . $article['slug']),
				'text' => e($article['title'])
			);
		}

		$result->setParam('nav', $nav)->set($nav);
	}

	public static function save($data)
	{
		if (isset($data['id']))
		{
			$query = DC::qb()
				->update(DC::p('plugin_ff_articles'))
				->where('id = :id')
				->setParameter(':id', $data['id']);

			foreach ($data as $k => $i)
			{
				if ($k !== 'id')
				{
					$query->set($k, DC::forge()->quote($i));
				}
			}

			$query->execute();
		}
		else
		{
			DC::forge()
				->insert(DC::p('plugin_ff_articles'), $data);
		}

		static::clear_cache();
	}
}