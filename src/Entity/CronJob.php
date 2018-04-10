<?php

namespace App\Entity;

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
     * @ORM\Column(type="string", unique=true)
     */
    protected $script_file;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $last_exec;

    /**
     * @var int
     * @ORM\Column(type="bigint", options={"default": 3600})
     */
    protected $exec_every = 3600;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     *
     * @return $this
     */
    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     *
     * @return $this
     */
    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return string
     */
    public function getScriptFile(): string
    {
        return $this->script_file;
    }

    /**
     * @param string $script_file
     *
     * @return $this
     */
    public function setScriptFile(string $script_file): self
    {
        $this->script_file = $script_file;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastExec(): \DateTime
    {
        return $this->last_exec;
    }

    /**
     * @param \DateTime $last_exec
     *
     * @return $this
     */
    public function setLastExec(\DateTime $last_exec): self
    {
        $this->last_exec = $last_exec;

        return $this;
    }

    /**
     * @return int
     */
    public function getExecEvery(): int
    {
        return $this->exec_every;
    }

    /**
     * @param int $exec_every
     *
     * @return $this
     */
    public function setExecEvery(int $exec_every): self
    {
        $this->exec_every = $exec_every;

        return $this;
    }
}
