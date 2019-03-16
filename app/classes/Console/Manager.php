<?php

namespace Console;

use Console\Command\BaseCommand;

class Manager
{
    use \SingletonTrait;

    public function commandNameToClassName($commandName)
    {
        return __NAMESPACE__ . '\\Command\\' . str_replace(' ', '', ucwords(str_replace('-', ' ', $commandName)));
    }

    /**
     * @param string $commandName
     * @return BaseCommand
     * @throws \Exception
     */
    public function getCommand($commandName)
    {
        $className = $this->CommandNameToClassName($commandName);
        if (!is_subclass_of($className, 'Console\Command\BaseCommand')) {
            throw new \Exception(sprintf('Command %s does not exist', $commandName));
        }

        /** @var $command \Console\Command\BaseCommand */
        return new $className();
    }

    public function execute($commandName, $args)
    {
        $command = $this->getCommand($commandName);
        list($options, $args) = $this->extractOptions($args);
        $command->setOptions($options);
        return $command->run($args);
    }

    protected function extractOptions($args)
    {
        $options = [];
        while (!empty($args)) {
            $item    = array_shift($args);
            $matches = [];
            if (preg_match('/^--([a-z0-9\-]+)$/', $item, $matches) === 1) {
                $options[$matches[1]] = true;
            } else {
                array_unshift($args, $item);
                break;
            }
        }
        return [
            $options,
            $args
        ];
    }
}
