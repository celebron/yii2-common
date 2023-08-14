<?php

namespace Celebron\common;

interface TokenInterface
{
    public function toJson():string;
    public function getAccessToken():?string;
    public function getExpireIn():int;
    public function getRefreshToken():?string;
    public function getTokenType():?string;
}