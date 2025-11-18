<?php

namespace SmartDynamicPricing\Services;

/**
 * Simple Service Container for dependency injection
 */
class ServiceContainer
{
    /**
     * Registered services
     */
    protected $services = [];

    /**
     * Singleton instances
     */
    protected $instances = [];

    /**
     * Register a service
     */
    public function bind(string $name, $concrete): self
    {
        $this->services[$name] = $concrete;
        return $this;
    }

    /**
     * Register a singleton service
     */
    public function singleton(string $name, $concrete): self
    {
        $this->services[$name] = $concrete;
        $this->instances[$name] = null;
        return $this;
    }

    /**
     * Get a service from the container
     */
    public function get(string $name)
    {
        // Return existing instance if it's a singleton
        if (isset($this->instances[$name]) && $this->instances[$name] !== null) {
            return $this->instances[$name];
        }

        // Check if service is registered
        if (!isset($this->services[$name])) {
            // throw new \Exception("Service '{$name}' not found in container");
        }

        $concrete = $this->services[$name];

        // If it's a callable, execute it
        if (is_callable($concrete)) {
            $instance = $concrete($this);
        } elseif (is_string($concrete) && class_exists($concrete)) {
            // If it's a class name, instantiate it
            $instance = new $concrete();
        } else {
            // Return the concrete value as-is
            $instance = $concrete;
        }

        // Store instance if it's a singleton
        if (isset($this->instances[$name])) {
            $this->instances[$name] = $instance;
        }

        return $instance;
    }

    /**
     * Check if a service is registered
     */
    public function has(string $name): bool
    {
        return isset($this->services[$name]);
    }

    /**
     * Remove a service from the container
     */
    public function remove(string $name): self
    {
        unset($this->services[$name]);
        unset($this->instances[$name]);
        return $this;
    }

    /**
     * Get all registered services
     */
    public function getServices(): array
    {
        return array_keys($this->services);
    }

    /**
     * Clear all services
     */
    public function clear(): self
    {
        $this->services = [];
        $this->instances = [];
        return $this;
    }
}
