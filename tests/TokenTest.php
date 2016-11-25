<?php

use Carbon\Carbon;
use Mockery as m;
use Orchestra\Testbench\TestCase;
use Whyounes\Passwordless\Models\Token;

class TokenTest extends TestCase
{

    /**
     * @test
     * @covers Whyounes\Passwordless\Models\Token::isValid()
     */
    public function assert_token_is_valid()
    {
        $token = new Token(
            [
                'token'      => 'token',
                'created_at' => Carbon::now(),
            ]
        );

        $this->assertTrue($token->isValid());
    }


    /**
     * @test
     * @covers Whyounes\Passwordless\Models\Token::isValid()
     * @covers Whyounes\Passwordless\Models\Token::isExpired()
     */
    public function assert_token_is_invalid()
    {
        $token = new Token(
            [
                'token'      => 'token',
                'created_at' => Carbon::now()
                    ->subMinutes(20),
            ]
        );

        $this->assertFalse($token->isValid());
        $this->assertTrue($token->isExpired());
    }
}
