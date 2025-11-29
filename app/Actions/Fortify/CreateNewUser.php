<?php

namespace App\Actions\Fortify;

use App\Core\LicenceTokenManager;
use App\Models\Licence;
use App\Models\TypeUser;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function __construct(
        private LicenceTokenManager $tokenManager
    ) {}

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        // Récupérer le type_user_id du client
        $clientType = TypeUser::where('slug', 'client')->first();

        // Récupérer la licence par défaut (la première licence active)
        $defaultLicence = Licence::where('is_active', true)
            ->where('status', 'ACTIVE')
            ->orderBy('max_apps')
            ->first();

        // Créer l'utilisateur
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'type_user_id' => $clientType->id,
            'licence_id' => $defaultLicence->id,
        ]);

        // Générer le token de licence
        $token = $this->tokenManager->generateToken($user, $defaultLicence);
        $user->licence_token = $token;
        $user->save();

        return $user;
    }
}
