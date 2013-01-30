<div class="navbar navbar-fixed-top">
	<div class="navbar-inner">
		<div class="container">
			<a class="brand" href="<?= \Uri::create('admin') ?>">
				<?= \Preferences::get('ff.gen.website_title') ?>
			</a>
			<ul class="nav pull-right">
				<li><a href="<?= \Uri::base('@default') ?>"><?= __('Boards') ?></a></li>
				<li class="divider-vertical"></li>
				<?php if (\Auth::has_access('maccess.user')) : ?>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">
							<?= \Auth::get_screen_name(); ?>
							<b class="caret"></b>
						</a>
						<ul class="dropdown-menu">
							<li>
								<a href="<?= \Uri::create('admin/account/change_email') ?>">
									<?= __('Profile') ?>
								</a>
							</li>
							<li>
								<a href="<?= \Uri::create('/admin/account/logout').'?token='.\Security::fetch_token() ?>">
									<?= __('Logout') ?>
								</a>
							</li>
							<li>
								<a href="<?= \Uri::create('/admin/account/logout_all').'?token='.\Security::fetch_token() ?>">
									<?= __('Logout on All Devices') ?>
								</a>
							</li>
						</ul>
					</li>
				<?php else : ?>
					<li>
						<a href="<?= \Uri::create('admin/account/login') ?>">
							<?= __('Login') ?>
						</a>
					</li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
</div>