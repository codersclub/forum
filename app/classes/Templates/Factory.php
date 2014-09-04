<?php

namespace Templates;

class Factory
{
    /**
     * @param int $id
     * @return BaseTemplate
     */
    public static function create($class)
    {
        if (!is_subclass_of($class, '\Templates\BaseTemplate')) {
            throw new \Exception(sprintf('Wrong class %s for a template', $class));
        }
        return new $class();
    }
}
