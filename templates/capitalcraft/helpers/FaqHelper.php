<?php

defined("_JEXEC") or die();

use Joomla\CMS\Factory;

class CapitalcraftFaqHelper
{
    protected static $categoryId = null;

    protected static $cache = [
        "page" => [],
        "featured" => [],
    ];

    public static function getFaqPageData(string $tagParam = ""): array
    {
        $normalizedTag = strtolower(trim($tagParam));

        if (isset(self::$cache["page"][$normalizedTag])) {
            return self::$cache["page"][$normalizedTag];
        }

        $faqCatId = self::getCategoryId();

        if (!$faqCatId) {
            $empty = [
                "items" => [],
                "allTags" => [],
                "selectedAlias" => "",
            ];
            self::$cache["page"][$normalizedTag] = $empty;

            return $empty;
        }

        $db = Factory::getDbo();

        $selectedAlias = "";
        $tagIds = [];
        $rawTag = trim($tagParam);

        if ($rawTag !== "") {
            if (ctype_digit($rawTag)) {
                $tagIds = [(int) $rawTag];
                $query = $db
                    ->getQuery(true)
                    ->select($db->quoteName("alias"))
                    ->from($db->quoteName("#__tags"))
                    ->where($db->quoteName("id") . " = " . (int) $rawTag)
                    ->where($db->quoteName("published") . " = 1");
                $db->setQuery($query);
                $selectedAlias = strtolower((string) $db->loadResult());
            } else {
                $query = $db
                    ->getQuery(true)
                    ->select($db->quoteName("id"))
                    ->from($db->quoteName("#__tags"))
                    ->where($db->quoteName("alias") . " = " . $db->quote($rawTag))
                    ->where($db->quoteName("published") . " = 1");
                $db->setQuery($query);
                $foundId = (int) $db->loadResult();

                if ($foundId) {
                    $tagIds = [$foundId];
                    $selectedAlias = strtolower($rawTag);
                }
            }
        }

        $query = $db
            ->getQuery(true)
            ->select("c.id, c.title, c.introtext, c.fulltext, c.publish_up")
            ->from($db->quoteName("#__content", "c"))
            ->where("c.state = 1")
            ->where("c.catid = " . (int) $faqCatId)
            ->order("c.publish_up DESC");

        $db->setQuery($query);
        $rows = (array) $db->loadObjectList();

        $faqItems = [];
        $faqIds = [];

        foreach ($rows as $row) {
            $answerSource = $row->fulltext !== "" ? $row->fulltext : $row->introtext ?? "";
            $faqItems[] = [
                "id" => (int) $row->id,
                "q" => (string) $row->title,
                "a" => trim(strip_tags((string) $answerSource)),
                "tags" => [],
            ];
            $faqIds[] = (int) $row->id;
        }

        if (!empty($faqIds)) {
            $query = $db
                ->getQuery(true)
                ->select(
                    $db->quoteName("m.content_item_id", "cid") .
                        ", " .
                        $db->quoteName("t.id") .
                        ", " .
                        $db->quoteName("t.title") .
                        ", " .
                        $db->quoteName("t.alias"),
                )
                ->from($db->quoteName("#__contentitem_tag_map", "m"))
                ->join("INNER", $db->quoteName("#__tags", "t") . " ON t.id = m.tag_id")
                ->where("m.type_alias = " . $db->quote("com_content.article"))
                ->where("t.published = 1")
                ->where("m.content_item_id IN (" . implode(",", array_map("intval", $faqIds)) . ")")
                ->order($db->quoteName("m.tag_date") . " ASC");

            $db->setQuery($query);
            $tagRows = (array) $db->loadObjectList();

            $tagsById = [];

            foreach ($tagRows as $tagRow) {
                $contentId = (int) $tagRow->cid;

                if (!isset($tagsById[$contentId])) {
                    $tagsById[$contentId] = [];
                }

                $tagsById[$contentId][] = [
                    "id" => (int) $tagRow->id,
                    "title" => (string) $tagRow->title,
                    "alias" => (string) $tagRow->alias,
                ];
            }

            foreach ($faqItems as $index => $item) {
                $id = (int) $item["id"];

                if (isset($tagsById[$id])) {
                    $faqItems[$index]["tags"] = array_values($tagsById[$id]);
                    $faqItems[$index]["primary_tag"] = $faqItems[$index]["tags"][0] ?? null;
                }
            }
        }

        $query = $db
            ->getQuery(true)
            ->select("DISTINCT t.id, t.title, t.alias")
            ->from($db->quoteName("#__tags", "t"))
            ->join(
                "INNER",
                $db->quoteName("#__contentitem_tag_map", "m") .
                    " ON m.tag_id = t.id AND m.type_alias = " .
                    $db->quote("com_content.article"),
            )
            ->join(
                "INNER",
                $db->quoteName("#__content", "c") .
                    " ON c.id = m.content_item_id AND c.state = 1 AND c.catid = " .
                    (int) $faqCatId,
            )
            ->where("t.published = 1")
            ->order("t.title ASC");

        $db->setQuery($query);
        $faqAllTags = (array) $db->loadObjectList();

        if (!empty($faqItems) && !empty($faqAllTags)) {
            $orderedTags = $faqAllTags;

            if ($selectedAlias !== "") {
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
                    $itemId = (int) $item["id"];

                    if (!empty($placed[$itemId])) {
                        continue;
                    }

                    $primary = $item["primary_tag"] ?? null;

                    if (!$primary && !empty($item["tags"])) {
                        $primary = $item["tags"][0];
                    }

                    $primaryAlias = $primary ? strtolower((string) ($primary["alias"] ?? "")) : "";

                    if ($primaryAlias === $tagAlias) {
                        $grouped[] = $item;
                        $placed[$itemId] = true;
                    }
                }
            }

            foreach ($faqItems as $item) {
                $itemId = (int) $item["id"];

                if (empty($placed[$itemId])) {
                    $grouped[] = $item;
                }
            }

            $faqItems = $grouped;
        }

        $result = [
            "items" => $faqItems,
            "allTags" => $faqAllTags,
            "selectedAlias" => $selectedAlias,
        ];

        self::$cache["page"][$normalizedTag] = $result;

        return $result;
    }

    public static function getFeaturedFaq(int $limit = 9): array
    {
        $limit = max(1, (int) $limit);
        $cacheKey = $limit;

        if (isset(self::$cache["featured"][$cacheKey])) {
            return self::$cache["featured"][$cacheKey];
        }

        $faqCatId = self::getCategoryId();

        if (!$faqCatId) {
            self::$cache["featured"][$cacheKey] = [];

            return [];
        }

        $db = Factory::getDbo();

        $query = $db
            ->getQuery(true)
            ->select("c.id, c.title, c.introtext, c.fulltext")
            ->from($db->quoteName("#__content", "c"))
            ->join("LEFT", $db->quoteName("#__content_frontpage", "fp") . " ON fp.content_id = c.id")
            ->where("c.state = 1")
            ->where("c.catid = " . (int) $faqCatId)
            ->where("c.featured = 1")
            ->order("COALESCE(fp.ordering, 9999) ASC")
            ->order("c.publish_up DESC");

        $db->setQuery($query, 0, $limit);
        $rows = (array) $db->loadObjectList();

        $items = [];

        foreach ($rows as $row) {
            $question = trim((string) $row->title);

            if ($question === "") {
                continue;
            }

            $answerSource = $row->fulltext !== "" ? $row->fulltext : $row->introtext;

            $items[] = [
                "id" => (int) $row->id,
                "q" => $question,
                "a" => trim(strip_tags((string) $answerSource)),
            ];
        }

        self::$cache["featured"][$cacheKey] = $items;

        return $items;
    }

    protected static function getCategoryId(): int
    {
        if (self::$categoryId !== null) {
            return (int) self::$categoryId;
        }

        $db = Factory::getDbo();

        $query = $db
            ->getQuery(true)
            ->select($db->quoteName("id"))
            ->from($db->quoteName("#__categories"))
            ->where($db->quoteName("extension") . " = " . $db->quote("com_content"))
            ->where($db->quoteName("alias") . " = " . $db->quote("faq"))
            ->where($db->quoteName("published") . " = 1");

        $db->setQuery($query);
        $categoryId = (int) $db->loadResult();

        if (!$categoryId) {
            $query = $db
                ->getQuery(true)
                ->select($db->quoteName("id"))
                ->from($db->quoteName("#__categories"))
                ->where($db->quoteName("extension") . " = " . $db->quote("com_content"))
                ->where($db->quoteName("title") . " = " . $db->quote("FAQ"))
                ->where($db->quoteName("published") . " = 1");

            $db->setQuery($query);
            $categoryId = (int) $db->loadResult();
        }

        self::$categoryId = $categoryId ?: 0;

        return (int) self::$categoryId;
    }
}
