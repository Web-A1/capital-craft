<?php

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>

<nav class="breadcrumbs">
    <ul class="breadcrumbs__list">
        <?php if ($params->get('showHere', 1)) : ?>
            <li class="breadcrumbs__item"><?php echo Text::_('MOD_BREADCRUMBS_HERE'); ?></li>
        <?php endif; ?>

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
    </ul>
</nav>