<?php

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

class CapitalcraftFaqHelper
{
    protected static $categoryId = [];
    protected static $faqArticle = [];

    protected static $cache = [
        'page' => [],
        'featured' => [],
    ];

    protected static $htmlFilter = null;
    protected static $context = null;

    protected static function getContext(): array
    {
        if (self::$context !== null) {
            return self::$context;
        }

        $app = Factory::getApplication();
        $user = Factory::getUser();

        $viewLevels = array_map('intval', $user->getAuthorisedViewLevels());
        sort($viewLevels);

        $languageTag = $app->getLanguage()->getTag() ?: '*';

        self::$context = [
            'viewLevels' => $viewLevels,
            'language' => $languageTag,
            'now' => Factory::getDate()->toSql(),
        ];

        return self::$context;
    }

    protected static function buildCacheKey(string $scope, string $suffix = ''): string
    {
        $context = self::getContext();
        $levelsKey = !empty($context['viewLevels']) ? implode(',', $context['viewLevels']) : '0';

        return $scope . ':' . $context['language'] . ':' . $levelsKey . ':' . $suffix;
    }

    protected static function buildLanguageCondition($db, string $field, string $language): string
    {
        $allowed = [$db->quote('*')];

        if ($language !== '' && $language !== '*') {
            $allowed[] = $db->quote($language);
        }

        return $db->quoteName($field) . ' IN (' . implode(',', $allowed) . ')';
    }

    protected static function buildViewLevelsCondition($db, string $field, array $viewLevels): string
    {
        $levels = array_map('intval', $viewLevels);

        if (empty($levels)) {
            $levels = [0];
        }

        return $db->quoteName($field) . ' IN (' . implode(',', $levels) . ')';
    }

    public static function parseAnswer(string $raw): array
    {
        return self::prepareAnswer($raw);
    }

    public static function getFaqPageData(string $tagParam = ''): array
    {
        $context = self::getContext();
        $normalizedTag = strtolower(trim($tagParam));
        $cacheKey = self::buildCacheKey('page', $normalizedTag);

        if (isset(self::$cache['page'][$cacheKey])) {
            return self::$cache['page'][$cacheKey];
        }

        $faqCatId = self::getCategoryId();

        if (!$faqCatId) {
            $empty = [
                'items' => [],
                'allTags' => [],
                'selectedAlias' => '',
            ];
            self::$cache['page'][$cacheKey] = $empty;

            return $empty;
        }

        $db = Factory::getDbo();

        $selectedAlias = '';
        $tagIds = [];
        $rawTag = trim($tagParam);

        $viewLevelsCondition = self::buildViewLevelsCondition($db, 't.access', $context['viewLevels']);
        $tagLanguageCondition = self::buildLanguageCondition($db, 't.language', $context['language']);

        if ($rawTag !== '') {
            if (ctype_digit($rawTag)) {
                $tagIds = [(int) $rawTag];
                $query = $db
                    ->getQuery(true)
                    ->select($db->quoteName('t.alias'))
                    ->from($db->quoteName('#__tags', 't'))
                    ->where($db->quoteName('id') . ' = ' . (int) $rawTag)
                    ->where($db->quoteName('published') . ' = 1')
                    ->where($viewLevelsCondition)
                    ->where($tagLanguageCondition);
                $db->setQuery($query);
                $selectedAlias = strtolower((string) $db->loadResult());
            } else {
                $query = $db
                    ->getQuery(true)
                    ->select($db->quoteName('t.id'))
                    ->from($db->quoteName('#__tags', 't'))
                    ->where($db->quoteName('t.alias') . ' = ' . $db->quote($rawTag))
                    ->where($db->quoteName('t.published') . ' = 1')
                    ->where($viewLevelsCondition)
                    ->where($tagLanguageCondition);
                $db->setQuery($query);
                $foundId = (int) $db->loadResult();

                if ($foundId) {
                    $tagIds = [$foundId];
                    $selectedAlias = strtolower($rawTag);
                }
            }
        }

        $now = $context['now'];
        $nullDateValue = $db->getNullDate();
        $nullDate = $db->quote($nullDateValue);
        $publishUpCondition =
            '(c.publish_up IS NULL OR c.publish_up = ' . $nullDate . ' OR c.publish_up <= ' . $db->quote($now) . ')';
        $publishDownCondition =
            '(c.publish_down IS NULL OR c.publish_down = ' .
            $nullDate .
            ' OR c.publish_down >= ' .
            $db->quote($now) .
            ')';

        $categoryLanguageCondition = self::buildLanguageCondition($db, 'cat.language', $context['language']);
        $categoryAccessCondition = self::buildViewLevelsCondition($db, 'cat.access', $context['viewLevels']);
        $contentLanguageCondition = self::buildLanguageCondition($db, 'c.language', $context['language']);
        $contentAccessCondition = self::buildViewLevelsCondition($db, 'c.access', $context['viewLevels']);

        $query = $db
            ->getQuery(true)
            ->select('c.id, c.title, c.introtext, c.fulltext, c.publish_up')
            ->from($db->quoteName('#__content', 'c'))
            ->join('INNER', $db->quoteName('#__categories', 'cat') . ' ON cat.id = c.catid')
            ->where('c.state = 1')
            ->where('c.catid = ' . (int) $faqCatId)
            ->where($contentAccessCondition)
            ->where($contentLanguageCondition)
            ->where('cat.published = 1')
            ->where($categoryAccessCondition)
            ->where($categoryLanguageCondition)
            ->where($publishUpCondition)
            ->where($publishDownCondition)
            ->order('c.publish_up DESC');

        if (!empty($tagIds)) {
            $query
                ->join(
                    'INNER',
                    $db->quoteName('#__contentitem_tag_map', 'mt') .
                        ' ON mt.content_item_id = c.id AND mt.type_alias = ' .
                        $db->quote('com_content.article'),
                )
                ->where('mt.tag_id IN (' . implode(',', array_map('intval', $tagIds)) . ')')
                ->group($db->quoteName('c.id'));
        }

        $db->setQuery($query);
        $rows = (array) $db->loadObjectList();

        $faqItems = [];
        $faqIds = [];

        foreach ($rows as $row) {
            $answerSource = $row->fulltext !== '' ? $row->fulltext : $row->introtext ?? '';
            [$answerHtml, $answerText] = self::prepareAnswer($answerSource);

            $faqItems[] = [
                'id' => (int) $row->id,
                'q' => (string) $row->title,
                'answer_html' => $answerHtml,
                'answer_text' => $answerText,
                'tags' => [],
                'publish_up' =>
                    $row->publish_up && (string) $row->publish_up !== $nullDateValue ? (string) $row->publish_up : '',
            ];
            $faqIds[] = (int) $row->id;
        }

        if (!empty($faqIds)) {
            $query = $db
                ->getQuery(true)
                ->select(
                    $db->quoteName('m.content_item_id', 'cid') .
                        ', ' .
                        $db->quoteName('t.id') .
                        ', ' .
                        $db->quoteName('t.title') .
                        ', ' .
                        $db->quoteName('t.alias'),
                )
                ->from($db->quoteName('#__contentitem_tag_map', 'm'))
                ->join('INNER', $db->quoteName('#__tags', 't') . ' ON t.id = m.tag_id')
                ->where('m.type_alias = ' . $db->quote('com_content.article'))
                ->where('t.published = 1')
                ->where($viewLevelsCondition)
                ->where($tagLanguageCondition)
                ->where('m.content_item_id IN (' . implode(',', array_map('intval', $faqIds)) . ')')
                ->order($db->quoteName('m.tag_date') . ' ASC');

            $db->setQuery($query);
            $tagRows = (array) $db->loadObjectList();

            $tagsById = [];

            foreach ($tagRows as $tagRow) {
                $contentId = (int) $tagRow->cid;

                if (!isset($tagsById[$contentId])) {
                    $tagsById[$contentId] = [];
                }

                $tagsById[$contentId][] = [
                    'id' => (int) $tagRow->id,
                    'title' => (string) $tagRow->title,
                    'alias' => (string) $tagRow->alias,
                ];
            }

            foreach ($faqItems as $index => $item) {
                $id = (int) $item['id'];

                if (isset($tagsById[$id])) {
                    $faqItems[$index]['tags'] = array_values($tagsById[$id]);
                    $faqItems[$index]['primary_tag'] = $faqItems[$index]['tags'][0] ?? null;
                }
            }
        }

        $query = $db
            ->getQuery(true)
            ->select('DISTINCT t.id, t.title, t.alias')
            ->from($db->quoteName('#__tags', 't'))
            ->join(
                'INNER',
                $db->quoteName('#__contentitem_tag_map', 'm') .
                    ' ON m.tag_id = t.id AND m.type_alias = ' .
                    $db->quote('com_content.article'),
            )
            ->join(
                'INNER',
                $db->quoteName('#__content', 'c') .
                    ' ON c.id = m.content_item_id AND c.state = 1 AND c.catid = ' .
                    (int) $faqCatId,
            )
            ->join('INNER', $db->quoteName('#__categories', 'cat') . ' ON cat.id = c.catid')
            ->where('t.published = 1')
            ->where($viewLevelsCondition)
            ->where($tagLanguageCondition)
            ->where($contentAccessCondition)
            ->where($contentLanguageCondition)
            ->where($categoryAccessCondition)
            ->where($categoryLanguageCondition)
            ->where('cat.published = 1')
            ->where($publishUpCondition)
            ->where($publishDownCondition)
            ->order('t.title ASC');

        $db->setQuery($query);
        $faqAllTags = (array) $db->loadObjectList();

        if (!empty($faqItems) && !empty($faqAllTags)) {
            $orderedTags = $faqAllTags;

            if ($selectedAlias !== '') {
                usort($orderedTags, function ($a, $b) use ($selectedAlias) {
                    $aliasA = strtolower($a->alias);
                    $aliasB = strtolower($b->alias);

                    if ($aliasA === $selectedAlias && $aliasB !== $selectedAlias) {
                        return -1;
                    }

                    if ($aliasB === $selectedAlias && $aliasA !== $selectedAlias) {
                        return 1;
                    }

                    return strcmp($a->title, $b->title);
                });
            }

            $grouped = [];
            $placed = [];

            foreach ($orderedTags as $tag) {
                $tagAlias = strtolower($tag->alias);

                foreach ($faqItems as $item) {
                    $itemId = (int) $item['id'];

                    if (!empty($placed[$itemId])) {
                        continue;
                    }

                    $primary = $item['primary_tag'] ?? null;

                    if (!$primary && !empty($item['tags'])) {
                        $primary = $item['tags'][0];
                    }

                    $primaryAlias = $primary ? strtolower((string) ($primary['alias'] ?? '')) : '';

                    if ($primaryAlias === $tagAlias) {
                        $grouped[] = $item;
                        $placed[$itemId] = true;
                    }
                }
            }

            foreach ($faqItems as $item) {
                $itemId = (int) $item['id'];

                if (empty($placed[$itemId])) {
                    $grouped[] = $item;
                }
            }

            $faqItems = $grouped;
        }

        $result = [
            'items' => $faqItems,
            'allTags' => $faqAllTags,
            'selectedAlias' => $selectedAlias,
        ];

        self::$cache['page'][$cacheKey] = $result;

        return $result;
    }

    public static function getFeaturedFaq(int $limit = 9): array
    {
        $limit = max(1, (int) $limit);
        $context = self::getContext();
        $cacheKey = self::buildCacheKey('featured', (string) $limit);

        if (isset(self::$cache['featured'][$cacheKey])) {
            return self::$cache['featured'][$cacheKey];
        }

        $faqCatId = self::getCategoryId();

        if (!$faqCatId) {
            self::$cache['featured'][$cacheKey] = [];

            return [];
        }

        $db = Factory::getDbo();
        $now = $context['now'];
        $nullDateValue = $db->getNullDate();
        $nullDate = $db->quote($nullDateValue);
        $publishUpCondition =
            '(c.publish_up IS NULL OR c.publish_up = ' . $nullDate . ' OR c.publish_up <= ' . $db->quote($now) . ')';
        $publishDownCondition =
            '(c.publish_down IS NULL OR c.publish_down = ' .
            $nullDate .
            ' OR c.publish_down >= ' .
            $db->quote($now) .
            ')';
        $contentAccessCondition = self::buildViewLevelsCondition($db, 'c.access', $context['viewLevels']);
        $categoryAccessCondition = self::buildViewLevelsCondition($db, 'cat.access', $context['viewLevels']);
        $contentLanguageCondition = self::buildLanguageCondition($db, 'c.language', $context['language']);
        $categoryLanguageCondition = self::buildLanguageCondition($db, 'cat.language', $context['language']);

        $query = $db
            ->getQuery(true)
            ->select('c.id, c.title, c.introtext, c.fulltext, c.publish_up')
            ->from($db->quoteName('#__content', 'c'))
            ->join('LEFT', $db->quoteName('#__content_frontpage', 'fp') . ' ON fp.content_id = c.id')
            ->join('INNER', $db->quoteName('#__categories', 'cat') . ' ON cat.id = c.catid')
            ->where('c.state = 1')
            ->where('c.catid = ' . (int) $faqCatId)
            ->where('c.featured = 1')
            ->where($contentAccessCondition)
            ->where($contentLanguageCondition)
            ->where($categoryAccessCondition)
            ->where($categoryLanguageCondition)
            ->where('cat.published = 1')
            ->where($publishUpCondition)
            ->where($publishDownCondition)
            ->order('COALESCE(fp.ordering, 9999) ASC')
            ->order('c.publish_up DESC');

        $db->setQuery($query, 0, $limit);
        $rows = (array) $db->loadObjectList();

        $items = [];

        foreach ($rows as $row) {
            $question = trim((string) $row->title);

            if ($question === '') {
                continue;
            }

            $answerSource = $row->fulltext !== '' ? $row->fulltext : $row->introtext;
            [$answerHtml, $answerText] = self::prepareAnswer($answerSource ?? '');

            $items[] = [
                'id' => (int) $row->id,
                'q' => $question,
                'answer_text' => $answerText,
                'answer_html' => $answerHtml,
                'publish_up' =>
                    $row->publish_up && (string) $row->publish_up !== $nullDateValue ? (string) $row->publish_up : '',
            ];
        }

        self::$cache['featured'][$cacheKey] = $items;

        return $items;
    }

    public static function getFaqRoute(array $queryParams = [], string $fragment = ''): string
    {
        $article = self::getFaqArticle();

        if ($article) {
            $route = RouteHelper::getArticleRoute(
                $article['id'] . ':' . $article['alias'],
                $article['catid'],
                $article['language'] ?: 0,
            );
        } else {
            $route = 'index.php?option=com_content&view=article&layout=faq';
        }

        if (!empty($queryParams)) {
            $separator = strpos($route, '?') === false ? '?' : '&';
            $route .= $separator . http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986);
        }

        $fragment = ltrim($fragment, '#');

        if ($fragment !== '') {
            $route .= '#' . $fragment;
        }

        return Route::_($route);
    }

    public static function buildFaqLink(int $id, ?string $tagAlias = null): string
    {
        $query = [];
        $normalized = trim((string) ($tagAlias ?? ''));

        if ($normalized !== '') {
            $query['tag'] = $normalized;
        }

        $fragment = $id > 0 ? 'faq-q-' . $id : '';

        return self::getFaqRoute($query, $fragment);
    }

    protected static function getFaqArticle(): ?array
    {
        $cacheKey = self::buildCacheKey('faq_article');

        if (array_key_exists($cacheKey, self::$faqArticle)) {
            return self::$faqArticle[$cacheKey];
        }

        $context = self::getContext();
        $db = Factory::getDbo();
        $now = $context['now'];
        $nullDateValue = $db->getNullDate();
        $nullDate = $db->quote($nullDateValue);

        $contentAccessCondition = self::buildViewLevelsCondition($db, 'c.access', $context['viewLevels']);
        $categoryAccessCondition = self::buildViewLevelsCondition($db, 'cat.access', $context['viewLevels']);
        $contentLanguageCondition = self::buildLanguageCondition($db, 'c.language', $context['language']);
        $categoryLanguageCondition = self::buildLanguageCondition($db, 'cat.language', $context['language']);

        $query = $db
            ->getQuery(true)
            ->select('c.id, c.alias, c.catid, c.language')
            ->from($db->quoteName('#__content', 'c'))
            ->join('INNER', $db->quoteName('#__categories', 'cat') . ' ON cat.id = c.catid')
            ->where('c.state = 1')
            ->where($db->quoteName('c.alias') . ' = ' . $db->quote('faq'))
            ->where($contentAccessCondition)
            ->where($contentLanguageCondition)
            ->where($categoryAccessCondition)
            ->where($categoryLanguageCondition)
            ->where('cat.published = 1')
            ->where(
                '(c.publish_up IS NULL OR c.publish_up = ' .
                    $nullDate .
                    ' OR c.publish_up <= ' .
                    $db->quote($now) .
                    ')',
            )
            ->where(
                '(c.publish_down IS NULL OR c.publish_down = ' .
                    $nullDate .
                    ' OR c.publish_down >= ' .
                    $db->quote($now) .
                    ')',
            )
            ->order('c.publish_up DESC');

        $db->setQuery($query, 0, 1);
        $record = $db->loadObject();

        if (!$record) {
            self::$faqArticle[$cacheKey] = null;

            return null;
        }

        $article = [
            'id' => (int) $record->id,
            'alias' => (string) $record->alias,
            'catid' => (int) $record->catid,
            'language' => (string) ($record->language ?? ''),
        ];

        self::$faqArticle[$cacheKey] = $article;

        return $article;
    }

    protected static function getHtmlFilter(): InputFilter
    {
        if (self::$htmlFilter instanceof InputFilter) {
            return self::$htmlFilter;
        }

        $allowedTags = [
            'p',
            'a',
            'ul',
            'ol',
            'li',
            'strong',
            'em',
            'b',
            'i',
            'u',
            'blockquote',
            'code',
            'pre',
            'span',
            'br',
        ];
        $allowedAttrs = ['href', 'title', 'target', 'rel', 'class'];

        self::$htmlFilter = InputFilter::getInstance($allowedTags, $allowedAttrs);

        return self::$htmlFilter;
    }

    protected static function prepareAnswer(string $raw): array
    {
        if ($raw === '') {
            return ['', ''];
        }

        $prepared = HTMLHelper::_('content.prepare', $raw, '', 'com_content.article');
        $filter = self::getHtmlFilter();
        $cleanHtml = trim($filter->clean($prepared, 'html'));

        if ($cleanHtml === '') {
            return ['', ''];
        }

        $plain = self::htmlToPlainText($cleanHtml);

        return [$cleanHtml, $plain];
    }

    protected static function htmlToPlainText(string $html): string
    {
        $withBreaks = preg_replace("/<\/(p|div|li|blockquote|h[1-6])>/i", '</$1>' . PHP_EOL, $html);
        $withBreaks = preg_replace('/<li[^>]*>/i', '- ', $withBreaks);
        $withBreaks = preg_replace("/<br\s*\/?/i", PHP_EOL, $withBreaks);

        $text = strip_tags($withBreaks);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\r\n|\r/', "\n", $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/ *\n */', "\n", $text);

        return trim($text);
    }

    protected static function getCategoryId(): int
    {
        $cacheKey = self::buildCacheKey('category');

        if (array_key_exists($cacheKey, self::$categoryId)) {
            return (int) self::$categoryId[$cacheKey];
        }

        $context = self::getContext();
        $db = Factory::getDbo();
        $accessCondition = self::buildViewLevelsCondition($db, 'cat.access', $context['viewLevels']);
        $languageCondition = self::buildLanguageCondition($db, 'cat.language', $context['language']);

        $query = $db
            ->getQuery(true)
            ->select($db->quoteName('cat.id'))
            ->from($db->quoteName('#__categories', 'cat'))
            ->where($db->quoteName('cat.extension') . ' = ' . $db->quote('com_content'))
            ->where($db->quoteName('cat.alias') . ' = ' . $db->quote('faq'))
            ->where($db->quoteName('cat.published') . ' = 1')
            ->where($accessCondition)
            ->where($languageCondition);

        $db->setQuery($query);
        $categoryId = (int) $db->loadResult();

        if (!$categoryId) {
            $query = $db
                ->getQuery(true)
                ->select($db->quoteName('cat.id'))
                ->from($db->quoteName('#__categories', 'cat'))
                ->where($db->quoteName('cat.extension') . ' = ' . $db->quote('com_content'))
                ->where($db->quoteName('cat.title') . ' = ' . $db->quote('FAQ'))
                ->where($db->quoteName('cat.published') . ' = 1')
                ->where($accessCondition)
                ->where($languageCondition);

            $db->setQuery($query);
            $categoryId = (int) $db->loadResult();
        }

        self::$categoryId[$cacheKey] = $categoryId ?: 0;

        return (int) self::$categoryId[$cacheKey];
    }
}
