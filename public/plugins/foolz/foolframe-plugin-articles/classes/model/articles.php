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
		static::get_by_id($id);

		DC::qb()
			->delete(DC::p('plugin_ff_articles'))
			->where('id = :id')
			->setParameter(':id', $id)
			->execute();

		static::clear_cache();
	}


	public static function clear_cache()
	{
		Cache::item('ff.plugin.articles.model.get_nav_top')->delete();
		Cache::item('ff.plugin.articles.model.get_nav_bottom')->delete();
	}


	/**
	 * Grab the whole table of articles
	 */
	public static function get_all()
	{
		$query = DC::qb()
			->select('*')
			->from(DC::p('plugin_ff_articles'), 'a');

		if ( ! \Auth::has_access('maccess.mod'))
		{
			$query->where('top', 1)
				->orWhere('bottom', 1);
		}

		$result = $query->execute()
			->fetchAll();

		return $result;
	}


	public static function get_by_slug($slug)
	{
		$query = DC::qb()
			->select('*')
			->from(DC::p('plugin_ff_articles'), 'a')
			->where('slug = :slug')
			->setParameter(':slug', $slug);


		if ( ! \Auth::has_access('maccess.mod'))
		{
			$query
				->where('top = 1')
				->orWhere('bottom = 1');
		}

		$result = $query->execute()
			->fetch();

		if ( ! $result)
		{
			throw new ArticlesArticleNotFoundException(__('The article you were looking for does not exist.'));
		}

		return $result;
	}


	public static function get_by_id($id)
	{
		$query = \DB::select()
			->from('plugin_ff-articles')
			->where('id', $id);

		if ( ! \Auth::has_access('maccess.mod'))
		{
			$query->where_open()
				->where('top', 1)
				->or_where('bottom', 1)
				->where_close();
		}

		$result = $query->as_object()
			->execute()
			->as_array();

		if ( ! count($result))
		{
			throw new ArticlesArticleNotFoundException;
		}

		return $result[0];
	}


	public static function get_top($result)
	{
		return static::get_nav('top', $result);
	}

	public static function get_bottom($result)
	{
		return static::get_nav('bottom', $result);
	}


	protected static function get_nav($where, $result)
	{
		$nav = $result->getParam('nav');

		try
		{
			$res = Cache::item('ff.plugin.articles.model.get_nav_'.$where)->get();
		}
		catch (\OutOfBoundsException $e)
		{
			$res = DC::qb()
				->select('slug, title')
				->from(DC::p('plugin_ff_articles'), 'a')
				->where($where.' = 1')
				->execute()
				->fetchAll();

			Cache::item('ff.plugin.articles.model.get_nav_'.$where)->set($res, 3600);
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


	public static function get_index($result)
	{
		$nav = $result->getParam('nav');

		$res = DC::qb()
			->select('slug, title')
			->from(DC::p('plugin_ff_articles'), 'a')
			->execute()
			->fetchAll();

		if( ! count($res))
		{
			return;
		}

		$nav['articles'] = array('title' => __('Articles'), 'elements' => array());

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
				->where('id', ':id')
				->setParameter(':id', $data['id']);

			foreach ($data as $k => $i)
			{
				$query->set($k, DC::forge()->quote($i));
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