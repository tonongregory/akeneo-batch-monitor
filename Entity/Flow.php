<?php
namespace FlowMonitoringBundle\Entity;

use Akeneo\Component\Batch\Model\JobExecution;

/**
 * Class Flow
 * @package FlowMonitoringBundle\Entity
 * @author Grégory Tonon <tonon.gregory@gmail.com>
 * @copyright 2018 Grégory Tonon
 */
class Flow
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var array
     */
    protected $context;

    /**
     * @var integer
     */
    protected $jobExecutionId;

    /**
     * @var string
     */
    protected $triggerClassName;

    /**
     * @var JobExecution
     */
    protected $jobExecution;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * Get the context
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set the context
     * @param array $context
     */
    public function setContext(array $context = []): void
    {
        $this->context = $context;
    }

    /**
     * Get the trigger class name
     * @return string
     */
    public function getTriggerClassName()
    {
        return $this->triggerClassName;
    }

    /**
     * Set the trigger class name
     * @param string $triggerClassName
     */
    public function setTriggerClassName(string $triggerClassName): void
    {
        $this->triggerClassName = $triggerClassName;
    }

    /**
     * @return JobExecution
     */
    public function getJobExecution()
    {
        return $this->jobExecution;
    }

    /**
     * @param JobExecution $jobExecution
     */
    public function setJobExecution(JobExecution $jobExecution): void
    {
        $this->jobExecution = $jobExecution;
        $this->jobExecutionId = $jobExecution->getId();
    }
}
