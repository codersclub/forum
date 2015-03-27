<span class="b-pages small">
    <span class="b-pages__title"><?= Ibf::app()->lang["topic_sp_pages"] ?></span>
    <?php foreach ($map as $page_no => $post_no): ?>
        <?php if ($post_no === "hellip"): ?>
            <span class="b-pages__hellip">...</span>
        <?php else: ?>
            <a class="b-pages__button" href="<?= $topic_url_prefix ?>&amp;st=<?= $post_no ?>"><?= $page_no ?></a>
        <?php endif; ?>
    <?php endforeach; ?>
    <a class="b-pages__button_last" href="<?= $topic_url_prefix ?>&amp;view=getlastpost">#</a>
    <?php if ($show_all_btn): ?>
        <a class="b-pages__button_all" href="<?= $topic_url_prefix ?>&amp;view=showall"><?= Ibf::app()->lang["all_posts"] ?></a>
    <?php endif; ?>
</span>
