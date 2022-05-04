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
     * @param $id
     * @param $data
     * @return User|null
     */
    public function update(User $user, $data): ?User
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
