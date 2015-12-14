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
     * @var string
     */
    protected $name;

    /**
     * Event target
     * @var mixed
     */
    protected $target;

    /**
     * Flag to stop further listeners to be triggered.
     * @var bool
     */
    protected $propagationStopped = false;

    /**
     * Constructor
     * @param string $name
     * @param mixed $target
     */
    public function __construct($name, $target = null)
    {
        $this->setName($name);
        $this->setTarget($target);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = (string) $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * {@inheritdoc}
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * {@inheritdoc}
     */
    public function stopPropagation()
    {
        $this->propagationStopped = true;
    }

    /**
     * {@inheritdoc}
     */
    public function isPropagationStopped()
    {
        return $this->propagationStopped;
    }
}
