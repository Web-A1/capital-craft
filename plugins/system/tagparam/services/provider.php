<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.tagparam
 *
 * @copyright   (C) 2024
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use CapitalCraft\Plugin\System\TagParam\Extension\TagParam;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     */
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                return new TagParam(
                    $container->get(DispatcherInterface::class),
                    (array) PluginHelper::getPlugin('system', 'tagparam')
                );
            }
        );
    }
};
