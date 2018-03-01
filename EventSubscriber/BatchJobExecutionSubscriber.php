<?php
namespace FlowMonitoringBundle\EventSubscriber;

use Akeneo\Component\Batch\Event\EventInterface;
use Akeneo\Component\Batch\Event\JobExecutionEvent;
use FlowMonitoringBundle\Monitor\Flow;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class BatchJobExecutionSubscriber
 * @package FlowMonitoringBundle\EventSubscriber
 * @author Grégory Tonon <tonon.gregory@gmail.com>
 * @copyright 2018 Grégory Tonon
 */
class BatchJobExecutionSubscriber implements EventSubscriberInterface
{
    /**
     * @var Flow
     */
    protected $flow;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager|object
     */
    protected $entityManager;

    /**
     * BatchJobExecutionSubscriber constructor.
     * @param Registry $registry
     * @param Flow $flow
     */
    public function __construct(Registry $registry, Flow $flow)
    {
        $this->entityManager = $registry->getManager();
        $this->flow = $flow;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            EventInterface::BEFORE_JOB_EXECUTION => [
                ['initializeFlow'],
                ['flowStart']
            ],
            EventInterface::AFTER_JOB_EXECUTION => 'flowEnd',
            EventInterface::JOB_EXECUTION_INTERRUPTED => 'flowEnd',
            EventInterface::JOB_EXECUTION_FATAL_ERROR => 'flowEnd'
        ];
    }

    /**
     * Initialize the flow for th current job execution
     * @param JobExecutionEvent $event
     */
    public function initializeFlow(JobExecutionEvent $event)
    {
        $flow = $this->entityManager->getRepository(\FlowMonitoringBundle\Entity\Flow::class)
            ->findOneBy(['jobExecutionId' => $event->getJobExecution()->getId()]);

        /** @var \FlowMonitoringBundle\Entity\Flow $flow */
        if ($flow) {
            $trigger = $flow->getTriggerClassName();
            $this->flow->setTrigger(new $trigger($flow->getContext()));
        } else {
            $this->flow = null;
        }
    }

    /**
     * Start the flow
     * @param JobExecutionEvent $event
     */
    public function flowStart(JobExecutionEvent $event)
    {
        if (null !== $this->flow) {
            $this->flow->start($event->getJobExecution());
        }
    }

    /**
     * Stop the flow
     * @param JobExecutionEvent $event
     */
    public function flowEnd(JobExecutionEvent $event)
    {
        if (null !== $this->flow) {
            $this->flow->end($event->getJobExecution());
        }
    }
}
