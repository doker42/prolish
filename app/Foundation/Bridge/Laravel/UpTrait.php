<?php


namespace App\Foundation\Bridge\Laravel;


trait UpTrait
{
    private static $upInstance;

    /**
     * @return $this
     */
    public static function up()
    {
        return self::$upInstance ?? self::$upInstance = \app(__CLASS__);
    }
}