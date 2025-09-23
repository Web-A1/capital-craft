<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.tagparam
 *
 * @copyright   (C) 2024
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CapitalCraft\Plugin\System\TagParam\Extension;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Event\Application\AfterRouteEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use stdClass;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Registers tag-related URL parameters as cache-safe for the site application.
 */
final class TagParam extends CMSPlugin implements SubscriberInterface
{
    /**
     * Automatically load the language file.
     *
     * @var  boolean
     */
    protected $autoloadLanguage = true;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterRoute' => 'onAfterRoute',
        ];
    }

    /**
     * Ensure that tag-related parameters are registered as safe URL parameters.
     */
    public function onAfterRoute(AfterRouteEvent $event): void
    {
        $application = $event->getApplication();

        if (!$application instanceof SiteApplication || !$application->isClient('site')) {
            return;
        }

        $registeredParams = $application->registeredurlparams ?? new stdClass();

        if (!$registeredParams instanceof stdClass) {
            $registeredParams = new stdClass();
        }

        $needsUpdate = false;

        if (!property_exists($registeredParams, 'tag') || $registeredParams->tag !== 'STRING') {
            $registeredParams->tag = 'STRING';
            $needsUpdate = true;
        }

        if (!property_exists($registeredParams, 'tagid') || $registeredParams->tagid !== 'INT') {
            $registeredParams->tagid = 'INT';
            $needsUpdate = true;
        }

        if ($needsUpdate) {
            $application->registeredurlparams = $registeredParams;
        }
    }
}
