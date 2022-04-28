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
     * @return bool
     */
    public function update($id, $data): bool
    {
        $user = User::find($id);

        if (!$user) {
            return false;
        }

        $user->fill($data);

        return $user->save();
    }
}
