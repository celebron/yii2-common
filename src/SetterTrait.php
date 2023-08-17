<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\common;

trait SetterTrait
{
    public function __set($name, $value)
    {
        $propertyName = '_' . $name;
        $methodName = 'get' . $name;
        $refThis = new \ReflectionClass($this);
        if($refThis->hasProperty($propertyName)  && $refThis->hasMethod($methodName)
            && $refThis->getProperty($propertyName)->isProtected()
            && $refThis->getMethod($methodName)->isPublic()
        ) {
            $refThis->getProperty($propertyName)->setValue($this, $value);
            return;
        }

        parent::__set($name, $value);
    }
}