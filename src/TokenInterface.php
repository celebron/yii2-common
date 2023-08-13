<?php

namespace Celebron\common;

interface TokenInterface
{
    public function toArray():array;
    public function getJson():string;
}