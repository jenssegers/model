<?php

trait TraitStub {

    public static $traitIsBooted = false;

    public static function bootTraitStub()
    {
        self::$traitIsBooted = true;
    }

}
