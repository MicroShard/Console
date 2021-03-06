<?php

namespace Microshard\Console;


use Microshard\Application\Container;
use Microshard\Console\Command\Parameter;

abstract class Command
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Parameter[]
     */
    protected $parameters = [];

    /**
     * Command constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return '';
    }

    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * @return $this
     */
    protected function prepare(): self
    {
        return $this;
    }

    /**
     * @param Parameter $parameter
     * @return $this
     */
    protected function addParameter(Parameter $parameter): self
    {
        $this->parameters[$parameter->getLong()] = $parameter;
        return $this;
    }

    /**
     * @return Parameter[]
     */
    protected function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param string $long
     * @return Parameter
     */
    protected function getParameter(string $long): Parameter
    {
        if (!isset($this->parameters[$long])){
            //error
        }
        return $this->parameters[$long];
    }

    /**
     * @param array $parameters
     */
    public function run(array $parameters)
    {
        foreach ($this->getParameters() as $name => $param) {
            $value = null;
            if (isset($parameters[$name])) {
                $value = $parameters[$name];
            } else if ($param->getShort() && isset($parameters[$param->getShort()])) {
                $value = $parameters[$name];
            }

            if (!$value && !$param->isOptional()) {
                //error
            }

            if (!$param->validate($value)) {
                //error
            }
            $param->setValue($value);
        }

        $this->execute();
    }

    /**
     * @param string $message
     * @return $this
     */
    protected function echoLine(string $message): self
    {
        echo $message . PHP_EOL;
        return $this;
    }

    protected abstract function execute();
}