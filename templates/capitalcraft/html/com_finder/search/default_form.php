<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var \Joomla\Component\Finder\Site\View\Search\HtmlView $this */

// Autosuggest assets (same as core)
if ($this->params->get('show_autosuggest', 1)) {
    $this->getDocument()->getWebAssetManager()->usePreset('awesomplete');
    $this->getDocument()->addScriptOptions('finder-search', [
        'url' => Route::_('index.php?option=com_finder&task=suggestions.suggest&format=json&tmpl=component', false),
    ]);

    Text::script('COM_FINDER_SEARCH_FORM_LIST_LABEL');
    Text::script('JLIB_JS_AJAX_ERROR_OTHER');
    Text::script('JLIB_JS_AJAX_ERROR_PARSE');
}
?>

<form action="<?php echo Route::_($this->query->toUri()); ?>" method="get" class="js-finder-searchform blog-form">
  <?php echo $this->getFields(); ?>

  <fieldset class="blog-search word mb-3">
    <legend class="visually-hidden"><?php echo Text::_('COM_FINDER_SEARCH_FORM_LEGEND'); ?></legend>
    <label for="q" class="blog-search__label"><?php echo Text::_('COM_FINDER_SEARCH_TERMS'); ?></label>
    <div class="input-group">
      <input type="text" name="q" id="q" class="js-finder-search-query form-control" value="<?php echo $this->escape($this->query->input); ?>" placeholder="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>">
      <button type="submit" class="btn btn-primary"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
    </div>
  </fieldset>

  <div class="blog-filters__inner">
    <?php echo HTMLHelper::_('filter.select', $this->query, $this->params); ?>

    <?php // Ссылка «Сбросить фильтры» — чистим основные параметры
      $reset = Uri::getInstance(Route::_($this->query->toUri()));
      foreach (['q','t','d1','d2','w1','w2','o','od'] as $var) {
          $reset->delVar($var);
      }
      $resetUrl = Route::_($reset->toString(['path','query']));
    ?>
    <a class="btn btn-sm btn-outline-secondary" href="<?php echo $resetUrl; ?>"><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></a>
  </div>
</form>
