<?php

namespace Foolz\FoolFrame\Theme\Admin\Partial;

class Confirm extends \Foolz\FoolFrame\View\View
{
    public function toString()
    {
        $form = $this->getForm();
        ?>
<div class="alert alert-block alert-<?= $this->getParamManager()->getParam('alert_level') ?> fade in">
    <p><?= $this->getParamManager()->getParam('message') ?></p>
    <p><?php echo $form->open(array('onsubmit' => 'fuel_set_csrf_token(this);'));
        echo $form->hidden('csrf_token', $this->getSecurity()->getCsrfToken());
        echo $form->submit(array(
            'name' => 'confirm',
            'value' => _i('Confirm'),
            'class' => 'btn btn-danger',
            'style' => 'margin-right:6px;'));
        echo '<input type="button" onClick="history.back()" class="btn" value="'. _i('Go back') . '" />';
        echo $form->close();
    ?></p>
</div>
<?php
    }
}
