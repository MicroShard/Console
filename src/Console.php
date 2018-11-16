<?php

namespace Microshard\Console;

use Microshard\Application\Container;
use Microshard\Application\Exception\SystemException;

class Console
{

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var string[]
     */
    protected $commands = [];

    /**
     * Console constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $identifier
     * @param string $class
     * @return $this
     */
    public function registerCommand(string $identifier, string $class): self
    {
        $this->commands[$identifier] = $class;
        return $this;
    }

    /**
     * @param string $identifier
     * @return $this
     */
    public function unregisterCommand(string $identifier): self
    {
        unset($this->commands[$identifier]);
        return $this;
    }

    /**
     * @param string $identifier
     * @return Command
     */
    protected function getCommand(string $identifier): Command
    {
        $class = $this->commands[$identifier];
        return new $class($this->container);
    }

    /**
     * @param string $identifier
     * @return bool
     */
    protected function hasCommand(string $identifier): bool
    {
        return isset($this->commands[$identifier]);
    }

    /**
     * @param array $args
     * @return array
     */
    protected function parseArgs(array $args): array
    {
        $parsed = [];
        reset($args);
        $currentArg = null;

        while (($next = next($args)) != false){

            if (substr($next, 0, 2) == '--') {
                if ($currentArg) {
                    $parsed[$currentArg] = true;
                }
                $currentArg = substr($next, 2);
            } else if (substr($next, 0, 1) == '-') {
                if ($currentArg) {
                    $parsed[$currentArg] = true;
                }
                $shorts = substr($next, 1);
                for($i=0; $i<strlen($shorts); $i++) {
                    $short = $shorts[$i];
                    $parsed[$short] = true;
                    $currentArg = $short;
                }
            } else if ($currentArg) {
                $parsed[$currentArg] = $next;
                $currentArg = null;
            }
        };

        return $parsed;
    }

    /**
     * @param array $args
     * @throws SystemException
     */
    public function run(array $args)
    {
        if (count($args) > 1) {
            $commandIdentifier = $args[1];
            if (!$this->hasCommand($commandIdentifier)) {
                throw new SystemException('command not found');
            } else {
                $command = $this->getCommand($commandIdentifier);
                $command->run($this->parseArgs($args));
            }
        } else {

            foreach ($this->commands as $identifier => $class) {
                $command = $this->getCommand($identifier);
                echo str_pad($identifier, 25, ' ', STR_PAD_RIGHT) . $command->getDescription() . PHP_EOL;
            }
        }
        echo PHP_EOL;
    }
}