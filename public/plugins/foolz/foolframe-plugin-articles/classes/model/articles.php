<?php

namespace Foolz\Foolframe\Plugins\Articles\Model;

use Foolz\Foolframe\Model\Context;
use Foolz\Foolframe\Model\DoctrineConnection;
use Foolz\Cache\Cache;
use Foolz\Foolframe\Model\Model;
use Foolz\Foolframe\Model\Uri;

class ArticlesArticleNotFoundException extends \Exception {};

class Articles extends Model
{
    /**
     * @var DoctrineConnection
     */
    protected $dc;

    /**
     * @var Uri
     */
    protected $uri;

    public function __construct(Context $context)
    {
        parent::__construct($context);

        $this->dc = $this->getContext()->getService('doctrine');
        $this->uri = $this->getContext()->getService('uri');
    }

    public function remove($id)
    {
        // this might throw ArticlesArticleNotFound, catch in controller
        $this->getById($id);

        $this->dc->qb()
            ->delete($this->dc->p('plugin_ff_articles'))
            ->where('id = :id')
            ->setParameter(':id', $id)
            ->execute();

        static::clear_cache();
    }

    public function clear_cache()
    {
        Cache::item('foolframe.plugin.articles.model.get_index')->delete();
        Cache::item('foolframe.plugin.articles.model.get_nav_top')->delete();
        Cache::item('foolframe.plugin.articles.model.get_nav_bottom')->delete();
    }

    /**
     * Grab the whole table of articles
     */
    public function getAll()
    {
        $query = $this->dc->qb()
            ->select('*')
            ->from($this->dc->p('plugin_ff_articles'), 'a');

        if (!$this->getAuth()->hasAccess('maccess.mod')) {
            $query->where('hidden = 0');
        }

        $result = $query->orderBy('title', 'asc')
            ->execute()
            ->fetchAll();

        return $result;
    }

    public function getById($id)
    {
        $query = $this->dc->qb()
            ->select('*')
            ->from($this->dc->p('plugin_ff_articles'), 'a')
            ->where('id = :id')
            ->setParameter(':id', $id);

        if (!$this->getAuth()->hasAccess('maccess.mod')) {
            $query->andWhere('hidden = 0');
        }

        $result = $query->execute()
            ->fetch();

        if (!count($result)) {
            throw new ArticlesArticleNotFoundException;
        }

        return $result;
    }

    public function getBySlug($slug)
    {
        $query = $this->dc->qb()
            ->select('*')
            ->from($this->dc->p('plugin_ff_articles'), 'a')
            ->where('slug = :slug')
            ->setParameter(':slug', $slug);

        if (!$this->getAuth()->hasAccess('maccess.mod')) {
            $query->andWhere('hidden = 0');
        }

        $result = $query->execute()
            ->fetch();

        if (!$result) {
            throw new ArticlesArticleNotFoundException(_i('The article you were looking for does not exist.'));
        }

        return $result;
    }

    public function getNav($where, $result)
    {
        $nav = $result->getParam('nav');

        try {
            $res = Cache::item('foolframe.plugin.articles.model.get_nav_'.$where)->get();
        } catch (\OutOfBoundsException $e) {
            $res = $this->dc->qb()
                ->select('slug, title')
                ->from($this->dc->p('plugin_ff_articles'), 'a')
                ->where($where.' = 1')
                ->execute()
                ->fetchAll();

            Cache::item('foolframe.plugin.articles.model.get_nav_'.$where)->set($res, 3600);
        }

        if(!count($res)) {
            return;
        }

        foreach($res as $article) {
            $nav[] = array('href' => $this->uri->create('_/articles/' . $article['slug']), 'text' => e($article['title']));
        }

        $result->setParam('nav', $nav)->set($nav);
    }

    public function getIndex($result)
    {
        $nav = $result->getParam('nav');

        try {
            $res = Cache::item('foolframe.plugin.articles.model.get_index')->get();
        } catch (\OutOfBoundsException $e) {
            $res = $this->dc->qb()
                ->select('slug, title')
                ->from($this->dc->p('plugin_ff_articles'), 'a')
                ->orderBy('title', 'asc')
                ->execute()
                ->fetchAll();

            Cache::item('foolframe.plugin.articles.model.get_index')->set($res, 3600);
        }

        if(!count($res)) {
            return;
        }

        $nav['articles'] = array('title' => _i('Articles'), 'elements' => array());

        foreach($res as $article) {
            $nav['articles']['elements'][] = array(
                'href' => $this->uri->create('_/articles/' . $article['slug']),
                'text' => e($article['title'])
            );
        }

        $result->setParam('nav', $nav)->set($nav);
    }

    public function save($data)
    {
        if (isset($data['id'])) {
            $query = $this->dc->qb()
                ->update($this->dc->p('plugin_ff_articles'))
                ->set('timestamp', ':time')
                ->where('id = :id')
                ->setParameter(':id', $data['id'])
                ->setParameter(':time', time());

            foreach ($data as $k => $i) {
                if ($k !== 'id') {
                    $query->set($k, $this->dc->getConnection()->quote($i));
                }
            }

            $query->execute();
        } else {
            $data['timestamp'] = time();

            $this->dc->getConnection()
                ->insert($this->dc->p('plugin_ff_articles'), $data);
        }

        $this->clear_cache();
    }
}
