<?php

defined("_JEXEC") or die();

use Joomla\CMS\Factory;

class CapitalcraftTagFilterHelper
{
    protected static $context;
    protected static $cache = [];

    protected static function getContext(): array
    {
        if (self::$context !== null) {
            return self::$context;
        }

        $app = Factory::getApplication();
        $user = Factory::getUser();
        $lang = $app->getLanguage()->getTag() ?: "*";
        $now = Factory::getDate()->toSql();
        $levels = array_map("intval", $user->getAuthorisedViewLevels());

        if (empty($levels)) {
            $levels = [0];
        }

        sort($levels);

        self::$context = [
            "language" => $lang,
            "levels" => $levels,
            "now" => $now,
        ];

        return self::$context;
    }

    protected static function buildLevelsCondition($db, string $column, array $levels): string
    {
        $safe = array_map("intval", $levels);

        return $db->quoteName($column) . " IN (" . implode(",", $safe) . ")";
    }

    protected static function buildLanguageCondition($db, string $column, $language): string
    {
        if ($language === "*" || $language === "") {
            return "1=1";
        }

        $allowed = [$db->quote("*"), $db->quote($language)];

        return $db->quoteName($column) . " IN (" . implode(",", $allowed) . ")";
    }

    protected static function buildCategoryCondition(
        $db,
        int $categoryId,
        bool $includeChildren,
        int $maxLevels,
    ): string {
        if ($categoryId <= 0) {
            return "";
        }

        if (!$includeChildren) {
            return $db->quoteName("cat.id") . " = " . (int) $categoryId;
        }

        $subQuery = $db
            ->getQuery(true)
            ->select($db->quoteName("child.id"))
            ->from($db->quoteName("#__categories", "child"))
            ->join(
                "INNER",
                $db->quoteName("#__categories", "parent"),
                $db->quoteName("child.lft") .
                    " > " .
                    $db->quoteName("parent.lft") .
                    " AND " .
                    $db->quoteName("child.rgt") .
                    " < " .
                    $db->quoteName("parent.rgt"),
            )
            ->where($db->quoteName("parent.id") . " = " . (int) $categoryId);

        if ($maxLevels > 0) {
            $subQuery->where(
                $db->quoteName("child.level") . " <= " . $db->quoteName("parent.level") . " + " . (int) $maxLevels,
            );
        }

        return "(" .
            $db->quoteName("cat.id") .
            " = " .
            (int) $categoryId .
            " OR " .
            $db->quoteName("cat.id") .
            " IN (" .
            $subQuery .
            "))";
    }

    protected static function queryTags(array $options): array
    {
        $context = self::getContext();

        $categoryId = (int) ($options["categoryId"] ?? 0);
        $includeChildren = (bool) ($options["includeSubcategories"] ?? false);
        $maxLevels = (int) ($options["maxSubLevels"] ?? 0);
        $excludeTagId = (int) ($options["excludeTagId"] ?? 0);
        $language = $options["language"] ?? $context["language"];
        $typeAlias = $options["typeAlias"] ?? "com_content.article";
        $cacheKeyParts = [
            $categoryId,
            $includeChildren ? "1" : "0",
            $maxLevels,
            $excludeTagId,
            $language,
            $typeAlias,
            implode("-", $context["levels"]),
        ];
        $cacheKey = implode(":", $cacheKeyParts);

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $db       = Factory::getDbo();
        $nullDate = $db->getNullDate();
        $publishUpCondition =
            "(" .
            $db->quoteName("c.publish_up") .
            " IS NULL OR " .
            $db->quoteName("c.publish_up") .
            " = " .
            $db->quote($nullDate) .
            " OR " .
            $db->quoteName("c.publish_up") .
            " <= " .
            $db->quote($context["now"]) .
            ")";
        $publishDownCondition =
            "(" .
            $db->quoteName("c.publish_down") .
            " IS NULL OR " .
            $db->quoteName("c.publish_down") .
            " = " .
            $db->quote($nullDate) .
            " OR " .
            $db->quoteName("c.publish_down") .
            " >= " .
            $db->quote($context["now"]) .
            ")";

        $query = $db
            ->getQuery(true)
            ->select("DISTINCT t.id, t.title, t.alias")
            ->from($db->quoteName("#__tags", "t"))
            ->join(
                "INNER",
                $db->quoteName("#__contentitem_tag_map", "m") .
                    " ON m.tag_id = t.id AND m.type_alias = " .
                    $db->quote($typeAlias),
            )
            ->join(
                "INNER",
                $db->quoteName("#__content", "c"),
                $db->quoteName("c.id") . " = " . $db->quoteName("m.content_item_id"),
            )
            ->join(
                "INNER",
                $db->quoteName("#__categories", "cat"),
                $db->quoteName("cat.id") . " = " . $db->quoteName("c.catid"),
            )
            ->where($db->quoteName("t.published") . " = 1")
            ->where($db->quoteName("c.state") . " = 1")
            ->where($db->quoteName("cat.published") . " = 1")
            ->where(self::buildLevelsCondition($db, "t.access", $context["levels"]))
            ->where(self::buildLevelsCondition($db, "c.access", $context["levels"]))
            ->where(self::buildLevelsCondition($db, "cat.access", $context["levels"]))
            ->where(self::buildLanguageCondition($db, "t.language", $language))
            ->where(self::buildLanguageCondition($db, "c.language", $language))
            ->where(self::buildLanguageCondition($db, "cat.language", $language))
            ->where($publishUpCondition)
            ->where($publishDownCondition);

        if ($excludeTagId > 0) {
            $query->where($db->quoteName("t.id") . " != " . (int) $excludeTagId);
        }

        $categoryCondition = self::buildCategoryCondition($db, $categoryId, $includeChildren, $maxLevels);

        if ($categoryCondition !== "") {
            $query->where($categoryCondition);
        }

        $query
            ->group($db->quoteName("t.id") . ", " . $db->quoteName("t.title") . ", " . $db->quoteName("t.alias"))
            ->order($db->quoteName("t.title") . " ASC");

        $db->setQuery($query);
        $result = (array) $db->loadObjectList();

        self::$cache[$cacheKey] = $result;

        return $result;
    }

    public static function getBlogTags(int $categoryId, array $options = []): array
    {
        if ($categoryId <= 0) {
            return [];
        }

        return self::queryTags(array_merge($options, ["categoryId" => $categoryId]));
    }

    public static function getAllTags(array $options = []): array
    {
        return self::queryTags($options);
    }
}
