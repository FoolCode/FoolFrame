<?php if (count($sidebar) > 0) : ?>
<div class="well" style="padding: 3px;">
	<ul class="nav nav-list">
	<?php foreach ($sidebar as $key => $item) : ?>
		<li class="nav-header">	<?= $item['name'] ?></li>
		<?php foreach ($item['content'] as $k => $i) : ?>
			<?php if ($i['active']) : ?>
				<li class="active">
					<a href="<?= $i['href'] ?>">
						<i class="<?= $i['icon'] ?> icon-white"></i> <?= $i['name'] ?>
						<?php if (isset($i['notification'])) : ?>
							<div style="float: right;">
								<?= $i['notification'] ?>
							</div>
						<?php endif; ?>
					</a>
				</li>
			<?php else : ?>
				<li>
					<a href="<?= $i['href'] ?>">
						<i class="<?= $i['icon'] ?>"></i> <?= $i['name'] ?>
						<?php if (isset($i['notification'])) : ?>
							<div style="float: right;">
								<?= $i['notification'] ?>
							</div>
						<?php endif; ?>
					</a>
				</li>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endforeach; ?>
	</ul>
</div>
<?php endif; ?>