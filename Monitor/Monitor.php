<?php
namespace FlowMonitoringBundle\Monitor;

/**
 * Class Monitor
 * @package FlowMonitoringBundle\Monitor
 * @author Grégory Tonon <tonon.gregory@gmail.com>
 * @copyright 2018 Grégory Tonon
 */
class Monitor
{
    /**
     * @var array
     */
    protected $flows;

    /**
     * Add flow to monitor
     * @param Flow $flow
     */
    public function addFlow(Flow $flow)
    {
        $this->flows[] = $flow;
    }

    /**
     * Get registered flows
     * @return array
     */
    public function getFlows() : array
    {
        return null === $this->flows ? [] : $this->flows;
    }
}
