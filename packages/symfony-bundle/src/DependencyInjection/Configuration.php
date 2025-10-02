<?php

declare(strict_types=1);

namespace Vollbehr\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * PHP Reader
 *
 * @package   \Vollbehr\Bridge\Symfony\DependencyInjection
 * @copyright (c) 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**
 * Defines the configuration tree for the Symfony PHP Reader bundle.
 *
 * @author Sven Vollbehr
 */
final class Configuration implements ConfigurationInterface
{
    /**
     * Builds the configuration tree consumed by Symfony.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('php_reader');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('default_file_mode')
                    ->defaultNull()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
