<?php

namespace Celebron\common;

use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

use yii\web\NotFoundHttpException;

/**
 * Стандартные данные при получении Access token из OAuth2 сервера
 * @property-read bool $isExpires
 * @property-read int $expiresTime
 */
class Token extends BaseObject implements \Stringable
{
    /** @var string - Токен доступа */
    public readonly string $accessToken;
    /** @var int - Время существования токина в секундах  */
    public readonly int $expiresIn;
    /** @var string|null - Токен для регенерации токена */
    public readonly ?string $refreshToken;
    /** @var string|null - Тип токена, как правило, Bearer */
    public readonly ?string $tokenType;
    /** @var array - остальные данные */
    public readonly array $data;
    /** @var int - дата генерации в секундах (для вычисления Expire) */
    public int $generateTime = 0;
    /** @var int - задержка, для регенерации токена (чтобы не пропустить) */
    public int $timeout = 3600 * 24;

    public function __construct (array $data, array $config = [])
    {
        \Yii::debug($data, static::class);
        $this->accessToken = ArrayHelper::remove($data, 'access_token');
        $this->expiresIn = ArrayHelper::remove($data, 'expires_in');
        $this->refreshToken = ArrayHelper::remove($data, 'refresh_token');
        $this->tokenType = ArrayHelper::remove($data, 'token_type');
        $this->generateTime = ArrayHelper::remove($data, 'generate_time', time());
        $this->data = $data;
        parent::__construct($config);
    }

    /**
     * Проверка времени на просрок ExpireIn
     * @return bool - Не просрочен/Просрочен
     */
    public function getIsExpires (): bool
    {
        return time() > ($this->getExpiresTime() - $this->timeout);
    }

    /**
     * Вычисление времени действия токина относительно времени сервера
     * @return int
     */
    public function getExpiresTime():int
    {
        return $this->generateTime + $this->expiresIn;
    }

    /**
     * Создания файлы со значениями полученные OAuth2 сервера
     * @param string $file - имя файла (можно использовать @)
     * @return int|false
     */
    public function createFile (string $file):int|false
    {
        $file = \Yii::getAlias($file);
        return file_put_contents($file, $this->__toString(), LOCK_EX);
    }

    /**
     * Открытие файла со значениями
     * @param string $file - имя файла (можно использовать @)
     * @return static
     * @throws NotFoundHttpException
     */
    public static function openFile (string $file): static
    {
        $file = \Yii::getAlias($file);
        if (file_exists($file)) {
            \Yii::info("Open file {$file}.", static::class);
            $data = Json::decode(file_get_contents($file));
            return new static($data);
        }

        throw new NotFoundHttpException("File '{$file}' not exists");
    }

    public function toArray() : array
    {
        $data = [
            'access_token' => $this->accessToken,
            'expires_in' => $this->expiresIn,
            'refresh_token' => $this->refreshToken,
            'token_type' => $this->tokenType,
            'generate_time' => $this->generateTime,
        ];

        return ArrayHelper::merge($data, $this->data);
    }

    public function __toString ()
    {
        return Json::encode($this->toArray());
    }
}