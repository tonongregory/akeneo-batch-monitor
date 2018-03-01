<?php
namespace FlowMonitoringBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package FlowMonitoring\DependencyInjection
 * @author Grégory Tonon <tonon.gregory@gmail.com>
 * @copyright 2018 Grégory Tonon
 */
class Configuration implements ConfigurationInterface
{
    protected $triggers;

    /**
     * Configuration constructor.
     * @param array $triggers
     */
    public function __construct(array $triggers = [])
    {
        $this->triggers = $triggers;
    }

    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('flow_monitoring');
        $this->addTriggersSection($rootNode);
        $this->addFlowsSection($rootNode);
        $rootNode->end();

        return $treeBuilder;
    }

    /**
     * Add triggers config section
     * @param ArrayNodeDefinition $node
     */
    protected function addTriggersSection(ArrayNodeDefinition $node)
    {
        $triggersNodeBuilder = $node
            ->fixXmlConfig('trigger')
            ->children()
                ->arrayNode('triggers')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                    ->performNoDeepMerging()
                    ->children();

        foreach ($this->triggers as $name => $trigger) {
            $triggerNode = $triggersNodeBuilder->arrayNode($name)->canBeUnset();
            $trigger->addConfiguration($triggerNode);
        }
    }

    /**
     * Add flows config sections
     * @param ArrayNodeDefinition $node
     */
    protected function addFlowsSection(ArrayNodeDefinition $node)
    {
        $node
            ->fixXmlConfig('flow')
                ->children()
                    ->arrayNode('flows')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                        ->performNoDeepMerging()
                        ->children()
                            ->scalarNode('trigger')->end()
                            ->scalarNode('job_instance_name')->end()
                            ->scalarNode('user')->defaultValue('admin')->end();
    }
}
