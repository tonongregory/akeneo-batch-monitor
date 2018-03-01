<?php
namespace FlowMonitoringBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class AddFlowsCompilerPass
 * @package FlowMonitoringBundle\DependencyInjection\Compiler
 * @author Grégory Tonon <tonon.gregory@gmail.com>
 * @copyright 2018 Grégory Tonon
 */
class AddFlowsCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('flow_monitoring.monitor.monitor')) {
            $definition = $container->getDefinition('flow_monitoring.monitor.monitor');
            foreach (array_keys($container->findTaggedServiceIds('flow_monitoring.flow')) as $id) {
                $definition->addMethodCall(
                    'addFlow',
                    [new Reference($id)]
                );
            }
        }
    }
}
