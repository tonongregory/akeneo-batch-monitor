<?php
namespace FlowMonitoringBundle\Command;

use FlowMonitoringBundle\Monitor\Flow;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class FlowMonitorRunCommand
 * @package FlowMonitoringBundle\Command
 * @author Grégory Tonon <tonon.gregory@gmail.com>
 * @copyright 2018 Grégory Tonon
 */
class FlowMonitorRunCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    public function configure()
    {
        $this->setName('flow-monitor-run');
        $this->setDescription('Run job execution monitor to create flows');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $monitor = $this->getContainer()->get('flow_monitoring.monitor.monitor');

        /** @var Flow $flow */
        foreach ($monitor->getFlows() as $flow) {
            if ($flow->getTrigger()->isSatisfied()) {
                try {
                    $flow->createFlow();
                } catch (\Exception $e) {
                    return 0;
                }
            }
        }

        return 0;
    }
}
