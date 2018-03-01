<?php
namespace FlowMonitoringBundle\Monitor;

use Akeneo\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\BatchQueue\Queue\JobExecutionMessage;
use Akeneo\Component\BatchQueue\Queue\JobExecutionQueueInterface;
use FlowMonitoringBundle\Trigger\TriggerInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * Class Flow
 * @package FlowMonitoringBundle\Monitor
 * @author Grégory Tonon <tonon.gregory@gmail.com>
 * @copyright 2018 Grégory Tonon
 */
class Flow
{
    /**
     * @var TriggerInterface
     */
    protected $trigger;

    /**
     * @var string
     */
    protected $jobInstanceName;

    /**
     * @var DoctrineJobRepository
     */
    protected $jobRepository;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager|object
     */
    protected $entityManager;

    /**
     * @var JobExecutionQueueInterface
     */
    protected $jobExecutionQueue;

    /**
     * @var JobExecution
     */
    protected $jobExecution;

    /**
     * @var string
     */
    protected $user;

    /**
     * Flow constructor.
     * @param DoctrineJobRepository $jobRepository
     * @param JobExecutionQueueInterface $jobExecutionQueue
     * @param Registry $registry
     */
    public function __construct(
        DoctrineJobRepository $jobRepository,
        JobExecutionQueueInterface $jobExecutionQueue,
        Registry $registry
    ) {
        $this->jobRepository = $jobRepository;
        $this->entityManager = $registry->getManager();
        $this->jobExecutionQueue = $jobExecutionQueue;
    }

    /**
     * Set the trigger attached to the flow
     * @param TriggerInterface $trigger
     */
    public function setTrigger(TriggerInterface $trigger) : void
    {
        $this->trigger = $trigger;
    }

    /**
     * Get the attached trigger
     * @return TriggerInterface|null
     */
    public function getTrigger() :? TriggerInterface
    {
        return $this->trigger;
    }

    /**
     * @return mixed
     */
    public function getJobInstanceName() :? string
    {
        return $this->jobInstanceName;
    }

    /**
     * @param mixed $jobInstanceName
     */
    public function setJobInstanceName(string $jobInstanceName): void
    {
        $this->jobInstanceName = $jobInstanceName;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function setUser(string $user): void
    {
        $this->user = $user;
    }

    /**
     * Return true if trigger is satisfied
     * @return bool
     */
    public function canLaunch()
    {
        return $this->trigger->isSatisfied();
    }

    /**
     * Start the flow
     * @param JobExecution $jobExecution
     */
    public function start(JobExecution $jobExecution)
    {
        $this->getTrigger()->flowStart($jobExecution);
    }

    /**
     * Stop the flow
     * @param JobExecution $jobExecution
     */
    public function end(JobExecution $jobExecution)
    {
        $this->getTrigger()->flowEnd($jobExecution);
    }

    /**
     * Create a new job execution, job_execution_queue and a new flow
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createFlow()
    {
        $jobInstance = $this->jobRepository->getJobManager()
            ->getRepository(JobInstance::class)->findOneBy(['code' => $this->getJobInstanceName()]);

        /** @var JobInstance $jobInstance */
        if ($jobInstance) {
            $jobParameters = array_merge(
                $jobInstance->getRawParameters(),
                $this->trigger->getJobParameters()
            );
            $jobExecution = $this->jobRepository->createJobExecution(
                $jobInstance,
                new JobParameters($jobParameters)
            );
            $jobExecution->setUser($this->getUser());
            $this->jobRepository->updateJobExecution($jobExecution);

            $jobExecutionMessage = JobExecutionMessage::createJobExecutionMessage(
                $jobExecution->getId(),
                ['env' => 'prod']
            );
            $this->jobExecutionQueue->publish($jobExecutionMessage);
            $this->entityManager->getRepository(\FlowMonitoringBundle\Entity\Flow::class)
                ->createFlow($jobExecution, $this);
        }
    }
}
