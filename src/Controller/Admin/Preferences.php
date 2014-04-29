<?php

namespace Foolz\Foolframe\Controller\Admin;

use Foolz\Foolframe\Controller\Admin;
use Foolz\Foolframe\Model\Validation\ActiveConstraint\Trim;
use GeoIp2\Database\Reader;
use MaxMind\Db\Reader\InvalidDatabaseException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;


class Preferences extends Admin
{
    public function before()
    {
        parent::before();

        // set controller title
        $this->param_manager->setParam('controller_title', _i('Preferences'));
    }

    public function security()
    {
        return $this->getAuth()->hasAccess('maccess.admin');
    }

    function action_general()
    {
        $this->param_manager->setParam('method_title', _i('General'));

        $form = [];

        $form['open'] = array(
            'type' => 'open'
        );

        // build the array for the form
        $form['foolframe.gen.website_title'] = array(
            'type' => 'input',
            'label' => 'Title',
            'class' => 'span3',
            'preferences' => true,
            'validation' => [new Trim(), new Assert\Length(['max' => 32])],
            'help' => _i('Set the title of your site.')
        );

        // build the array for the form
        $form['foolframe.gen.index_title'] = array(
            'type' => 'input',
            'label' => 'Index title',
            'class' => 'span3',
            'preferences' => true,
            'validation' => [new Trim(), new Assert\Length(['max' => 32])],
            'help' => _i('Set the title displayed on the index page.')
        );

        $form['foolframe.maxmind.geoip2_db_path'] = [
            'type' => 'input',
            'label' => _i('GeoIP database path'),
            'help' => _i('Overrides the default path to GeoIP2 Country database (mmdb format)'),
            'preferences' => true,
            'validation' => [new Trim()],
            'validation_func' => function($input, $form) {
                    $path = trim($input['foolframe.maxmind.geoip2_db_path']);
                    if (!$path) {
                        return ['success' => true];
                    }

                    if(!is_readable($path)) {
                        return [
                            'error_code' => 'NO_SUCH_FILE',
                            'error' => _i('Specified file does not exist or is not readable.')
                        ];
                    }

                    try {
                        new Reader($path);
                    } catch(InvalidDatabaseException $e) {
                        return [
                            'warning_code' => 'INVALID_DATABASE',
                            'warning' => _i('The specified path does not contain a valid GeoIP2 database.')
                        ];
                    }

                    return ['success' => true];
                }
        ];

        $form['foolframe.imagick.convert_path'] = [
            'type' => 'input',
            'label' => _i('Imagemagick Convert'),
            'help' => _i('Overrides the default path to the Imagemagick convert executable'),
            'preferences' => true,
            'validation' => [new Trim()],
            'validation_func' => function($input, $form) {
                    $path = trim($input['foolframe.imagick.convert_path']);
                    if (!$path) {
                        return ['success' => true];
                    }

                    if(!file_exists($path)) {
                        return [
                            'error_code' => 'NO_SUCH_FILE',
                            'error' => _i('The "convert" executable could not be found.')
                        ];
                    }

                    return ['success' => true];
                }
        ];

        $form['foolframe.lang.default'] = array(
            'type' => 'select',
            'label' => _i('Default language'),
            'help' => _i('Set the language users will see as they reach your site.'),
            'options' => $this->config->get('foolz/foolframe', 'package', 'preferences.lang.available'),
            'preferences' => true,
        );

        $form['separator-2'] = array(
            'type' => 'separator'
        );

        foreach ($this->config->get('foolz/foolframe', 'config', 'modules.installed') as $module) {
            if ($module === 'foolz/foolframe') {
                continue;
            }

            $theme_loader = new \Foolz\Theme\Loader();
            $theme_loader->addDir(VENDPATH.$module.'/'.$this->config->get($module, 'package', 'directories.themes'));
            $themes = $theme_loader->getAll();

            $module_name = $this->config->get($module, 'package', 'main.name');

            $theme_checkboxes = [];

            foreach($themes as $name => $theme) {
                $theme_checkboxes[] = array(
                    'type' => 'checkbox_array_value',
                    'label' => $theme->getConfig('name'),
                    'help' => sprintf(_i('Enable %s theme'), $theme->getConfig('extra.name')),
                    'array_key' => $theme->getConfig('name'),
                    'preferences' => true
                );
            }

            $form[strtolower($module_name).'.theme.active_themes'] = array(
                'type' => 'checkbox_array',
                'label' => _i('Active themes'),
                'help' => _i('Select which themes will be available to the users for %s. (Mods and Admins have full access to all)',
                    '<strong>'.$module_name.'</strong>'),
                'checkboxes' => $theme_checkboxes
            );

            $themes_default = [];

            foreach($themes as $name => $theme) {
                if ($theme->getConfig('extra.styles', false)) {
                    foreach ($theme->getConfig('extra.styles') as $style => $style_name) {
                        $themes_default[$name.'/'.$style] = $theme->getConfig('extra.name').' - '.$style_name;
                    }
                } else {
                    $themes_default[$name] = $theme->getConfig('extra.name');
                }

            }

            $form[strtolower($module_name).'.theme.default'] = array(
                'type' => 'select',
                'label' => _i('Default theme for %s', '<strong>'.$module_name.'</strong>'),
                'help' => _i('Set the default theme users will see as they reach %s.', '<strong>'.$module_name.'</strong>'),
                'options' => $themes_default,
                'preferences' => true,
            );
        }

        $form['foolframe.theme.google_analytics'] = array(
            'type' => 'input',
            'label' => _i('Google Analytics code'),
            'placeholder' => 'UX-XXXXXXX-X',
            'preferences' => true,
            'help' => _i("Insert your Google Analytics code."),
            'class' => 'span2'
        );

        $form['separator-3'] = array(
            'type' => 'separator'
        );

        $form['foolframe.theme.header_text'] = array(
            'type' => 'textarea',
            'label' => _i('Header Text ("alerts/notices")'),
            'preferences' => true,
            'help' => _i("Inserts text above in the header site wide, below the navigation links. <br> Most <a href='http://getbootstrap.com/2.3.2/base-css.html' target='blank'>Bootstrap CSS</a> formatting can be used here."),
            'class' => 'span5'
        );

        $form['foolframe.theme.header_code'] = array(
            'type' => 'textarea',
            'label' => _i('Header Code'),
            'preferences' => true,
            'help' => _i("This will insert the HTML code inside the &lt;HEAD&gt;."),
            'class' => 'span5'
        );

        $form['foolframe.theme.footer_text'] = array(
            'type' => 'textarea',
            'label' => _i('Footer Text'),
            'preferences' => true,
            'help' => _i("Inserts text in the footer site wide, such as credits and similar.<br> Most <a href='http://getbootstrap.com/2.3.2/base-css.html' target='blank'>Bootstrap CSS</a> formatting can be used here."),
            'class' => 'span5'
        );

        $form['foolframe.theme.footer_code'] = array(
            'type' => 'textarea',
            'label' => _i('Footer Code'),
            'preferences' => true,
            'help' => _i("This will insert the HTML code above after the &lt;BODY&gt;."),
            'class' => 'span5'
        );

        $form['separator'] = array(
            'type' => 'separator'
        );

        $form['submit'] = array(
            'type' => 'submit',
            'value' => _i('Submit'),
            'class' => 'btn btn-primary'
        );

        $form['close'] = array(
            'type' => 'close'
        );

        $data['form'] = $form;

        $this->preferences->submit_auto($this->getRequest(), $form, $this->getPost());

        // create a form
        $this->builder->createPartial('body', 'form_creator')
            ->getParamManager()->setParams($data);
        return new Response($this->builder->build());
    }

    function action_advertising()
    {
        $this->param_manager->setParam('method_title', _i('Advertising'));

        $form = [];

        $form['open'] = array(
            'type' => 'open'
        );

        $form['foolframe.ads_top_banner'] = array(
            'type' => 'textarea',
            'label' => _i('Top banner'),
            'help' => _i('Insert the HTML code provided by your advertiser.'),
            'preferences' => true,
            'validation' => [new Trim()],
            'class' => 'span5'
        );

        $form['foolframe.ads_top_banner_active'] = array(
            'type' => 'checkbox',
            'preferences' => true,
            'help' => _i('Enable top banner')
        );

        $form['foolframe.ads_bottom_banner'] = array(
            'type' => 'textarea',
            'label' => _i('Bottom banner'),
            'help' => _i('Insert the HTML code provided by your advertiser.'),
            'preferences' => true,
            'validation' => [new Trim()],
            'class' => 'span5'
        );

        $form['foolframe.ads_bottom_banner_active'] = array(
            'type' => 'checkbox',
            'preferences' => true,
            'help' => _i('Enable bottom banner')
        );

        $form['separator'] = array(
            'type' => 'separator'
        );

        $form['submit'] = array(
            'type' => 'submit',
            'value' => _i('Submit'),
            'class' => 'btn btn-primary'
        );

        $form['close'] = array(
            'type' => 'close'
        );

        $data['form'] = $form;

        $this->preferences->submit_auto($this->getRequest(), $form, $this->getPost());

        // create a form
        $this->builder->createPartial('body', 'form_creator')
            ->getParamManager()->setParams($data);
        return new Response($this->builder->build());
    }

    function action_registration()
    {
        $this->param_manager->setParam('method_title', _i('Registration'));

        $form = [];

        $form['open'] = array(
            'type' => 'open'
        );

        $form['foolframe.auth.disable_registration'] = array(
            'type' => 'checkbox',
            'preferences' => true,
            'help' => _i('Disable New User Registrations')
        );
        $form['foolframe.auth.disable_registration_email'] = array(
            'type' => 'checkbox',
            'preferences' => true,
            'help' => _i('Disable Email Activation')
        );

        $form['separator'] = array(
            'type' => 'separator'
        );

        $form['paragraph'] = array(
            'type' => 'paragraph',
            'help' => _i('In order to use reCAPTCHA&trade; you need to sign up for the service at <a href="http://www.google.com/recaptcha">reCAPTCHA&trade;</a>, which will provide you with a public and a private key.')
        );

        $form['foolframe.auth.recaptcha_public'] = array(
            'type' => 'input',
            'label' => _i('reCaptcha&trade; Public Key'),
            'preferences' => true,
            'help' => _i('Insert the public key provided by reCAPTCHA&trade;.'),
            'validation' => [new Trim()],
            'class' => 'span4'
        );

        $form['foolframe.auth.recaptcha_private'] = array(
            'type' => 'input',
            'label' => _i('reCaptcha&trade; Prvate Key'),
            'preferences' => true,
            'help' => _i('Insert the private key provided by reCAPTCHA&trade;.'),
            'validation' => [new Trim()],
            'class' => 'span4'
        );

        $form['separator-2'] = array(
            'type' => 'separator'
        );

        $form['submit'] = array(
            'type' => 'submit',
            'value' => _i('Submit'),
            'class' => 'btn btn-primary'
        );

        $form['close'] = array(
            'type' => 'close'
        );

        $data['form'] = $form;

        $this->preferences->submit_auto($this->getRequest(), $form, $this->getPost());

        // create a form
        $this->builder->createPartial('body', 'form_creator')
            ->getParamManager()->setParams($data);
        return new Response($this->builder->build());
    }
}
