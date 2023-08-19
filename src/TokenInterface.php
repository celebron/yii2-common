<?php

namespace Celebron\common;

interface TokenInterface
{
    /**
     * Выводить результат в виде валидного JSON
     * @return string - json
     */
    public function toJson():string;

    public function getAccessToken():?string;
    public function getExpireIn():int;
    public function getRefreshToken():?string;
    public function getTokenType():?string;

    /**
     * Проверка, что время работы токена не истекло
     * @return bool
     */
    public function getIsExpires (): bool;
    public function getExpiresTime():int;
}