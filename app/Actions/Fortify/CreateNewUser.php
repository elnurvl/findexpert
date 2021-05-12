<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Laudis\Neo4j\Client;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    private $neo4jClient;

    public function __construct(Client $neo4jClient)
    {
        $this->neo4jClient = $neo4jClient;
    }


    /**
     * Validate and create a newly registered user.
     *
     * @param  array  $input
     * @return User
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'website' => ['url'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['required', 'accepted'] : '',
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'website' => $input['website'] ?? null
        ]);

        // TODO: A temporary workaround. Use the library method instead of this request.
        Http::withBasicAuth(env('NEO4J_USER'), env('NEO4J_PASSWORD'))->post(env('NEO4J_PROTOCOL').'://'.env('NEO4J_HOST').'/db/data/transaction/commit', [
            'statements' => [
                [
                    'statement' => <<<'CYPHER'
MERGE (:User{id: $userId})
CYPHER,
                    'parameters' => [
                        'userId' => $user->id
                    ]
                ]
            ]
        ]);

        // TODO: Use this instead of the above request.
        // TODO: Neo4j Client does not make changes on the database. Possibly a library bug.
//        $this->neo4jClient->run(<<<'CYPHER'
//MERGE (:User{id: $userId})
//CYPHER,
//            ['userId' => $user->id]
//        );

        return $user;
    }
}
