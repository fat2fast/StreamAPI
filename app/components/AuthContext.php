<?php

namespace App\components;

use yii\base\Component;

final class AuthContext extends Component
{
    private ?int $userId = null;
    private ?string $role = null;

    public function set(int $userId, string $role): void
    {
        $this->userId = $userId;
        $this->role = $role;
    }

    public function userId(): ?int
    {
        return $this->userId;
    }

    public function role(): ?string
    {
        return $this->role;
    }
}
