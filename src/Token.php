<?php

namespace Celebron\common;

use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

use yii\web\NotFoundHttpException;

/**
 *
 * @property-read bool $isExpires
 * @property-read int $expiresTime
 */
class Token extends BaseObject
{
    public readonly string $accessToken;
    public readonly int $expiresIn;
    public readonly ?string $refreshToken;
    public readonly ?string $tokenType;

    public readonly array $data;
    public int $generateTime = 0;

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


    public function getIsExpires (): bool
    {
        return time() > ($this->getExpiresTime() - $this->timeout);
    }

    public function getExpiresTime():int
    {
        return $this->generateTime + $this->expiresIn;
    }

    public function create (string $file):int|false
    {
        $file = \Yii::getAlias($file);
        $json = Json::encode($this->toArray());
        return file_put_contents($file, $json, LOCK_EX);
    }

    /**
     * @throws NotFoundHttpException
     */
    public static function openFile (string $file, array $params = []): static
    {
        $file = \Yii::getAlias($file);
        if (file_exists($file)) {
            \Yii::info("Open file {$file}.", static::class);
            $data = Json::decode(file_get_contents($file));
            return new static($data, $params);
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
}