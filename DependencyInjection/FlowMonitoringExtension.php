<?php
namespace FlowMonitoringBundle\DependencyInjection;

use FlowMonitoringBundle\Trigger\FileSystem;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class FlowMonitoringExtension
 * @package FlowMonitoring\DependencyInjection
 * @author Grégory Tonon <tonon.gregory@gmail.com>
 * @copyright 2018 Grégory Tonon
 */
class FlowMonitoringExtension extends Extension
{
    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('triggers.yml');
        $loader->load('services.yml');
        $loader->load('event_subscribers.yml');

        $configuration = new Configuration($this->getTriggers($container));
        $config = $this->processConfiguration($configuration, $configs);

        $this->createTriggersServices($container, $config);
        $this->createFlowsServices($container, $config);
    }

    /**
     * Get all services tagged as flow_monitoring.trigger
     * @param ContainerBuilder $container
     * @return array
     * @throws \Exception
     */
    protected function getTriggers(ContainerBuilder $container) : array
    {
        $triggers = [];
        foreach (array_keys($container->findTaggedServiceIds('flow_monitoring.trigger')) as $id) {
            $triggers[$container->get($id)->getName()] = $container->get($id);
        }

        return $triggers;
    }

    /**
     * Create trigger according to configuration
     * @param ContainerBuilder $container
     * @param array $config
     */
    protected function createTriggersServices(ContainerBuilder $container, array $config = [])
    {
        foreach ($config['flows'] as $nameFlow => $flow) {
            $trigger = $config['triggers'][$flow['trigger']] ?? [];
            foreach ($trigger as $type => $configuration) {
                switch ($type) {
                    case 'filesystem':
                        $id = sprintf('flow_monitoring.trigger.%s', $flow['trigger']);
                        $configuration = $config['triggers'][$flow['trigger']][$type];
                        $definition = new Definition(FileSystem::class);
                        $definition->addMethodCall('setDirectory', [$configuration['directory']]);
                        if (null !== $configuration['data_file_extension']) {
                            $definition->addMethodCall(
                                'setDataFileExtension',
                                [$configuration['data_file_extension']]
                            );
                        }
                        $definition->addMethodCall('setTriggerFilePattern', [$configuration['trigger_file_pattern']]);
                        $definition->addMethodCall('setRunExtension', [$configuration['run_extension']]);
                        $definition->addMethodCall('setDoneExtension', [$configuration['done_extension']]);
                        $definition->addMethodCall(
                            'setDeleteDataOnFlowEnd',
                            [$configuration['delete_data_on_flow_end']]
                        );
                        $definition->addTag('flow_monitoring.concrete.trigger');
                        $definition->setAbstract(false);
                        $container->setDefinition($id, $definition);
                        break;
                    default:
                        break;
                }
            }
        }
    }

    /**
     * Create flow services according configuration
     * @param ContainerBuilder $container
     * @param array $config
     */
    public function createFlowsServices(ContainerBuilder $container, array $config = [])
    {
        foreach ($config['flows'] as $name => $flow) {
            $id = sprintf('flow_monitoring.flow.%s', $name);
            $definition = clone $container->getDefinition('flow_monitoring.monitor.flow');
            $definition->addMethodCall('setJobInstanceName', [$flow['job_instance_name']]);
            $definition->addMethodCall('setUser', [$flow['user']]);
            $definition->addMethodCall(
                'setTrigger',
                [new Reference('flow_monitoring.trigger.' . $flow['trigger'])]
            );

            $definition->addTag('flow_monitoring.flow');
            $definition->setAbstract(false);
            $container->setDefinition($id, $definition);
        }
    }
}
