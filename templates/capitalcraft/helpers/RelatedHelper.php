<?php

defined('_JEXEC') or die();

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

class CapitalcraftRelatedHelper
{
    protected static $cache = [];

    public static function getRelatedForArticle($article, array $tagIds): array
    {
        $articleId = (int) ($article->id ?? 0);
        $tagIds = array_values(array_unique(array_map('intval', $tagIds)));
        sort($tagIds);

        $user = Factory::getUser();
        $viewLevels = array_map('intval', $user->getAuthorisedViewLevels());
        sort($viewLevels);
        $viewLevelsKey = !empty($viewLevels) ? implode('-', $viewLevels) : '0';

        $app = Factory::getApplication();
        $appLanguage = $app->getLanguage()->getTag() ?: '*';
        $articleLanguage = (string) ($article->language ?? '');

        $languagePool = ['*'];

        if ($articleLanguage !== '' && !in_array($articleLanguage, $languagePool, true)) {
            $languagePool[] = $articleLanguage;
        }

        if ($appLanguage !== '' && !in_array($appLanguage, $languagePool, true)) {
            $languagePool[] = $appLanguage;
        }

        sort($languagePool);
        $languageKey = implode('-', $languagePool);

        $cacheKey = $articleId . ':' . implode('-', $tagIds) . ':' . $viewLevelsKey . ':' . $languageKey;

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $cacheController = null;
        $cacheId = md5(__METHOD__ . ':' . $cacheKey);

        try {
            $cacheController = Factory::getContainer()
                ->get(CacheControllerFactoryInterface::class)
                ->createCacheController('output', ['defaultgroup' => 'capitalcraft.related']);
        } catch (\Throwable $e) {
            $cacheController = null;
        }

        if ($cacheController && $cacheController->contains($cacheId)) {
            $cached = $cacheController->get($cacheId);
            if (\is_array($cached)) {
                self::$cache[$cacheKey] = $cached;

                return $cached;
            }
        }

        if (!$articleId || empty($tagIds)) {
            $empty = [
                'articles' => [],
                'faq' => [],
                'heading_tags' => [],
            ];
            self::$cache[$cacheKey] = $empty;

            if ($cacheController) {
                try {
                    $cacheController->store($empty, $cacheId);
                } catch (\Throwable $e) {
                    // Игнорируем проблемы с кешем, чтобы не ломать вывод
                }
            }

            return $empty;
        }

        $db = Factory::getDbo();
        $tagList = implode(',', $tagIds);
        $languageList = implode(',', array_map(function ($lang) use ($db) {
            return $db->quote($lang);
        }, $languagePool));
        $viewLevelsList = !empty($viewLevels) ? implode(',', $viewLevels) : '0';
        $contentAccessCondition = 'c.access IN (' . $viewLevelsList . ')';
        $categoryAccessCondition = 'cat.access IN (' . $viewLevelsList . ')';
        $tagAccessCondition = '(t.id IS NULL OR t.access IN (' . $viewLevelsList . '))';
        $contentLanguageCondition = 'c.language IN (' . $languageList . ')';
        $categoryLanguageCondition = 'cat.language IN (' . $languageList . ')';
        $tagLanguageCondition = '(t.id IS NULL OR t.language IN (' . $languageList . '))';
        $tagPublishedCondition = '(t.id IS NULL OR t.published = 1)';
        $now = Factory::getDate()->toSql();
        $nullDateValue = $db->getNullDate();
        $nullDate = $db->quote($nullDateValue);
        $publishUpCondition = '(c.publish_up IS NULL OR c.publish_up = ' . $nullDate . ' OR c.publish_up <= ' .
            $db->quote($now) . ')';
        $publishDownCondition = '(c.publish_down IS NULL OR c.publish_down = ' . $nullDate . ' OR c.publish_down >= ' .
            $db->quote($now) . ')';

        $articleQuery = $db
            ->getQuery(true)
            ->select('1 AS sort_group')
            ->select($db->quote('article') . ' AS item_type')
            ->select('c.id, c.title, c.alias, c.catid, c.language, c.publish_up, c.introtext, c.fulltext')
            ->select('GROUP_CONCAT(DISTINCT m.tag_id) AS tag_ids')
            ->select('GROUP_CONCAT(DISTINCT t.alias) AS tag_aliases')
            ->from($db->quoteName('#__content', 'c'))
            ->join(
                'INNER',
                $db->quoteName('#__contentitem_tag_map', 'm') .
                    ' ON m.content_item_id = c.id AND m.type_alias = ' . $db->quote('com_content.article')
            )
            ->join('LEFT', $db->quoteName('#__tags', 't') . ' ON t.id = m.tag_id')
            ->join('INNER', $db->quoteName('#__categories', 'cat') . ' ON cat.id = c.catid')
            ->where('c.state = 1')
            ->where('c.id != ' . $articleId)
            ->where('m.tag_id IN (' . $tagList . ')')
            ->where($db->quoteName('cat.alias') . ' != ' . $db->quote('faq'))
            ->where('cat.published = 1')
            ->where($contentAccessCondition)
            ->where($contentLanguageCondition)
            ->where($categoryAccessCondition)
            ->where($categoryLanguageCondition)
            ->where($tagAccessCondition)
            ->where($tagLanguageCondition)
            ->where($tagPublishedCondition)
            ->where($publishUpCondition)
            ->where($publishDownCondition)
            ->group(
                'c.id, c.title, c.alias, c.catid, c.language, c.publish_up, c.introtext, c.fulltext'
            )
            ->order('c.publish_up DESC');
        $articleQuery->setLimit(6);

        $faqQuery = $db
            ->getQuery(true)
            ->select('2 AS sort_group')
            ->select($db->quote('faq') . ' AS item_type')
            ->select('c.id, c.title, c.alias, c.catid, c.language, c.publish_up, c.introtext, c.fulltext')
            ->select('GROUP_CONCAT(DISTINCT m.tag_id) AS tag_ids')
            ->select('GROUP_CONCAT(DISTINCT t.alias) AS tag_aliases')
            ->from($db->quoteName('#__content', 'c'))
            ->join(
                'INNER',
                $db->quoteName('#__contentitem_tag_map', 'm') .
                    ' ON m.content_item_id = c.id AND m.type_alias = ' . $db->quote('com_content.article')
            )
            ->join('LEFT', $db->quoteName('#__tags', 't') . ' ON t.id = m.tag_id')
            ->join('INNER', $db->quoteName('#__categories', 'cat') . ' ON cat.id = c.catid')
            ->where('c.state = 1')
            ->where($db->quoteName('cat.alias') . ' = ' . $db->quote('faq'))
            ->where('m.tag_id IN (' . $tagList . ')')
            ->where('cat.published = 1')
            ->where($contentAccessCondition)
            ->where($contentLanguageCondition)
            ->where($categoryAccessCondition)
            ->where($categoryLanguageCondition)
            ->where($tagAccessCondition)
            ->where($tagLanguageCondition)
            ->where($tagPublishedCondition)
            ->where($publishUpCondition)
            ->where($publishDownCondition)
            ->group(
                'c.id, c.title, c.alias, c.catid, c.language, c.publish_up, c.introtext, c.fulltext'
            )
            ->order('c.publish_up DESC');
        $faqQuery->setLimit(6);

        $articleSql = $articleQuery->__toString();
        $faqSql = $faqQuery->__toString();
        $combinedSql = '(' . $articleSql . ') UNION ALL (' . $faqSql . ') ORDER BY sort_group ASC, publish_up DESC';

        $db->setQuery($combinedSql);
        $rows = (array) $db->loadObjectList();

        $articleTagAliasMap = [];
        $articleTagTitleMap = [];
        if (!empty($article->tags->itemTags)) {
            foreach ($article->tags->itemTags as $tag) {
                if (!empty($tag->tag_id)) {
                    $articleTagAliasMap[(int) $tag->tag_id] = (string) ($tag->alias ?? '');
                    $articleTagTitleMap[(int) $tag->tag_id] = (string) ($tag->title ?? '');
                }
            }
        }

        $articles = [];
        $faqItems = [];
        $matchedTagIds = [];

        foreach ($rows as $row) {
            $itemTagIds = array_values(
                array_filter(
                    array_map('intval', explode(',', (string) ($row->tag_ids ?? '')))
                )
            );
            if (!empty($itemTagIds)) {
                $matchedTagIds = array_merge($matchedTagIds, $itemTagIds);
            }

            if ((int) $row->sort_group === 1) {
                $raw = $row->introtext !== '' ? $row->introtext : $row->fulltext;
                $plain = trim(strip_tags((string) $raw));
                $excerpt = $plain !== ''
                    ? HTMLHelper::_('string.truncate', $plain, 240, true, false)
                    : '';

                $articles[] = [
                    'id' => (int) $row->id,
                    'title' => (string) $row->title,
                    'link' => Route::_(
                        RouteHelper::getArticleRoute(
                            $row->id . ':' . $row->alias,
                            (int) $row->catid,
                            $row->language ?? 0
                        )
                    ),
                    'publish_up' => $row->publish_up,
                    'excerpt' => $excerpt,
                    'tag_ids' => $itemTagIds,
                ];
            } else {
                $raw = $row->fulltext !== '' ? $row->fulltext : ($row->introtext ?? '');
                [, $answerText] = self::prepareFaqAnswer((string) $raw);
                $excerpt = $answerText !== ''
                    ? HTMLHelper::_('string.truncate', $answerText, 200, true, false)
                    : '';

                $fallbackAliases = array_values(
                    array_filter(explode(',', (string) ($row->tag_aliases ?? '')))
                );
                $linkAlias = self::resolveFaqLinkAlias($itemTagIds, $articleTagAliasMap, $fallbackAliases);

                $faqItems[] = [
                    'id' => (int) $row->id,
                    'title' => (string) $row->title,
                    'excerpt' => $excerpt,
                    'publish_up' => $row->publish_up,
                    'link' => self::buildFaqLink((int) $row->id, $linkAlias),
                    'tag_ids' => $itemTagIds,
                ];
            }
        }

        $matchedTagIds = array_values(array_unique($matchedTagIds));

        $headingTags = [];
        if (!empty($matchedTagIds) && !empty($articleTagTitleMap)) {
            foreach ($matchedTagIds as $matchedId) {
                if (isset($articleTagTitleMap[$matchedId])) {
                    $headingTags[] = [
                        'title' => $articleTagTitleMap[$matchedId],
                        'alias' => $articleTagAliasMap[$matchedId] ?? '',
                    ];
                }
            }
        }

        $result = [
            'articles' => $articles,
            'faq' => $faqItems,
            'heading_tags' => $headingTags,
        ];

        self::$cache[$cacheKey] = $result;

        if ($cacheController) {
            try {
                $cacheController->store($result, $cacheId);
            } catch (\Throwable $e) {
                // Игнорируем проблемы с кешем, чтобы не ломать вывод
            }
        }

        return $result;
    }

    protected static function resolveFaqLinkAlias(array $itemTagIds, array $articleTagAliasMap, array $fallbackAliases): string
    {
        foreach ($itemTagIds as $tagId) {
            if (isset($articleTagAliasMap[$tagId]) && $articleTagAliasMap[$tagId] !== '') {
                return (string) $articleTagAliasMap[$tagId];
            }
        }

        foreach ($fallbackAliases as $alias) {
            if ($alias !== '') {
                return (string) $alias;
            }
        }

        return isset($itemTagIds[0]) ? (string) $itemTagIds[0] : '';
    }

    protected static function buildFaqLink(int $id, string $tagAlias): string
    {
        self::ensureFaqHelper();

        $normalized = trim($tagAlias);

        return CapitalcraftFaqHelper::buildFaqLink($id, $normalized === '' ? null : $normalized);
    }

    protected static function prepareFaqAnswer(string $raw): array
    {
        self::ensureFaqHelper();

        return CapitalcraftFaqHelper::parseAnswer($raw);
    }

    protected static function ensureFaqHelper(): void
    {
        if (!class_exists('CapitalcraftFaqHelper')) {
            require_once JPATH_SITE . '/templates/capitalcraft/helpers/FaqHelper.php';
        }
    }
}
