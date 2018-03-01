<?php
use FlowMonitoringBundle\DependencyInjection\Compiler\AddFlowsCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class FlowMonitoringBundle
 * @package FlowMonitoring
 * @author Grégory Tonon <tonon.gregory@gmail.com>
 * @copyright 2018 Grégory Tonon
 */
class FlowMonitoringBundle extends Bundle
{
    /**
     * Add custom compiler pass
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddFlowsCompilerPass());
    }
}
