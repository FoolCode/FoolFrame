<?php

namespace Foolframe\Plugins\Articles;

if (!defined('DOCROOT'))
	exit('No direct script access allowed');

class Controller_Plugin_Ff_Articles_Admin_Articles extends \Controller_Admin
{
	
	public function before()
	{
		if( ! \Auth::has_access('maccess.mod'))
		{
			\Response::redirect('admin');
		}

		parent::before();
	}
	
	public function structure()
	{
		return array(
			'open' => array(
				'type' => 'open',
			),
			'id' => array(
				'type' => 'hidden',
				'database' => TRUE,
				'validation_func' => function($input, $form_internal)
				{
					// check that the ID exists
					$query = \DB::select()
						->from('plugin_ff-articles')
						->where('id', $input['id'])
						->execute();
					if (count($query) != 1)
					{
						return array(
							'error_code' => 'ID_NOT_FOUND',
							'error' => __('Couldn\'t find the article with the submitted ID.'),
							'critical' => TRUE
						);
					}

					return array('success' => TRUE);
				}
			),
			'title' => array(
				'type' => 'input',
				'database' => TRUE,
				'label' => 'Title',
				'help' => __('The title of your article'),
				'class' => 'span4',
				'placeholder' => __('Required'),
				'validation' => 'trim|required'
			),
			'slug' => array(
				'database' => TRUE,
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
						$result = \DB::select()
							->from('plugin_ff-articles')
							->where('id', $input['id'])
							->as_object()
							->execute()
							->current();
						
						// no change?
						if ($input['slug'] == $result->slug)
						{
							// no change
							return array('success' => TRUE);
						}
					}

					// check that there isn't already an article with that name
					$result = \DB::select()
						->from('plugin_ff-articles')
						->where('slug', $input['slug'])
						->execute();
					
					if (count($result))
					{
						return array(
							'error_code' => 'ALREADY_EXISTS',
							'error' => __('The slug is already used for another board.')
						);
					}
				}
			),
			'url' => array(
				'type' => 'input',
				'database' => TRUE,
				'class' => 'span4',
				'label' => 'URL',
				'help' => __('If you set this, the article link will actually be an outlink.'),
				'validation' => 'trim'
			),
			'article' => array(
				'type' => 'textarea',
				'database' => TRUE,
				'style' => 'height:350px; width: 90%',
				'label' => __('Article'),
				'help' => __('The content of your article, in MarkDown')
			),
			'separator-1' => array(
				'type' => 'separator'
			),
			'top' => array(
				'type' => 'checkbox',
				'database' => TRUE,
				'label' => __('Display the article link on the top of the page'),
				'help' => __('Display the article link on the top of the page')
			),
			'bottom' => array(
				'type' => 'checkbox',
				'database' => TRUE,
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
		$this->_views['controller_title'] = __("Articles");
		$this->_views['method_title'] = __('Manage');

		$articles = Articles::get_all();
		
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
							<?php echo htmlentities($article->title) ?>
						</td>
						<td>
							<a href="<?php echo \Uri::create('_/articles/' . $article->slug) ?>" target="_blank"><?php echo $article->slug ?></a>
						</td>
						<td>
							<a href="<?php echo \Uri::create('admin/articles/edit/'.$article->slug) ?>" class="btn btn-mini btn-primary"><?php echo __('Edit') ?></a>
						</td>
						<td>
							<a href="<?php echo \Uri::create('admin/articles/remove/'.$article->id) ?>" class="btn btn-mini btn-danger"><?php echo __('Remove') ?></a>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

		<?php
		$this->_views["main_content_view"] = ob_get_clean();
		return \Response::forge(\View::forge('admin/default', $this->_views));
	}


	public function action_edit($slug = null)
	{
		$data['form'] = $this->structure();
		
		if (\Input::post() && ! \Security::check_token())
		{
			\Notices::set('warning', __('The security token wasn\'t found. Try resubmitting.'));
		}
		else if(\Input::post())
		{
			$result = \Validation::form_validate($data['form']);
			if (isset($result['error']))
			{
				\Notices::set('warning', $result['error']);
			}
			else
			{
				// it's actually fully checked, we just have to throw it in DB
				Articles::save($result['success']);
				if (is_null($slug))
				{
					\Notices::set_flash('success', __('New article created!'));
					\Response::redirect('admin/articles/edit/' . $result['success']['slug']);
				}
				else if ($slug != $result['success']['slug'])
				{
					// case in which letter was changed
					\Notices::set_flash('success', __('Article information updated.'));
					\Response::redirect('admin/article/edit/' . $result['success']['slug']);
				}
				else
				{
					\Notices::set('success', __('Article information updated.'));
				}
			}
		}
		
		if(!is_null($slug))
		{
			$data['object'] = Articles::get_by_slug($slug);
			if($data['object'] == FALSE)
			{
				throw new HttpServerErrorException;
			}	
			
			$this->_views["method_title"] = __('Article') . ': ' . $data['object']->slug;
		}
		else 
		{
			$this->_views["method_title"] = __('New article') ;
		}
		
		$this->_views["controller_title"] = __('Articles');
		
		$this->_views["main_content_view"] = \View::forge('admin/form_creator', $data);
		return \Response::forge(\View::forge('admin/default', $this->_views));
	}

	
	public function action_remove($id)
	{
		try
		{
			$article = Articles::get_by_id($id);
		}
		catch (ArticlesArticleNotFoundException $e)
		{
			throw new \HttpNotFoundException;
		}
		
		if (\Input::post() && ! \Security::check_token())
		{
			\Notices::set('warning', __('The security token wasn\'t found. Try resubmitting.'));
		}
		else if (\Input::post())
		{
			try
			{
				Articles::remove($id);
			}
			catch (ArticlesArticleNotFoundException $e)
			{
				throw new \HttpNotFoundException;
			}
			
			\Response::redirect('admin/articles');
		}
		
		
		$this->_views["controller_title"] = __('Articles');
		$this->_views["method_title"] = __('Removing article:') . ' ' . $article->title;
		$data['alert_level'] = 'warning';
		$data['message'] = __('Do you really want to remove the article?');

		$this->_views["main_content_view"] = \View::forge('admin/confirm', $data);
		return \Response::forge(\View::forge('admin/default', $this->_views));
		
	}
	
}