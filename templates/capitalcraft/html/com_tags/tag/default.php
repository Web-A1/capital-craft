<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var \Joomla\Component\Tags\Site\View\Tag\HtmlView $this */
$htag = $this->params->get('show_page_heading') ? 'h2' : 'h1';
?>

<section class="frame section-with-divider blog-tags" aria-labelledby="tag-title">
  <div class="container">

    <header class="blog__header">
      <div class="blog__subtitle" id="tag-subtitle">Статьи по тегу</div>
      <<?php echo $htag; ?> class="blog__title" id="tag-title">
        #<?php echo HTMLHelper::_('content.prepare', $this->tags_title, '', 'com_tags.tag'); ?>
      </<?php echo $htag; ?>>
    </header>

    <?php echo $this->loadTemplate('items'); ?>

    <?php if (($this->params->def('show_pagination', 1) == 1 || ($this->params->get('show_pagination') == 2)) && ($this->pagination->pagesTotal > 1)) : ?>
      <nav class="blog-pagination" aria-label="Пагинация по тегу">
        <?php if ($this->params->def('show_pagination_results', 1)) : ?>
          <p class="blog-pagination__counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
        <?php endif; ?>
        <div class="blog-pagination__links"><?php echo $this->pagination->getPagesLinks(); ?></div>
      </nav>
    <?php endif; ?>

    <?php
      // Other tags block: show all published tags used by articles, except current
      $db = Joomla\CMS\Factory::getDbo();
      $idParam = Joomla\CMS\Factory::getApplication()->input->get('id', '', 'STRING');
      $currentTagId = (int) explode(':', (string) $idParam)[0];
      $q = $db->getQuery(true)
        ->select('t.id, t.title')
        ->from($db->quoteName('#__tags','t'))
        ->join('INNER', $db->quoteName('#__contentitem_tag_map','m') . ' ON m.tag_id = t.id AND m.type_alias = ' . $db->quote('com_content.article'))
        ->where('t.published = 1')
        ->where('t.id != ' . (int) $currentTagId)
        ->group('t.id')
        ->order('t.title ASC');
      $db->setQuery($q);
      $allTags = (array) $db->loadObjectList();
    ?>

    <?php if (!empty($allTags)) : ?>
      <section class="blog-tags__others">
        <div class="blog__subtitle">Другие теги</div>
        <ul class="blog-tags__cloud">
          <?php foreach ($allTags as $tg) : ?>
            <li class="blog-tags__tag">
              <a class="blog-tags__link" href="<?php echo JRoute::_('index.php?option=com_tags&view=tag&id=' . (int)$tg->id); ?>">#<?php echo htmlspecialchars($tg->title, ENT_QUOTES, 'UTF-8'); ?></a>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endif; ?>

  </div>
</section>

<script>
  document.addEventListener('click', function(e) {
    const tagLink = e.target.closest('.blog-card__tag-link');
    if (tagLink) return;
    const card = e.target.closest('.blog-card');
    if (!card) return;
    const href = card.dataset.href;
    if (href) window.location.href = href;
  });
  document.addEventListener('keydown', function(e) {
    if ((e.key === 'Enter' || e.key === ' ') && e.target.classList && e.target.classList.contains('blog-card')) {
      const href = e.target.dataset.href;
      if (href) { e.preventDefault(); window.location.href = href; }
    }
  });
</script>
