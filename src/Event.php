<?php
namespace Gamegos\Events;

/**
 * Generic Event
 * @author Safak Ozpinar <safak@gamegos.com>
 */
class Event implements EventInterface
{
    /**
     * Event name
     */
    protected string $name;

    /**
     * Event target
     */
    protected mixed $target;

    /**
     * Flag to stop further listeners to be triggered.
     */
    protected bool $propagationStopped = false;

    /**
     * Constructor
     */
    public function __construct(string $name, mixed $target = null)
    {
        $this->setName($name);
        $this->setTarget($target);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getTarget(): mixed
    {
        return $this->target;
    }

    /**
     * {@inheritdoc}
     */
    public function setTarget(mixed $target): void
    {
        $this->target = $target;
    }

    /**
     * {@inheritdoc}
     */
    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    /**
     * {@inheritdoc}
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }
}
