<?php
if (!defined('DOCROOT')) {
    exit('No direct script access allowed');
}
?>
<div class="container">
    <nav>
        <ul>
            <?php foreach ($articles as $article) : ?>
            <li>
                <a href="<?= \Uri::create('_/articles/'.$article["slug"]) ?>"><?= $article["title"] ?></a>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</div>
