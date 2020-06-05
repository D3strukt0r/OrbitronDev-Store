<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="app_cronjob")
 */
class CronJob
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": true})
     */
    protected $enabled = true;

    /**
     * @var int
     * @ORM\Column(type="smallint", options={"default": 5})
     */
    protected $priority = 5;

    /**
     * @var string
     * @ORM\Column(type="string", unique=true, length=191)
     */
    protected $script_file;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    protected $last_exec;

    /**
     * @var int
     * @ORM\Column(type="bigint", options={"default": 3600})
     */
    protected $exec_every = 3600;

    /**
     * @return int The ID
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return bool Whether the job is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled Whether the job is enabled
     *
     * @return $this
     */
    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return int The priority
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority The priority
     *
     * @return $this
     */
    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return string The script to run
     */
    public function getScriptFile(): string
    {
        return $this->script_file;
    }

    /**
     * @param string $script_file The script to run
     *
     * @return $this
     */
    public function setScriptFile(string $script_file): self
    {
        $this->script_file = $script_file;

        return $this;
    }

    /**
     * @return DateTime Last execution time
     */
    public function getLastExec(): DateTime
    {
        return $this->last_exec;
    }

    /**
     * @param DateTime $last_exec Last execution time
     *
     * @return $this
     */
    public function setLastExec(DateTime $last_exec): self
    {
        $this->last_exec = $last_exec;

        return $this;
    }

    /**
     * @return int Exec every "how many seconds"
     */
    public function getExecEvery(): int
    {
        return $this->exec_every;
    }

    /**
     * @param int $exec_every Exec every "how many seconds"
     *
     * @return $this
     */
    public function setExecEvery(int $exec_every): self
    {
        $this->exec_every = $exec_every;

        return $this;
    }
}
