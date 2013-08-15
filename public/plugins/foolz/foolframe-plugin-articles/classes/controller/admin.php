<?php

namespace Foolz\Foolframe\Controller\Admin;

use Foolz\Foolframe\Model\DoctrineConnection;
use Foolz\Foolframe\Model\Validation\Validator;
use Foolz\Foolframe\Plugins\Articles\Model\Articles as A;
use Foolz\Foolframe\Plugins\Articles\Model\ArticlesArticleNotFoundException;
use Foolz\Foolframe\Model\Validation\ActiveConstraint\Trim;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints as Assert;


class Articles extends \Foolz\Foolframe\Controller\Admin
{
    /**
     * @var A
     */
    protected $articles;

    public function before()
    {
        parent::before();

        $this->articles = $this->getContext()->getService('foolframe-plugin.articles');
    }

    public function security()
    {
        return $this->getAuth()->hasAccess('maccess.mod');
    }

    public function structure()
    {
        /** @var DoctrineConnection $dc */
        $dc = $this->getContext()->getService('doctrine');

        return array(
            'open' => array(
                'type' => 'open',
            ),
            'id' => array(
                'type' => 'hidden',
                'database' => true,
                'validation_func' => function($input, $form_internal) use ($dc) {
                    // check that the ID exists
                    /** @var DoctrineConnection $dc */
                    $count = (int) $dc->qb()
                        ->select('COUNT(*) as count')
                        ->from($dc->p('plugin_ff_articles'), 'a')
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
                'validation' => [new Trim(), new Assert\NotBlank()]
            ),
            'slug' => array(
                'database' => true,
                'type' => 'input',
                'label' => _i('Slug'),
                'help' => _i('Insert the short name of the article to use in the url. Only alphanumeric and dashes.'),
                'placeholder' => _i('Required'),
                'class' => 'span4',
                'validation' => [new Trim(), new Assert\Regex('/^\w+$/')],
                'validation_func' => function($input, $form_internal) use ($dc) {
                    // if we're working on the same object
                    if (isset($input['id'])) {
                        // existence ensured by CRITICAL in the ID check
                        /** @var DoctrineConnection $dc */
                        $result = $dc->qb()
                            ->select('*')
                            ->from($dc->p('plugin_ff_articles'), 'a')
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
                    $count = $dc->qb()
                        ->select('COUNT(*) as count')
                        ->from($dc->p('plugin_ff_articles'), 'a')
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
                'validation' => [new Trim()]
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

        $articles = $this->articles->getAll();

        ob_start();
        ?>

            <a href="<?= $this->uri->create('admin/articles/edit') ?>" class="btn" style="float:right; margin:5px"><?= _i('New article') ?></a>

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
                            <a href="<?= $this->uri->create('_/articles/' . $article['slug']) ?>" target="_blank"><?= $article['slug'] ?></a>
                        </td>
                        <td>
                            <a href="<?= $this->uri->create('admin/articles/edit/'.$article['slug']) ?>" class="btn btn-mini btn-primary"><?= _i('Edit') ?></a>
                        </td>
                        <td>
                            <a href="<?= $this->uri->create('admin/articles/remove/'.$article['id']) ?>" class="btn btn-mini btn-danger"><?= _i('Remove') ?></a>
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

        if ($this->getPost() && !\Security::check_token()) {
            $this->notices->set('warning', _i('The security token wasn\'t found. Try resubmitting.'));
        } elseif ($this->getPost()) {
            $result = Validator::formValidate($data['form']);
            if (isset($result['error'])) {
                $this->notices->set('warning', $result['error']);
            } else {
                // it's actually fully checked, we just have to throw it in DB
                $this->articles->save($result['success']);
                if (is_null($slug)) {
                    $this->notices->setFlash('success', _i('New article created!'));
                    return $this->redirect('admin/articles/edit/' . $result['success']['slug']);
                } elseif ($slug != $result['success']['slug']) {
                    // case in which letter was changed
                    $this->notices->setFlash('success', _i('Article information updated.'));
                    return $this->redirect('admin/articles/edit/' . $result['success']['slug']);
                } else {
                    $this->notices->set('success', _i('Article information updated.'));
                }
            }
        }

        if (!is_null($slug)) {
            try {
                $article = $this->articles->getBySlug($slug);
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
            $article = $this->articles->getById($id);
        } catch (ArticlesArticleNotFoundException $e) {
            throw new NotFoundHttpException;
        }

        if ($this->getPost() && !\Security::check_token()) {
            $this->notices->set('warning', _i('The security token wasn\'t found. Try resubmitting.'));
        } elseif ($this->getPost()) {
            try {
                $this->articles->remove($id);
            } catch (ArticlesArticleNotFoundException $e) {
                throw new NotFoundHttpException;
            }

            return $this->redirect('admin/articles/manage');
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
