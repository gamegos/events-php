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
     */
    public function getName(): string;

    /**
     * Set the event name.
     */
    public function setName(string $name): void;

    /**
     * Get the event target.
     */
    public function getTarget(): mixed;

    /**
     * Set the event target.
     */
    public function setTarget(mixed $target): void;

    /**
     * Stop further listeners to be triggered.
     */
    public function stopPropagation(): void;

    /**
     * Check if the propagation is stopped or not.
     */
    public function isPropagationStopped(): bool;
}
