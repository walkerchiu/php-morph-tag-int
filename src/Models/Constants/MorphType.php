<?php

namespace WalkerChiu\MorphTag\Models\Constants;

/**
 * @license MIT
 * @package WalkerChiu\MorphTag
 *
 * 
 */

class MorphType
{
    /**
     * @param String  $type
     * @return Array
     */
    public static function getCodes(string $type): array
    {
        $items = [];
        $types = self::all();

        switch ($type) {
            case "relation":
                foreach ($types as $key => $value)
                    array_push($items, $key);
                break;
            case "class":
                foreach ($types as $value)
                    array_push($items, $value);
                break;
        }

        return $items;
    }

    /**
     * @return Array
     */
    public static function all(): array
    {
        return [
            'articles'   => 'Article',
            'blogs'      => 'Blog',
            'cards'      => 'Card',
            'catalogs'   => 'Catalog',
            'categories' => 'Category',
            'devices'    => 'Device',
            'groups'     => 'Group',
            'images'     => 'Image',
            'products'   => 'Product',
            'records'    => 'Record',
            'sensors'    => 'Sensor',
            'stocks'     => 'Stock',
            'stores'     => 'Store',
            'sites'      => 'Site',
            'targets'    => 'Target'
        ];
    }
}
