<?php

namespace App\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $role
 * @property string $created_at
 * @property string $updated_at
 */
class UserRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%users}}';
    }

    public function rules(): array
    {
        return [
            [['username', 'email', 'role'], 'required'],
            [['username'], 'string', 'max' => 100],
            [['email'], 'string', 'max' => 255],
            [['email'], 'email'],
            [['email'], 'unique'],
            [['role'], 'in', 'range' => ['streamer', 'audience']],
        ];
    }
}
