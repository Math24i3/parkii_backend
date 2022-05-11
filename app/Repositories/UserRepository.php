<?php
namespace App\Repositories;

use App\Models\User;

/**
 *
 */
class UserRepository
{
    /**
     * Update a user
     * @param User $user
     * @param array $data
     * @return User|null
     */
    public function update(User $user, array $data): ?User
    {
        try {
            $user->fill($data);
            $user->save();
        } catch (\Exception $exception) {
            return null;
        }

        return $user;
    }
}
