<?php

namespace Foolframe\Plugins\Articles;

if (!defined('DOCROOT'))
	exit('No direct script access allowed');


class ArticlesArticleNotFoundException extends \Exception {};


class Articles extends \Plugins
{
	public static function remove($id)
	{
		// this might throw ArticlesArticleNotFound, catch in controller
		static::get_by_id($id);

		\DB::delete('plugin_ff-articles')
			->where('id', $id)
			->execute();

		static::clear_cache();
	}


	public static function clear_cache()
	{
		\Cache::delete('ff.plugin.articles.model.get_nav_top');
		\Cache::delete('ff.plugin.articles.model.get_nav_bottom');
	}


	/**
	 * Grab the whole table of articles
	 */
	public static function get_all()
	{
		$query = \DB::select()
			->from('plugin_ff-articles');

		if ( ! \Auth::has_access('maccess.mod'))
		{
			$query->where('top', 1)
				->or_where('bottom', 1);
		}

		$result = $query->as_object()
			->execute()
			->as_array();

		return $result;
	}


	public static function get_by_slug($slug)
	{
		$query = \DB::select()
			->from('plugin_ff-articles')
			->where('slug', $slug);

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
			throw new ArticlesArticleNotFoundException(__('The article you were looking for does not exist.'));
		}

		return $result[0];
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


	public static function get_nav($where, $result)
	{
		$nav = $result->getParam('nav');

		try
		{
			$res = \Cache::get('ff.plugin.articles.model.get_nav_'.$where);
		}
		catch (\CacheNotFoundException $e)
		{
			$res = \DB::select('slug', 'title')
				->from('plugin_ff-articles')
				->where($where, 1)
				->as_object()
				->execute()
				->as_array();

			\Cache::set('ff.plugin.articles.model.get_nav_'.$where, $res, 3600);
		}

		if( ! count($res))
		{
			return;
		}

		foreach($res as $article)
		{
			$nav[] = array('href' => \Uri::create('_/articles/' . $article->slug), 'text' => e($article->title));
		}

		//$result->setParam('nav', $nav)->set($nav);
	}


	public static function get_index($result)
	{
		$nav = $result->getParam('nav');

		$res = \DB::select('slug', 'title')
			->from('plugin_ff-articles')
			->as_object()
			->execute()
			->as_array();

		if( ! count($res))
		{
			return;
		}

		$nav['articles'] = array('title' => __('Articles'), 'elements' => array());

		foreach($res as $article)
		{
			$nav['articles']['elements'][] = array(
				'href' => \Uri::create('_/articles/' . $article->slug),
				'text' => e($article->title)
			);
		}

		$result->setParam('nav', $nav)->set($nav);
	}


	public static function save($data)
	{
		if (isset($data['id']))
		{
			\DB::update('plugin_ff-articles')
				->where('id', $data['id'])
				->set($data)
				->execute();
		}
		else
		{
			\DB::insert('plugin_ff-articles')
				->set($data)
				->execute();
		}

		static::clear_cache();
	}

}