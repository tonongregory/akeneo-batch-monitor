<?php
namespace FlowMonitoringBundle\Repository;

use Akeneo\Component\Batch\Model\JobExecution;
use FlowMonitoringBundle\Monitor\Flow;
use FlowMonitoringBundle\Entity\Flow as FlowEntity;
use Doctrine\ORM\EntityRepository;

/**
 * Class FlowRepository
 * @package FlowMonitoringBundle\Repository
 * @author Grégory Tonon <tonon.gregory@gmail.com>
 * @copyright 2018 Grégory Tonon
 */
class FlowRepository extends EntityRepository
{
    /**
     * Create a new flow in database
     * Attach the flow to job execution
     * @param JobExecution $execution
     * @param Flow $flow
     * @return FlowEntity
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createFlow(JobExecution $execution, Flow $flow) : FlowEntity
    {
        $entity = new FlowEntity();
        $entity->setContext($flow->getTrigger()->getContext());
        $entity->setJobExecution($execution);
        $entity->setTriggerClassName(get_class($flow->getTrigger()));
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush($entity);

        return $entity;
    }
}
