<?php

namespace Plugin;
abstract class SamplePlugin
{
    /**
     * @return string
     */
    public function getUpdateUrl(): string
    {
        return 'https://update.example.com/' . $this->getName() . '.json';
    }

    /**
     * @return string
     */
    abstract public function getName(): string;

    /**
     * @return bool
     */
    public function check(): bool
    {
        if ($this->getAuthor() && $this->getVersion() && $this->getDescription() && $this->getName() && $this->getEventList()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    abstract public function getAuthor(): string;

    /**
     * @return string
     */
    abstract public function getVersion(): string;

    /**
     * @return string
     */
    abstract public function getDescription(): string;

    /**
     * @return array
     */
    abstract public function getEventList(): array;
}