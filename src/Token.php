<?php

namespace Celebron\common;

use yii\base\BaseObject;
use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;


/**
 *
 * @property-read int $expireIn
 * @property-read null|string $tokenType
 * @property-read null|string $accessToken
 * @property-read int $generateTime
 * @property-read bool $isExpires
 * @property-read int $expiresTime
 * @property-read null|string $refreshToken
 */
class Token extends BaseObject implements \Stringable, TokenInterface
{
    public const PROPERTY_ACCESS_TOKEN = 'accessToken';
    public const PROPERTY_EXPIRE_IN = 'expiresIn';
    public const PROPERTY_REFRESH_TOKEN = 'refreshToken';
    public const PROPERTY_TOKEN_TYPE = 'tokenType';
    public const PROPERTY_GENERATE_TIME = 'generateTime';
    public int $timeout = 3600 * 24;
    public readonly array $compare;
    public function __construct (
        public array $data,
        ?array $compare = null,
        array $config = []
    ) {
        parent::__construct($config);
        $this->compare = $compare ?? $this->defaultCompare();
    }

    protected function defaultCompare():array
    {
        return [
            self::PROPERTY_ACCESS_TOKEN => 'access_token',
            self::PROPERTY_EXPIRE_IN => 'expires_in',
            self::PROPERTY_REFRESH_TOKEN => 'refresh_token',
            self::PROPERTY_TOKEN_TYPE => 'token_type',
            self::PROPERTY_GENERATE_TIME => 'generate_time',
        ];
    }

    /**
     * @param string $key - ключ из $compare
     * @param mixed $default - значение по-умолчанию
     * @return mixed
     * @throws \Exception
     */
    protected function property(string $key, mixed $default) : mixed
    {
        $field = $this->compare[$key];
        return ArrayHelper::getValue($this->data, $field, $default);
    }

    public function getAccessToken():?string
    {
        return $this->property(self::PROPERTY_ACCESS_TOKEN, null);
    }

    public function getExpireIn():int
    {
        return $this->property(self::PROPERTY_EXPIRE_IN, 0);
    }

    public function getRefreshToken():?string
    {
        return $this->property(self::PROPERTY_REFRESH_TOKEN, null);
    }

    public function getTokenType():?string
    {
        return $this->property(self::PROPERTY_TOKEN_TYPE, null);
    }

    public function getGenerateTime():int
    {
        return $this->property(self::PROPERTY_GENERATE_TIME, time());
    }

    /**
     * @return bool
     */
    public function getIsExpires (): bool
    {
        return time() > ($this->getExpiresTime() - $this->timeout);
    }

    /**
     * Вычисление времени действия токена относительно времени сервера
     * @return int
     */
    public function getExpiresTime():int
    {
        return $this->getGenerateTime() + $this->getExpireIn();
    }

    /**
     * Создания файлы со значениями полученные OAuth2 сервера
     * @param string $file - имя файла (можно использовать @)
     * @return int|false
     */
    public function createFile (string $file):int|false
    {
        $file = \Yii::getAlias($file);
        return file_put_contents($file, $this->toJson(), LOCK_EX);
    }

    /**
     * Открытие файла со значениями
     * @param string $file - имя файла (можно использовать @)
     * @return static
     */
    public static function openFile (string $file): static
    {
        $file = \Yii::getAlias($file);
        if (file_exists($file)) {
            \Yii::info("Open file {$file}.", static::class);
            $data = Json::decode(file_get_contents($file));
            return new static($data);
        }

        throw new InvalidArgumentException("File '{$file}' not exists");
    }

    /**
     * Получение данных в виде JSON
     * @return string
     */
    public function toJson() : string
    {
        return Json::encode($this->data);
    }

    public function __toString ()
    {
        return $this->toJson();
    }
}