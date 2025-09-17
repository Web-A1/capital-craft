<?php
defined('_JEXEC') or die();

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

        $allowedQueryLower = array_map('strtolower', $allowedQuery);
        $query = [];

        foreach ($uri->getQuery(true) as $key => $value) {
            if (in_array(strtolower($key), $allowedQueryLower, true)) {
                $query[$key] = $value;
            }
        }

        $canonicalUri->setQuery($query);
        $canonicalUri->setFragment('');

        return $canonicalUri->toString(['scheme', 'host', 'port', 'path', 'query']);
    }

    /**
     * Ensure canonical link exists and points to provided URL.
     */
    public static function addCanonicalLink(string $url): void
    {
        $doc = Factory::getDocument();
        $head = $doc->getHeadData();

        if (!empty($head['links'])) {
            foreach ($head['links'] as $href => $linkData) {
                if (($linkData['relationType'] ?? '') === 'rel' && ($linkData['relation'] ?? '') === 'canonical') {
                    unset($head['links'][$href]);
                }
            }
            $doc->setHeadData($head);
        }

        $doc->addHeadLink($url, 'canonical', 'rel');
    }
}
