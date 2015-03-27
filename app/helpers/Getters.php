<?php

trait Getters
{
    public function __get($name)
    {
        $m_name = 'get' . $name;
        if (method_exists($this, $m_name)) {
            return $this->$m_name();
        } elseif (method_exists(get_parent_class($this), '__get')) {
            return parent::__get($name);
        } else {
            throw new Exception('Property ' . $name . ' does not exist');
        }
    }
}
