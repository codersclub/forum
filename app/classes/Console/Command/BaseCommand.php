<?php
/**
 * @file
 */

namespace Console\Command;


abstract class BaseCommand {
    protected $options = [];

    abstract public function run($args);


    public function help(){
        return '';
    }

    public function execute($args){
        $this->run($args);
    }

    public function setOptions($options){
        $this->options = $options;
    }
}
