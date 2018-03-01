<?php
namespace FlowMonitoringBundle\Trigger;

use Akeneo\Component\Batch\Model\JobExecution;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * Interface TriggerInterface
 * @package FlowMonitoring\Triggers
 */
interface TriggerInterface
{
    /**
     * Return true if current trigger is satisfied
     * @return bool
     */
    public function isSatisfied() : bool;

    /**
     * TriggerInterface constructor.
     * @param array $context
     */
    public function __construct(array $context = []);

    /**
     * Get the name of the trigger
     * @return string
     */
    public function getName() : string;

    /**
     * Add configuration to tree builder
     * @param ArrayNodeDefinition $node
     */
    public function addConfiguration(ArrayNodeDefinition $node) : void;

    /**
     * Get the context
     * @return array
     */
    public function getContext() : array;

    /**
     * Action when flow is started
     * @param JobExecution $jobExecution
     */
    public function flowStart(JobExecution $jobExecution) : void;

    /**
     * Action when flow is stopped
     * @param JobExecution $jobExecution
     */
    public function flowEnd(JobExecution $jobExecution) : void;

    /**
     * Get job parameter to override existing job configuration
     * @return array
     */
    public function getJobParameters() : array;
}
