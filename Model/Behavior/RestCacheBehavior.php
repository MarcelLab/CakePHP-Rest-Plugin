<?php
/**
 * CacheBehavior 
 * 
 * @uses ModelBehavior
 * @package Fruitstore
 * @version 
 * @copyright Copyright (C) 2013 Marcel Publicis All rights reserved.
 * @author Vivien Ripoche <vivien.ripoche@marcelww.com> 
 * @license 
 */
class RestCacheBehavior extends ModelBehavior {

    /**
     * name 
     * 
     * @var string
     */
    public $name = 'Cache';

    /**
     * afterDelete 
     * 
     * @param mixed $modifiedItem 
     * @return NULL
     */
    public function afterDelete(Model $model) {
        self::_flush();
    }

    /**
     * _flush 
     * 
     * @return NULL
     */
    private static function _flush() {
        Cache::clear(false, 'request');
    }
}
