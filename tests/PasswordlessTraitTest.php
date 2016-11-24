<?php

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Foundation\Testing\Concerns\ImpersonatesUsers;
use Mockery as m;
use Orchestra\Testbench\TestCase;
use Whyounes\Passwordless\Exceptions\InvalidTokenException;
use Whyounes\Passwordless\Models\Token;
use Whyounes\Passwordless\Traits\Passwordless;

class PasswordlessTraitTest extends TestCase
{
    use ImpersonatesUsers;

    private $passwordlessStub;

    public function setUp()
    {
        parent::setUp();

        \Config::set('passwordless.expire_in', 15);

        $this->passwordlessStub = new PasswordlessTraitTestStub();
    }


    /**
     * @test
     * @covers Whyounes\Passwordless\Traits\Passwordless::isValidToken()
     */
    public function assert_valid_token()
    {
        $this->assertTrue($this->passwordlessStub->isValidToken('token-1'));
    }


    /**
     * @test
     * @covers Whyounes\Passwordless\Traits\Passwordless::isValidToken()
     */
    public function assert_invalid_token()
    {
        $this->assertFalse($this->passwordlessStub->isValidToken('token-4'));
    }


    /**
     * @test
     * @covers Whyounes\Passwordless\Traits\Passwordless::validateToken()
     */
    public function assert_throws_exception_on_invalid_token()
    {
        $this->expectException(InvalidTokenException::class);
        $this->passwordlessStub->validateToken('token-4');
    }


    /**
     * @test
     * @covers Whyounes\Passwordless\Traits\Passwordless::generateToken()
     */
    public function assert_it_generates_token()
    {
        $token = $this->passwordlessStub->generateToken();

        $this->assertInstanceOf(Token::class, $token);
        $this->assertInternalType('string', $token->token);
    }


    /**
     * @test
     * @runTestsInSeparateProcesses
     * @preserveGlobalState disabled
     * @covers              Whyounes\Passwordless\Traits\Passwordless::generateToken()
     */
    public function assert_it_generates_token_and_save_it()
    {
        $this->markTestSkipped("Could not overload token class");

        m::mock("overload:".Token::class)
            ->shouldReceive('save')
            ->once()
            ->andReturn(true);
        $token = $this->passwordlessStub->generateToken(true);

        $this->assertInstanceOf(Token::class, $token);
        $this->assertInternalType('string', $token->token);
    }

    /**
     * @test
     * @covers Whyounes\Passwordless\Providers\PasswordlessProvider::registerEvents()
     */
    public function assert_deletes_tokens_after_auth()
    {
        // This is called after authentication
        $this->passwordlessStub->tokens()->delete();
        $this->assertEquals(0, $this->passwordlessStub->tokens->count());
    }
}

class PasswordlessTraitTestStub
{

    use Passwordless;

    public $id;

    public $email;

    public $tokens;

    private $tokensMock;


    public function __construct()
    {
        $this->id = 1;
        $this->email = 'younes.rafie@gmail.com';
        $this->setTokens();
        $this->setTokensMock();
    }


    /**
     * Fill user tokens
     */
    private function setTokens()
    {
        $now = Carbon\Carbon::now();
        $this->tokens = collect();

        for ($i = 1; $i < 5; $i++) {
            $this->tokens[] = new Token(
                [
                    'token'      => "token-{$i}",
                    'user_id'    => 1,
                    'created_at' => $now->subMinutes(5 * $i),
                ]
            );
        }
    }


    /**
     * Mock querying tokens from DB
     */
    private function setTokensMock()
    {
        $this->tokensMock = m::mock(stdClass::class);
        $tokens = $this->tokens;

        $this->tokensMock->shouldReceive('where')
            ->andReturnUsing(
                function () {
                    $foundToken = $this->tokens->where('token', func_get_arg(1));

                    return $foundToken;
                }
            );

        $this->tokensMock->shouldReceive('delete')
            ->andReturnUsing(function () {
                $this->tokens = collect();
            });
    }


    /**
     * Return tokens mock instead of Eloquent relation
     *
     * @return mixed
     */
    public function tokens()
    {
        return $this->tokensMock;
    }
}