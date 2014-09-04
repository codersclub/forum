<?php

namespace Skins;


use Models\Skins;

class Factory {
    /**
     * Factory method
     * @param array $data
     * @return BaseSkin
     */
    public static function create($data){
        //todo multi skin support
        return new BaseSkin($data);
    }

    /**
     * Небольщая обёртка для поиска дефолтового скина
     * @return BaseSkin
     * @throws \Exception
     */
    public static function createDefaultSkin() {
        $data = Skins::find(['sid' => \Config::get('app.default_skin', 0)]);
        if ($data === FALSE){
            throw new \Exception('No default skin found');
        }
        return static::create($data);
    }
}
