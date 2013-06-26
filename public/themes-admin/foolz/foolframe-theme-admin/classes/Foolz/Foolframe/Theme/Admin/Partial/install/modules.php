<p class="description">
	<?= __('Congratulations, you have completed the installation and setup of FoolFrame. Please choose the module(s) you wish to install below:') ?>
</p>

<?= \Form::open() ?>

	<?php foreach ($modules as $module => $info) : ?>
		<label class="checkbox">
			<?php if ($info['disabled']) : ?>
				<input type="checkbox" name="<?= $module ?>" disabled="disabled" />
			<?php else : ?>
				<input type="checkbox" name="<?= $module ?>" />
			<?php endif; ?>
			<?= $info['title'] ?>
		</label>
		<p style="font-size: 0.8em; padding-left: 20px"><?= $info['description'] ?></p>
	<?php endforeach; ?>

	<hr />

	<?= \Form::submit(array('name' => 'submit', 'value' => __('Next'), 'class' => 'btn btn-success pull-right')) ?>
<?= \Form::close() ?>