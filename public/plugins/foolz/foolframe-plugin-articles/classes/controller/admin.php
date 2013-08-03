<?php

namespace Foolz\Foolframe\Controller\Admin;

use Foolz\Foolframe\Model\Legacy\DoctrineConnection as DC,
    \Foolz\Foolframe\Plugins\Articles\Model\Articles as A,
    \Foolz\Foolframe\Plugins\Articles\Model\ArticlesArticleNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Articles extends \Foolz\Foolframe\Controller\Admin
{

    public function before(Request $request)
    {
        if (!\Auth::has_access('maccess.mod')) {
            \Response::redirect('admin');
        }

        parent::before($request);
    }

    public function structure()
    {
        return array(
            'open' => array(
                'type' => 'open',
            ),
            'id' => array(
                'type' => 'hidden',
                'database' => true,
                'validation_func' => function($input, $form_internal) {
                    // check that the ID exists
                    $count = (int) DC::qb()
                        ->select('COUNT(*) as count')
                        ->from(DC::p('plugin_ff_articles'), 'a')
                        ->where('id = :id')
                        ->setParameter(':id', $input['id'])
                        ->execute()
                        ->fetch()['count'];

                    if ($count !== 1) {
                        return array(
                            'error_code' => 'ID_NOT_FOUND',
                            'error' => _i('Couldn\'t find the article with the submitted ID.'),
                            'critical' => true
                        );
                    }

                    return array('success' => true);
                }
            ),
            'title' => array(
                'type' => 'input',
                'database' => true,
                'label' => 'Title',
                'help' => _i('The title of your article'),
                'class' => 'span4',
                'placeholder' => _i('Required'),
                'validation' => 'trim|required'
            ),
            'slug' => array(
                'database' => true,
                'type' => 'input',
                'label' => _i('Slug'),
                'help' => _i('Insert the short name of the article to use in the url. Only alphanumeric and dashes.'),
                'placeholder' => _i('Required'),
                'class' => 'span4',
                'validation' => 'required|valid_string[alpha,dashes,numeric]',
                'validation_func' => function($input, $form_internal) {
                    // if we're working on the same object
                    if (isset($input['id'])) {
                        // existence ensured by CRITICAL in the ID check
                        $result = DC::qb()
                            ->select('*')
                            ->from(DC::p('plugin_ff_articles'), 'a')
                            ->where('id = :id')
                            ->setParameter(':id', $input['id'])
                            ->execute()
                            ->fetch();

                        // no change?
                        if ($input['slug'] == $result['slug']) {
                            return array('success' => true);
                        }
                    }

                    // check that there isn't already an article with that name
                    $count = DC::qb()
                        ->select('COUNT(*) as count')
                        ->from(DC::p('plugin_ff_articles'), 'a')
                        ->where('slug = :slug')
                        ->setParameter(':slug', $input['slug'])
                        ->execute()
                        ->fetch()['count'];

                    if ($count) {
                        return array(
                            'error_code' => 'ALREADY_EXISTS',
                            'error' => _i('The slug is already being used for another board.')
                        );
                    }
                }
            ),
            'url' => array(
                'type' => 'input',
                'database' => true,
                'class' => 'span4',
                'label' => 'URL',
                'help' => _i('If you set this, the article link will be an outlink.'),
                'validation' => 'trim'
            ),
            'content' => array(
                'type' => 'textarea',
                'database' => true,
                'style' => 'height:350px; width: 90%',
                'label' => _i('Article'),
                'help' => _i('The content of your article, in MarkDown')
            ),
            'separator-1' => array(
                'type' => 'separator'
            ),
            'hidden' => array(
                'type' => 'checkbox',
                'database' => true,
                'help' => _i('Disable access to the article.')
            ),
            'separator-1' => array(
                'type' => 'separator'
            ),
            'top' => array(
                'type' => 'checkbox',
                'database' => true,
                'help' => _i('Display the article link on the top of the page')
            ),
            'bottom' => array(
                'type' => 'checkbox',
                'database' => true,
                'help' => _i('Display the article link on the bottom of the page')
            ),
            'separator-2' => array(
                'type' => 'separator-short'
            ),
            'submit' => array(
                'type' => 'submit',
                'class' => 'btn-primary',
                'value' => _i('Submit')
            ),
            'close' => array(
                'type' => 'close'
            ),
        );
    }

    public function action_manage()
    {
        $this->param_manager->setParam('controller_title', _i("Articles"));
        $this->param_manager->setParam('method_title', _i('Manage'));

        $articles = A::getAll();

        ob_start();
        ?>

            <a href="<?= \Uri::create('admin/articles/edit') ?>" class="btn" style="float:right; margin:5px"><?= _i('New article') ?></a>

            <table class="table table-bordered table-striped table-condensed">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Edit</th>
                        <th>Remove</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach($articles as $article) : ?>
                    <tr>
                        <td>
                            <?= htmlentities($article['title']) ?>
                        </td>
                        <td>
                            <a href="<?= \Uri::create('_/articles/' . $article['slug']) ?>" target="_blank"><?= $article['slug'] ?></a>
                        </td>
                        <td>
                            <a href="<?= \Uri::create('admin/articles/edit/'.$article['slug']) ?>" class="btn btn-mini btn-primary"><?= _i('Edit') ?></a>
                        </td>
                        <td>
                            <a href="<?= \Uri::create('admin/articles/remove/'.$article['id']) ?>" class="btn btn-mini btn-danger"><?= _i('Remove') ?></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php
        $this->builder->createPartial('body', 'content')
            ->getParamManager()->setParam('content', ob_get_clean());

        return new Response($this->builder->build());
    }

    public function action_edit($slug = null)
    {
        $data['form'] = $this->structure();

        if (\Input::post() && !\Security::check_token()) {
            \Notices::set('warning', _i('The security token wasn\'t found. Try resubmitting.'));
        } elseif (\Input::post()) {
            $result = \Validation::form_validate($data['form']);
            if (isset($result['error'])) {
                \Notices::set('warning', $result['error']);
            } else {
                // it's actually fully checked, we just have to throw it in DB
                A::save($result['success']);
                if (is_null($slug)) {
                    \Notices::setFlash('success', _i('New article created!'));
                    \Response::redirect('admin/articles/edit/' . $result['success']['slug']);
                } elseif ($slug != $result['success']['slug']) {
                    // case in which letter was changed
                    \Notices::setFlash('success', _i('Article information updated.'));
                    \Response::redirect('admin/articles/edit/' . $result['success']['slug']);
                } else {
                    \Notices::set('success', _i('Article information updated.'));
                }
            }
        }

        if (!is_null($slug)) {
            try {
                $article = A::getBySlug($slug);
                $data['object'] = (object) $article;
            } catch (ArticlesArticleNotFoundException $e) {
                throw new NotFoundHttpException;
            }

            $this->param_manager->setParam('method_title', [_i('Edit'), $article['slug']]);
        } else {
            $this->param_manager->setParam('method_title', _i('New'));
        }

        $this->param_manager->setParam('controller_title', _i('Articles'));
        $this->builder->createPartial('body', 'form_creator')
            ->getParamManager()->setParams($data);

        return new Response($this->builder->build());
    }

    public function action_remove($id)
    {
        try {
            $article = A::getById($id);
        } catch (ArticlesArticleNotFoundException $e) {
            throw new NotFoundHttpException;
        }

        if (\Input::post() && !\Security::check_token()) {
            \Notices::set('warning', _i('The security token wasn\'t found. Try resubmitting.'));
        } elseif (\Input::post()) {
            try {
                A::remove($id);
            } catch (ArticlesArticleNotFoundException $e) {
                throw new NotFoundHttpException;
            }

            \Response::redirect('admin/articles/manage');
        }

        $this->param_manager->setParam('controller_title', _i('Articles'));
        $this->param_manager->setParam('method_title', _i('Delete') . ' ' . $article['title']);
        $data['alert_level'] = 'warning';
        $data['message'] = _i('Do you really want to remove the article?');

        $this->builder->createPartial('body', 'confirm')
            ->getParamManager()->setParams($data);

        return new Response($this->builder->build());

    }
}
