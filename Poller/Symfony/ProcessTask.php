<?php

namespace Poller\Symfony;

use Poller\Task\Task;
use Symfony\Component\Process\Process;

class ProcessTask implements Task
{

    /**
     * @var Process
     */
    protected $process;

    /**
     * @var int
     */
    protected $threadId;

    /**
     * @var bool
     */
    protected $stopped;

    /**
     * @var bool
     */
    protected $heartbeat;


    public function __construct(Process $process, callable $heartbeat = null)
    {
        $this->process   = $process;
        $this->stopped   = false;
        $this->heartbeat = $heartbeat;
    }

    /**
     * @return int
     */
    public function getThreadId()
    {
        return $this->threadId;
    }

    /**
     * @return Process
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @inheritdoc
     */
    public function start($threadId)
    {
        $this->process->start();
        $this->stopped = false;
    }

    /**
     * @inheritdoc
     */
    public function heartbeat()
    {
        if ($this->stopped) {
            return false;
        }

        if ($this->process->isRunning()) {
            $this->heartbeatCallback();
            return true;
        } else {
            $this->stopped = true;
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function forceStop()
    {
        if ( ! $this->stopped) {
            $this->process->stop();
        }

        $this->stopped = true;
    }

    protected function heartbeatCallback()
    {
        if (null !== ($hb = $this->heartbeat)) {
            $hb($this);
        }
    }

}
