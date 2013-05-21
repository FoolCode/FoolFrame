<?php

namespace Foolz\Foolframe\Controller\Admin;

use \Foolz\Foolframe\Model\DoctrineConnection as DC,
	\Foolz\Foolframe\Plugins\Articles\Model\Articles as A,
	\Foolz\Foolframe\Plugins\Articles\Model\ArticlesArticleNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Articles extends \Foolz\Foolframe\Controller\Admin
{

	public function before(Request $request)
	{
		if ( ! \Auth::has_access('maccess.mod'))
		{
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
				'validation_func' => function($input, $form_internal)
				{
					// check that the ID exists
					$count = (int) DC::qb()
						->select('COUNT(*) as count')
						->from(DC::p('plugin_ff_articles'), 'a')
						->where('id = :id')
						->setParameter(':id', $input['id'])
						->execute()
						->fetch()['count'];

					if ($count !== 1)
					{
						return array(
							'error_code' => 'ID_NOT_FOUND',
							'error' => __('Couldn\'t find the article with the submitted ID.'),
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
				'help' => __('The title of your article'),
				'class' => 'span4',
				'placeholder' => __('Required'),
				'validation' => 'trim|required'
			),
			'slug' => array(
				'database' => true,
				'type' => 'input',
				'label' => __('Slug'),
				'help' => __('Insert the short name of the article to use in the url. Only alphanumeric and dashes.'),
				'placeholder' => __('Required'),
				'class' => 'span4',
				'validation' => 'required|valid_string[alpha,dashes,numeric]',
				'validation_func' => function($input, $form_internal)
				{
					// if we're working on the same object
					if (isset($input['id']))
					{
						// existence ensured by CRITICAL in the ID check
						$result = DC::qb()
							->select('*')
							->from(DC::p('plugin_ff_articles'), 'a')
							->where('id = :id')
							->setParameter(':id', $input['id'])
							->execute()
							->fetch();

						// no change?
						if ($input['slug'] == $result['slug'])
						{
							// no change
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

					if ($count)
					{
						return array(
							'error_code' => 'ALREADY_EXISTS',
							'error' => __('The slug is already being used for another board.')
						);
					}
				}
			),
			'url' => array(
				'type' => 'input',
				'database' => true,
				'class' => 'span4',
				'label' => 'URL',
				'help' => __('If you set this, the article link will be an outlink.'),
				'validation' => 'trim'
			),
			'article' => array(
				'type' => 'textarea',
				'database' => true,
				'style' => 'height:350px; width: 90%',
				'label' => __('Article'),
				'help' => __('The content of your article, in MarkDown')
			),
			'separator-1' => array(
				'type' => 'separator'
			),
			'top' => array(
				'type' => 'checkbox',
				'database' => true,
				'label' => __('Display the article link on the top of the page'),
				'help' => __('Display the article link on the top of the page')
			),
			'bottom' => array(
				'type' => 'checkbox',
				'database' => true,
				'label' => __('Display the article link on the bottom of the page'),
				'help' => __('Display the article link on the bottom of the page')
			),
			'separator-2' => array(
				'type' => 'separator-short'
			),
			'submit' => array(
				'type' => 'submit',
				'class' => 'btn-primary',
				'value' => __('Submit')
			),
			'close' => array(
				'type' => 'close'
			),
		);
	}

	public function action_manage()
	{
		$this->param_manager->setParam('controller_title', __("Articles"));
		$this->param_manager->setParam('method_title', __('Manage'));

		$articles = A::getAll();

		ob_start();
		?>

			<a href="<?php echo \Uri::create('admin/articles/edit') ?>" class="btn" style="float:right; margin:5px"><?php echo __('New article') ?></a>

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
							<?php echo htmlentities($article['title']) ?>
						</td>
						<td>
							<a href="<?php echo \Uri::create('_/articles/' . $article['slug']) ?>" target="_blank"><?php echo $article['slug'] ?></a>
						</td>
						<td>
							<a href="<?php echo \Uri::create('admin/articles/edit/'.$article['slug']) ?>" class="btn btn-mini btn-primary"><?php echo __('Edit') ?></a>
						</td>
						<td>
							<a href="<?php echo \Uri::create('admin/articles/remove/'.$article['id']) ?>" class="btn btn-mini btn-danger"><?php echo __('Remove') ?></a>
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

		if (\Input::post() && ! \Security::check_token())
		{
			\Notices::set('warning', __('The security token wasn\'t found. Try resubmitting.'));
		}
		elseif (\Input::post())
		{
			$result = \Validation::form_validate($data['form']);
			if (isset($result['error']))
			{
				\Notices::set('warning', $result['error']);
			}
			else
			{
				// it's actually fully checked, we just have to throw it in DB
				A::save($result['success']);
				if (is_null($slug))
				{
					\Notices::setFlash('success', __('New article created!'));
					\Response::redirect('admin/articles/edit/' . $result['success']['slug']);
				}
				elseif ($slug != $result['success']['slug'])
				{
					// case in which letter was changed
					\Notices::setFlash('success', __('Article information updated.'));
					\Response::redirect('admin/articles/edit/' . $result['success']['slug']);
				}
				else
				{
					\Notices::set('success', __('Article information updated.'));
				}
			}
		}

		if ( ! is_null($slug))
		{
			try
			{
				$article = A::getBySlug($slug);
				$data['object'] = (object) $article;
			}
			catch (ArticlesArticleNotFoundException $e)
			{
				throw new \HttpNotFoundException;
			}

			$this->param_manager->setParam('method_title', [__('Edit'), $article['slug']]);
		}
		else
		{
			$this->param_manager->setParam('method_title', __('New'));
		}

		$this->param_manager->setParam('controller_title', __('Articles'));
		$this->builder->createPartial('body', 'form_creator')
			->getParamManager()->setParams($data);

		return new Response($this->builder->build());
	}

	public function action_remove($id)
	{
		try
		{
			$article = A::getById($id);
		}
		catch (ArticlesArticleNotFoundException $e)
		{
			throw new \HttpNotFoundException;
		}

		if (\Input::post() && ! \Security::check_token())
		{
			\Notices::set('warning', __('The security token wasn\'t found. Try resubmitting.'));
		}
		elseif (\Input::post())
		{
			try
			{
				A::remove($id);
			}
			catch (ArticlesArticleNotFoundException $e)
			{
				throw new \HttpNotFoundException;
			}

			\Response::redirect('admin/articles/manage');
		}

		$this->param_manager->setParam('controller_title', __('Articles'));
		$this->param_manager->setParam('method_title', __('Delete') . ' ' . $article['title']);
		$data['alert_level'] = 'warning';
		$data['message'] = __('Do you really want to remove the article?');

		$this->builder->createPartial('body', 'confirm')
			->getParamManager()->setParams($data);

		return new Response($this->builder->build());

	}
}