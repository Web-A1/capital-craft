<?php

defined("_JEXEC") or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

class CapitalcraftSeoHelper
{
    /**
     * Build canonical URL normalised to scheme/host/path and selected query params.
     */
    public static function buildCanonical(array $allowedQuery = []): string
    {
        $uri = Uri::getInstance();
        $canonicalUri = clone $uri;

        $allowedQueryLower = array_map("strtolower", $allowedQuery);
        $query = [];

        foreach ($uri->getQuery(true) as $key => $value) {
            if (in_array(strtolower($key), $allowedQueryLower, true)) {
                $query[$key] = $value;
            }
        }

        $canonicalUri->setQuery($query);
        $canonicalUri->setFragment("");

        return $canonicalUri->toString(["scheme", "host", "port", "path", "query"]);
    }

    /**
     * Ensure canonical link exists and points to provided URL.
     */
    public static function addCanonicalLink(string $url): void
    {
        $doc = Factory::getDocument();
        $head = $doc->getHeadData();

        if (!empty($head["links"])) {
            foreach ($head["links"] as $href => $linkData) {
                if (($linkData["relationType"] ?? "") === "rel" && ($linkData["relation"] ?? "") === "canonical") {
                    unset($head["links"][$href]);
                }
            }
            $doc->setHeadData($head);
        }

        $doc->addHeadLink($url, "canonical", "rel");
    }

    /**
     * Получаем ID хлебных крошек, который Joomla добавляет в JSON-LD по умолчанию.
     * Нужно, чтобы наша кастомная схема ссылалась на тот же @id.
     */
    public static function getDefaultBreadcrumbId(): ?string
    {
        $doc = Factory::getDocument();
        $head = $doc->getHeadData();

        if (empty($head["custom"])) {
            return null;
        }

        foreach ($head["custom"] as $tag) {
            if (stripos($tag, "application/ld+json") === false) {
                continue;
            }

            if (strpos($tag, "#/schema/BreadcrumbList/") === false) {
                continue;
            }

            if (strpos($tag, "article-") !== false) {
                continue;
            }

            if (preg_match('/\\"breadcrumb\\"\s*:\s*\{\\"@id\\"\s*:\s*\\"([^\\"]+)\\"\}/', $tag, $matches)) {
                $id = stripcslashes($matches[1]);
                return $id !== "" ? $id : null;
            }
        }

        return null;
    }

    /**
     * Build meta description with CTA fallback when metadesc is empty in admin.
     */
    public static function buildMetaDescriptionFromText(string $text, int $maxLength = 160): string
    {
        $text = trim(preg_replace("/\s+/u", " ", $text));
        if ($text === "") {
            return "";
        }

        $cta = " — Capital Craft.";
        $ctaLength = mb_strlen($cta, "UTF-8");
        $baseMax = max(60, $maxLength - $ctaLength);
        $base = self::truncateAtWord($text, $baseMax);

        $description = rtrim($base, " .,;:-");
        if ($description === "") {
            $description = self::truncateAtWord($text, $maxLength);
        }

        $result = $description . $cta;

        if (mb_strlen($result, "UTF-8") > $maxLength) {
            $result = self::truncateAtWord($result, $maxLength - 1) . "…";
        }

        return $result;
    }

    private static function truncateAtWord(string $text, int $maxLength): string
    {
        if (mb_strlen($text, "UTF-8") <= $maxLength) {
            return $text;
        }

        $slice = mb_substr($text, 0, $maxLength, "UTF-8");
        $clean = preg_replace('/\s+\S*$/u', "", $slice);

        return $clean !== "" ? $clean : $slice;
    }
}
