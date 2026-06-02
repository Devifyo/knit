<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Models\User;
use App\Modules\Admin\Services\WorkspaceProvisioner;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function __construct(protected WorkspaceProvisioner $provisioner) {}

    /**
     * Validate registration and provision a new workspace + owner user.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'workspace' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            // Email is unique per workspace (DB composite unique). Each signup
            // creates a brand-new workspace, so no global uniqueness check here.
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => $this->passwordRules(),
        ])->validate();

        $tenant = $this->provisioner->provision([
            'name' => $input['workspace'],
            'owner_name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);

        /** @var User $owner */
        $owner = $tenant->getRelation('owner');

        return $owner;
    }
}
