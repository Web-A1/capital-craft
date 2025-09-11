<?php

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>

<?php
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

$app = Factory::getApplication();
$input = $app->input;
$option = $input->getCmd('option');
$view   = $input->getCmd('view');

$custom = null;

// Custom breadcrumbs for tag pages: Главная / Блог / #Tag
if ($option === 'com_tags' && $view === 'tag') {
    $menu = $app->getMenu();
    $home = $menu->getDefault();
    $blogItem = $menu->getItems('alias', 'blog', true);

    // Resolve current tag title from id param (may be like "12:alias")
    $idParam = $input->get('id', '', 'STRING');
    $tagId = (int) explode(':', (string) $idParam)[0];
    $tagTitle = '';
    if ($tagId > 0) {
        $db = Factory::getDbo();
        $q = $db->getQuery(true)
            ->select($db->quoteName('title'))
            ->from($db->quoteName('#__tags'))
            ->where($db->quoteName('id') . ' = ' . (int) $tagId);
        $db->setQuery($q);
        $tagTitle = (string) $db->loadResult();
    }

    $custom = [];
    if ($home) {
        $custom[] = (object) ['name' => Text::_('MOD_BREADCRUMBS_HOME'), 'link' => Route::_('index.php?Itemid=' . $home->id)];
    }
    if ($blogItem) {
        $custom[] = (object) ['name' => 'Блог', 'link' => Route::_('index.php?Itemid=' . $blogItem->id)];
    }
    if ($tagTitle !== '') {
        $custom[] = (object) ['name' => '#' . $tagTitle, 'link' => ''];
    }
}
?>

<nav class="breadcrumbs">
    <ul class="breadcrumbs__list">
        <?php if ($params->get('showHere', 1)) : ?>
            <li class="breadcrumbs__item"><?php echo Text::_('MOD_BREADCRUMBS_HERE'); ?></li>
        <?php endif; ?>

        <?php if ($custom !== null) : ?>
            <?php foreach ($custom as $i => $item) : ?>
                <li class="breadcrumbs__item">
                    <?php if (!empty($item->link) && $i < count($custom) - 1) : ?>
                        <a href="<?php echo $item->link; ?>"><?php echo $item->name; ?></a>
                    <?php else : ?>
                        <?php echo $item->name; ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        <?php else : ?>
            <?php $showLast = $params->get('showLast', 1); ?>
            <?php $count = count($list); ?>
            <?php foreach ($list as $i => $item) : ?>
                <?php if ($i < $count - 1 || $showLast) : ?>
                    <li class="breadcrumbs__item">
                        <?php if (!empty($item->link) && $i < $count - 1) : ?>
                            <a href="<?php echo $item->link; ?>"><?php echo $item->name; ?></a>
                        <?php else : ?>
                            <?php echo $item->name; ?>
                        <?php endif; ?>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
    </nav>
