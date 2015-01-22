<div class="container">
    <nav>
        <ul>
            <?php foreach ($articles as $article) : ?>
            <li>
                <a href="<?= $this->uri->create('_/articles/'.$article["slug"]) ?>"><?= $article["title"] ?></a>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</div>
