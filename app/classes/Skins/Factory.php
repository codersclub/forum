<?php

namespace Skins;

class Factory
{
    /**
     * Factory method
     * @param int skin id
     * @throws \Exception
     * @return BaseSkinManager
     */
    public static function create($id)
    {
        $skin = static::searchSkin($id);
        if ($skin === null) {
            throw new \Exception(sprintf('Skin with id %d not found', $id));
        }
        return new DatasetSkinManager($skin);
    }

    /**
     * Небольщая обёртка для поиска дефолтового скина
     * @return BaseSkinManager
     */
    public static function createDefaultSkin()
    {
        return static::create(\Config::get('app.skins.default', 0));
    }

    /**
     * Ищет данные скина
     * @param mixed $id Skin id
     * @return mixed массив данных скина
     */
    public static function searchSkin($id)
    {
        static $map = [];
        if (!isset($map[$id])) {
            $data = array_filter(
                self::getAllSkinsData(),
                function ($item) use ($id) {
                    return $item['id'] === $id;
                }
            );
            \Logs::debug('Debug', 'skins found for id ' . $id, ['skins' => $data]);
            $map[$id] = array_shift($data);//yes, null too.
        }
        return $map[$id];
    }

    /**
     * Возвращает данные всех скинов
     * @return mixed
     */
    public static function getAllSkinsData()
    {
        return DatasetSkinManager::getAllSkinsData();
    }
}
