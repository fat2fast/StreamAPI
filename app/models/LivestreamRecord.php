<?php

namespace App\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $streamer_id
 * @property string $title
 * @property string $status
 * @property string $started_at
 * @property string|null $closed_at
 * @property string $created_at
 * @property string $updated_at
 * @property UserRecord $streamer
 */
class LivestreamRecord extends ActiveRecord
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CLOSED = 'closed';

    public static function tableName(): string
    {
        return '{{%livestreams}}';
    }

    public function rules(): array
    {
        return [
            [['streamer_id', 'title', 'status', 'started_at'], 'required'],
            [['streamer_id'], 'integer', 'min' => 1],
            [['title'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_CLOSED]],
            [['started_at', 'closed_at'], 'safe'],
            [['streamer_id'], 'exist', 'targetClass' => UserRecord::class, 'targetAttribute' => ['streamer_id' => 'id']],
        ];
    }

    public function getStreamer(): ActiveQuery
    {
        return $this->hasOne(UserRecord::class, ['id' => 'streamer_id']);
    }
}
