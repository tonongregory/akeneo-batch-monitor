<?php
namespace FlowMonitoringBundle\Trigger;

use Akeneo\Component\Batch\Model\JobExecution;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class FileSystem
 * @package FlowMonitoringBundle\Trigger
 * @author Grégory Tonon <tonon.gregory@gmail.com>
 * @copyright 2018 Grégory Tonon
 */
class FileSystem implements TriggerInterface
{
    /**
     * @var string
     */
    protected $directory;

    /**
     * @var string
     */
    protected $dataFileExtension;

    /**
     * @var string
     */
    protected $triggerFilePattern;

    /**
     * @var string
     */
    protected $runExtension;

    /**
     * @var string
     */
    protected $doneExtension;

    /**
     * @var \SplFileInfo|null
     */
    protected $triggerFile;

    /**
     * @var \SplFileInfo
     */
    protected $originalTriggerFile;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * @var array
     */
    protected $context;

    /**
     * @var boolean
     */
    protected $deleteDataOnFlowEnd;

    public function __construct(array $context = [])
    {
        $this->context = $context;
        $this->filesystem = new \Symfony\Component\Filesystem\Filesystem();
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'filesystem';
    }

    /**
     * Add configuration to configuration tree builder
     * @param ArrayNodeDefinition $node
     */
    public function addConfiguration(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                    ->scalarNode('directory')->end()
                    ->scalarNode('data_file_extension')->end()
                    ->scalarNode('trigger_file_pattern')->end()
                    ->scalarNode('run_extension')->end()
                    ->scalarNode('done_extension')->end()
                    ->scalarNode('delete_data_on_flow_end')->defaultTrue()->end()
                ->end();
    }

    /**
     * Return true if trigger found file with $this->triggerFilePattern extension in $this->directory
     * The flow can be created
     * @return bool
     */
    public function isSatisfied(): bool
    {
        if ($this->getTriggerFile() && !$this->isLocked()) {
            $this->lock();

            return true;
        }

        return false;
    }

    /**
     * Return the first trigger file found in directory
     * @return null|\SplFileInfo
     */
    public function getTriggerFile() :? \SplFileInfo
    {
        if (null === $this->triggerFile) {
            $finder = new Finder();
            $finder->name(sprintf("/%s/", $this->triggerFilePattern))->in($this->directory)->sortByModifiedTime();

            // Exclude locked files
            $lockFinder = new Finder();
            /** @var SplFileInfo $lockFile */
            foreach ($lockFinder->name('*.lock')->in($this->directory)->files() as $lockFile) {
                $finder->notName(
                    $lockFile->getBasename($lockFile->getExtension()) .
                    substr($this->triggerFilePattern, strpos($this->triggerFilePattern, '.') + 1)
                );
            }

            if ($finder->count() > 0) {
                $this->triggerFile = $finder->getIterator()->current();
                $this->originalTriggerFile = $finder->getIterator()->current();
            }
        }


        return $this->triggerFile;
    }

    /**
     * Return context
     * @return array
     */
    public function getContext(): array
    {
        return [
            'trigger_file'          => $this->getTriggerFile()->getRealPath(),
            'directory'             => $this->getDirectory(),
            'data_file_extension'   => $this->getDataFileExtension(),
            'trigger_file_pattern'  => $this->getTriggerFilePattern(),
            'run_extension'         => $this->getRunExtension(),
            'done_extension'        => $this->getDoneExtension(),
            'delete_data_on_flow_end' => $this->isDeleteDataOnFlowEnd(),
        ];
    }

    /**
     * Init current instance from given context when instantiate
     */
    protected function initFromContext()
    {
        $this->directory = $this->context['directory'] ?? null;
        $this->dataFileExtension = $this->context['data_file_extension'] ?? null;
        $this->triggerFilePattern = $this->context['trigger_file_pattern'] ?? null;
        $this->runExtension = $this->context['run_extension'] ?? null;
        $this->doneExtension = $this->context['done_extension'] ?? null;
        $this->deleteDataOnFlowEnd = $this->context['delete_data_on_flow_end'] ?? null;
        $this->triggerFile = new \SplFileInfo(
            $this->context['trigger_file']
        );
        if (null === $this->originalTriggerFile) {
            $this->originalTriggerFile = new \SplFileInfo(
                $this->context['trigger_file']
            );
        }
    }

    /**
     * @return mixed
     */
    public function getDirectory() :? string
    {
        return $this->directory;
    }

    /**
     * @param mixed $directory
     */
    public function setDirectory(string $directory): void
    {
        if (!$this->filesystem->exists($directory)) {
            $this->filesystem->mkdir($directory);
        }

        $this->directory = $directory;
    }

    /**
     * @return mixed
     */
    public function getDataFileExtension() :? string
    {
        return $this->dataFileExtension;
    }

    /**
     * @param mixed $dataFileExtension
     */
    public function setDataFileExtension(string $dataFileExtension): void
    {
        $this->dataFileExtension = $dataFileExtension;
    }

    /**
     * @return mixed
     */
    public function getTriggerFilePattern() :? string
    {
        return $this->triggerFilePattern;
    }

    /**
     * @param mixed $triggerFilePattern
     */
    public function setTriggerFilePattern($triggerFilePattern): void
    {
        $this->triggerFilePattern = $triggerFilePattern;
    }

    /**
     * @return mixed
     */
    public function getRunExtension() :? string
    {
        return $this->runExtension;
    }

    /**
     * @param mixed $runExtension
     */
    public function setRunExtension(string $runExtension): void
    {
        $this->runExtension = $runExtension;
    }

    /**
     * @return mixed
     */
    public function getDoneExtension() :? string
    {
        return $this->doneExtension;
    }

    /**
     * @param mixed $doneExtension
     */
    public function setDoneExtension(string $doneExtension): void
    {
        $this->doneExtension = $doneExtension;
    }

    /**
     * @return bool
     */
    public function isDeleteDataOnFlowEnd(): bool
    {
        return $this->deleteDataOnFlowEnd;
    }

    /**
     * @param bool $deleteDataOnFlowEnd
     */
    public function setDeleteDataOnFlowEnd(bool $deleteDataOnFlowEnd): void
    {
        $this->deleteDataOnFlowEnd = $deleteDataOnFlowEnd;
    }


    /**
     * @inheritdoc
     */
    public function flowStart(JobExecution $jobExecution): void
    {
        $this->initFromContext();

        // Rename the trigger file replacing action extension by run extension
        $runFilename = dirname($this->getTriggerFile()->getRealPath()) . DIRECTORY_SEPARATOR .
            $this->getTriggerFile()->getBasename("." . $this->getTriggerFile()->getExtension()) . '.' .
            $this->getRunExtension();
        $this->filesystem->rename($this->getTriggerFile()->getRealPath(), $runFilename);
        $this->context['trigger_file'] = $runFilename;
    }

    /**
     * @inheritdoc
     */
    public function flowEnd(JobExecution $jobExecution): void
    {
        $this->initFromContext();

        // Rename the trigger file replacing run extension by done extension
        $doneFilename = dirname($this->getTriggerFile()->getRealPath()) . DIRECTORY_SEPARATOR .
            $this->getTriggerFile()->getBasename("." . $this->getTriggerFile()->getExtension()) . '.' .
            $this->getDoneExtension();
        $this->filesystem->rename($this->getTriggerFile()->getRealPath(), $doneFilename);
        $this->triggerFile = new \SplFileInfo($doneFilename);

        // Copy log file content into done file
        $this->filesystem->dumpFile(
            $doneFilename,
            file_get_contents($jobExecution->getLogFile())
        );

        // Remove lock file
        $this->unlock();

        // Remove data file
        if ($this->isDeleteDataOnFlowEnd()) {
            $dataFilename = $this->getDataFilename();
            if ($this->filesystem->exists($dataFilename)) {
                $this->filesystem->remove($dataFilename);
            }
        }
    }

    /**
     * Return the job parameters
     * @return array
     */
    public function getJobParameters(): array
    {
        $result = [];
        if (null !== $this->getDataFileExtension()) {
            $dataFile = dirname($this->getTriggerFile()->getRealPath()) . DIRECTORY_SEPARATOR .
                $this->getTriggerFile()->getBasename("." . $this->getTriggerFile()->getExtension()) . '.' .
                $this->getDataFileExtension();
            if ($this->filesystem->exists($dataFile)) {
                $result = ['filePath' => $dataFile];
            }
        }

        return $result;
    }

    /**
     * Lock trigger file
     */
    public function lock()
    {
        $this->filesystem->touch($this->getLockFilename());
    }

    /**
     * Unlock trigger file
     */
    public function unlock()
    {
        if ($this->isLocked()) {
            $this->filesystem->remove($this->getLockFilename());
        }
    }

    /**
     * Return if file is locked
     * @return bool
     */
    protected function isLocked()
    {
        return $this->filesystem->exists($this->getLockFilename());
    }

    /**
     * Generate lock file name for trigger file
     * @return string
     */
    protected function getLockFilename()
    {
        return $this->directory . DIRECTORY_SEPARATOR .
            $this->originalTriggerFile->getBasename("." . $this->originalTriggerFile->getExtension()) . '.' .
            'lock';
    }

    /**
     * Generate lock file name for trigger file
     * @return string
     */
    protected function getDataFilename()
    {
        return $this->directory . DIRECTORY_SEPARATOR .
            $this->originalTriggerFile->getBasename("." . $this->originalTriggerFile->getExtension()) . '.' .
            $this->dataFileExtension;
    }
}
