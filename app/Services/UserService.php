<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAllUsers()
    {
        return $this->userRepository->getUsersWithRoles();
    }

    public function getUsersPaginated(int $perPage = 15, array $filters = [])
    {
        return $this->userRepository->paginate($perPage, $filters);
    }

    public function createUser(array $data)
    {
        try {
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $user = $this->userRepository->create($data);

            if (isset($data['roles'])) {
                $user->syncRoles($data['roles']);
            }

            Log::info('User created', ['user_id' => $user->id, 'email' => $user->email]);

            return $user;
        } catch (\Exception $e) {
            Log::error('Error creating user', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateUser(int $id, array $data)
    {
        try {
            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user = $this->userRepository->find($id);
            
            if (!$user) {
                throw new \Exception('User not found');
            }

            $this->userRepository->update($id, $data);

            if (isset($data['roles'])) {
                $user->syncRoles($data['roles']);
            }

            Log::info('User updated', ['user_id' => $id]);

            return $user->fresh();
        } catch (\Exception $e) {
            Log::error('Error updating user', ['user_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function deleteUser(int $id)
    {
        try {
            $result = $this->userRepository->delete($id);
            
            if ($result) {
                Log::info('User deleted', ['user_id' => $id]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Error deleting user', ['user_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findUser(int $id)
    {
        return $this->userRepository->find($id);
    }

    public function searchUsers(string $query)
    {
        return $this->userRepository->search($query, ['name', 'email', 'first_name', 'last_name']);
    }
}

