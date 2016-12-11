<?php

namespace Whyounes\Passwordless\Traits;

use Whyounes\Passwordless\Exceptions\InvalidTokenException;
use Whyounes\Passwordless\Models\Token;
use Illuminate\Support\Facades\App;

trait Passwordless
{
    /**
     * Validate attributes.
     *
     * @param  string $token Token
     * @throws InvalidTokenException
     */
    public function validateToken($token)
    {
        if (! $this->isValidToken($token)) {
            throw new InvalidTokenException(trans("passwordless.errors.invalid_token"));
        }
    }

    /**
     * Validate attributes.
     *
     * @param  string $token Token
     * @return bool
     */
    public function isValidToken($token)
    {
        if (! $token) {
            return false;
        }

        /**
         * @var $tokenModel Token
         */
        $tokenModel = $this->tokens()->where('token', $token)->first();

        return $tokenModel ? $tokenModel->isValid() : false;
    }

    /**
     * Generate a token for the current user.
     *
     * @param bool $save Generate token and save it.
     *
     * @return Token
     */
    public function generateToken($save = false)
    {
        $attributes = [
            'token'      => str_random(16),
            'is_used'    => false,
            'user_id'    => $this->id,
            'created_at' => time()
        ];

        $token = App::make(Token::class);
        $token->fill($attributes);
        if ($save) {
            $token->save();
        }

        return $token;
    }

    /**
     * User tokens relation.
     *
     * @return mixed
     */
    public function tokens()
    {
        return $this->hasMany(Token::class, 'user_id', 'id');
    }

    /**
     * Identifier name to be used with token.
     *
     * @return string
     */
    protected function getIdentifierKey()
    {
        return 'email';
    }
}
