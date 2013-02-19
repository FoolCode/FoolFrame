<?php

/*
 * How to use: just as any view.
 *
 * $form = $this->load->view('admin/Form::creator', $data, TRUE);
 *
 * The $data must have the $data['form'] index. The keys in the array are the
 * name="" field.
 *
 * 	$form = array(
 * 			'open' => array(
 * 				'type' => 'open',
 * 				'hidden' => array(
 * 					'id' => NULL
 * 				)
 * 			)
 * 			'name' => array(
 * 				'type' => 'input',
 * 				'label' => __('Name'),
 * 				'help' => __('Insert the name of your mom.'),
 * 				'placeholder' => __('Required'),
 * 				'class' => 'span3',
 * 				'validation' => 'required'
 * 			),
 * 			'separator-1' => array( // keep the key different
 * 				'type' => 'separator' // prints a separator
 * 			),
 * 			'a-checkbox' => array(
 * 				'type' => 'checkbox',
 * 				'checked' => TRUE
 * 				'value' => 1 // defaults to 1 if not inserted
 * 				'help' => __('A checkbox example')
 * 			),
 * ...
 * 		);
 *
 * Values outside of $not_input will be sent to the Form:: function (where applicable).
 *
 *
 *
 * $object if set will automatically populate the fields
 * $object must be an object, so you need $board->archive, with the key being
 * the same name as the name=""
 *
 *
 *
 */
?>

<div class="admin-container"<?php echo (isset($parent))?' data-form-parent="' . $parent . '"':'';
	echo ((isset($hide) && $hide === TRUE)?' style="display:none"':'');
?>>

	<?php
	foreach ($form as $name => $item) :

		// separate up the array so we can put the rest in the form function
		$not_input = array(
			'help',
			'label',
			'validation',
			'validation_func',
			'preferences',
			'array',
			'sub',
			'sub_inverse',
			'checkboxes',
			'checked',
			'array_key',
			'boards_preferences',
			'default_value'
		);
		$helpers = array();
		foreach ($not_input as $not)
		{
			if (isset($item[$not]))
			{
				$helpers[$not] = $item[$not];
				unset($item[$not]);
			}
		}

		// PHP doesn't allow periods in POST array
		$name = str_replace('.', ',', $name);;
		if (isset($item['name']))
		{
			$item['name'] = str_replace('.', ',', $item['name']);
		}

		// support for HTML form arrays
		if(isset($helpers['array']) && $helpers['array'])
		{
			$item['name'] = $name . '[]';

			$item['value_array'] = array();

			if (\Input::post($item['name']))
			{
				$item['value_array'] = \Input::post($item['name']);
				$item['value_array'] = array_filter($item['value_array']);
			}
			else
			{
				if(isset($item['value']))
					$item['value_array'] = unserialize($item['value_array']);
			}



			$count = count($item['value_array'])+1;
		}
		else
		{
			$item['name'] = $name;

			if (\Input::post($item['name']))
			{
				$item['value'] = \Input::post($item['name']);
			}

			$count = 1;
		}


		// loop all the array to generate the html
		if (isset($item['type'])) :
			for($i = 0; $i < $count; $i++) :
				if(isset($item['value_array']))
				{
					//$item['value'] = $item['value_array'][$i];
				}

				switch ($item['type']):

					// internal variable that goes into database but is not public in any way
					case 'internal':
						break;

					case 'separator':
						?>
						<br/><br/>
						<?php
						break;

					case 'separator-short':
						?>
						<br/>
						<?php
						break;


					case 'paragraph':
						?>
						<p><?php echo $helpers['help'] ?></p>
						<?php
						break;


					case 'open':
						$open_default_attr = array('onsubmit' => 'fuel_set_csrf_token(this);');
						echo Form::open(
							isset($item['attributes']) ? merge($item['attributes'], $open_default_attr) : $open_default_attr,
							isset($item['hidden']) ? $item['hidden'] : array()
						);

						echo \Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());
						break;


					case 'close':
						echo Form::close(); // I know there's a variable there but it's useless
						break;


					case 'hidden':
						// to keep maximum functionality we want one value per hidden
						if (isset($item['value']) && is_array($item['value']))
						{
							// better not supporting it, things might get messy
							log_message('error',
								'The form automator doesn\'t support arrays of hidden values in Form::hidden.');
							show_error('The form automator doesn\'t support arrays of hidden values in Form::hidden.');
						}


						// this is outputted only if we actually have a value
						// it will never be inserted by the user so don't take care of repopulation
						if (isset($object->$name))
						{
							$item['value'] = $object->$name;
						}

						if (isset($item['value']))
						{
							echo Form::hidden($name, $item['value']);
						}
						break;


					case 'submit':
					case 'reset':
						echo call_user_func('Form::' . $item['type'], $item);
						break;


					case 'radio':
						?>
						<div style="margin: 0px 0px 15px;">
							<?php
							echo '<label>'.$helpers['help'].'</label>';
							foreach ($item['radio_values'] as $radio_key => $radio_value)
							{
								if (isset($object->$name) && $object->$name == $radio_key)
								{
									$checked = TRUE;
								}
								else
								{
									$checked = FALSE;
								}

								?>
								<label class="radio">
									<?php
									echo Form::radio($name, $radio_key, $checked)
									?>
									<?php echo $radio_value ?>
								</label>
								<?php
							}
							?>
						</div>
						<?php
						break;


					case 'checkbox':
						if (!isset($item['value']))
						{
							$item['value'] = 1;
						}

						if (isset($helpers['preferences']) && $helpers['preferences'])
						{
							$checked = \Preferences::get(str_replace(',', '.', $name));

							if(isset($helpers['array_key']))
							{
								$checked = @unserialize($checked);

								if(isset($checked[$helpers['array_key']]))
								{
									$checked = $checked[$helpers['array_key']];
								}
								else
								{
									// do we have a fallback in the array?
									if(isset($helpers['checked']) && $helpers['checked'] == TRUE)
									{
										$checked = TRUE;
									}
									else
									{
										$checked = FALSE;
									}
								}
							}
						}
						else
						{
							$checked = isset($object->$name) ? $object->$name : FALSE;
						}

						$extra = array();
						if(isset($helpers['sub']))
						{
							$extra['data-function'] = 'hasSubForm';
						}

						if(isset($item['disabled']))
						{
							$extra['disabled'] = 'disabled';
						}
						?>
						<label class="checkbox">
							<?php
							echo Form::checkbox($name, $item['value'], $checked, $extra)
							?>
							<?php echo $helpers['help'] ?>
						</label>
						<?php

						// sub and sub_inverse, respectively popup and appear by default
						if(isset($helpers['sub']))
						{
							$data = array('form' => $helpers['sub']);
							if(!$checked)
								$data['hide'] = TRUE;
							else
								$data['hide'] = FALSE;
							if (isset($object)) $data['object'] = $object;
							$data['parent'] = $name;
							echo View::forge('foolz/foolframe::admin/form_creator', $data);
						}

						if(isset($helpers['sub_inverse']))
						{
							$data = array('form' => $helpers['sub_inverse']);
							if($checked)
								$data['hide'] = TRUE;
							else
								$data['hide'] = FALSE;
							if (isset($object)) $data['object'] = $object;
							$data['parent'] = $name . '_inverse';
							echo \View::forge('foolz/foolframe::admin/form_creator', $data);
						}

						break;

					case 'checkbox_array':
						$data_form = array();
						if (!isset($item['value']))
						{
							if($unserialized = unserialize(\Preferences::get(str_replace(',', '.', $name))))
							{
								$item['value'] = $unserialized;
							}
							else
							{
								$item['value'] = array();
							}
						}

						foreach($helpers['checkboxes'] as $checkbox)
						{
							$checked = FALSE;
							if(isset($item['value'][$checkbox['array_key']]))
							{
								$checked = (bool) $item['value'][$checkbox['array_key']];
							}
							elseif(isset($checkbox['checked']))
							{
								$checked = $checkbox['checked'];
							}

							$data_form[$item['name'].'[' . $checkbox['array_key'] . ']'] =
								array_merge($checkbox, array(
									'type' => 'checkbox', 'value' => 1, 'checked' => $checked
								)
							);
						}
						echo $helpers['help'];
						echo View::forge('foolz/foolframe::admin/form_creator', array('form' => $data_form));
						break;

					case 'select':
						?>
						<label><?php echo $helpers['label'] ?></label>
						<?php
						if (isset($helpers['preferences']) && $helpers['preferences'])
						{
							$item['selected'] = \Preferences::get(str_replace(',', '.', $name));
						}
						elseif (isset($item['value']))
						{
							$item['selected'] = $item['value'];
						}
						elseif (isset($object->$name))
						{
							$item['selected'] = $object->$name;
						}
						elseif (isset($helpers['default_value']))
						{
							$item['selected'] = $helpers['default_value'];
						}

						echo Form::select($name, $item['selected'], $item['options']);
						?>
						<span class="help-inline">
							<?php
							echo isset($helpers['help']) ? $helpers['help'] : NULL;
							?>
						</span>
						<?php
						break;


					case 'input':
					case 'password':
					case 'upload':
					case 'textarea':
					case 'multiselect':
					case 'button':

						$helper['type'] = $item['type'];
						unset($item['type']);

						if (!isset($item['value']))
						{
							if (isset($helpers['preferences']) && $helpers['preferences'])
							{
								$item['value'] = \Preferences::get(str_replace(',', '.', $name));
								if(isset($helpers['array']) && $helpers['array'])
								{
									$item['value'] = unserialize($item['value']);

									if(is_array($item['value']) && isset($item['value'][$i]))
									{
										$item['value'] = $item['value'][$i];
										$count++;
									}
									else
									{
										$item['value'] = '';
									}
								}
							}
							elseif (isset($object->$name))
							{
								$item['value'] = $object->$name;
							}
							elseif (isset($helpers['default_value']))
							{
								$item['value'] = $helpers['default_value'];
							}
							else
							{
								$item['value'] = '';
							}
						}

						?>
						<?php
							// if help is not set, put the label in help-inline
							if (isset($helpers['help'])) : ?><label><?php echo $helpers['label'] ?></label><?php endif; ?>
						<?php
						echo Form::$helper['type']($item);
						?>
						<span class="help-inline">
							<?php
							echo (isset($helpers['help']) ? $helpers['help'] : '');
							echo (!isset($helpers['help']) ? $helpers['label'] : '');
							?>
						</span>
						<br/>

						<?php
						break;

					default:
						break;

				endswitch;
				unset($item['value']);
			endfor;
		endif;
		?>

	<?php endforeach; ?>

</div>