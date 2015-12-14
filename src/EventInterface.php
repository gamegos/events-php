<?php
namespace Gamegos\Events;

/**
 * Event Interface
 * @author Safak Ozpinar <safak@gamegos.com>
 */
interface EventInterface
{
    /**
     * Get the event name.
     * @return string
     */
    public function getName();

    /**
     * Set the event name.
     * @param string $name
     */
    public function setName($name);

    /**
     * Get the event target.
     * @return mixed
     */
    public function getTarget();

    /**
     * Set the event target.
     * @param mixed $target
     */
    public function setTarget($target);

    /**
     * Stop further listeners to be triggered.
     */
    public function stopPropagation();

    /**
     * Check if the propagation is stopped or not.
     * @return bool
     */
    public function isPropagationStopped();
}
