<?php

namespace App\Services;

use App\Models\User;

abstract class Service
{
    /**
     * @var null|User
     */
    protected ?User $user = null;

    /**
     * @param  User|null  $user
     * @return $this
     */
    public function withUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): User|null
    {
        return $this->user;
    }

    /**
     * Create new service instance
     *
     * @return $this
     */
    public static function getInstance(): static
    {
        return app(static::class);
    }
}
