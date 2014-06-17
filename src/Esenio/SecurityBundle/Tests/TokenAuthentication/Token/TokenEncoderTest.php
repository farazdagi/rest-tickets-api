<?php

namespace Esenio\SecurityBundle\Tests\TokenAuthentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface as BaseTokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Faker;

use Esenio\TestingBundle\UnitTesting\TestCase;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenEncoderInterface;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenDecoderInterface;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenEncoder;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenInterface;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\Token;


class MockToken extends AbstractToken implements BaseTokenInterface
{
    public function getCredentials() {}
}


class TokenEncoderTest extends TestCase
{
    /**
     * @var TokenEncoderInterface
     */
    protected $encoder;

    /**
     * @var TokenDecoderInterface
     */
    protected $decoder;

    protected function setUp()
    {
        parent::setUp();
        $this->boot();

        $this->decoder = $this->encoder = new TokenEncoder(
            $this->container->getParameter('esenio_security.secret'),
            $this->container->getParameter('esenio_security.jwt.algo'));
    }

    public function testInit()
    {
        $this->assertTrue($this->encoder instanceof TokenEncoderInterface);
        $this->assertTrue($this->decoder instanceof TokenDecoderInterface);
    }

    public function testEncodeToken()
    {
        $faker = Faker\Factory::create();

        $payload = array(
            'username' => $faker->userName,
            'exp' => time() + 3600
        );

        $encoded = $this->encoder->encodeToken($payload);
        $this->assertEquals($payload, $this->decoder->decodeToken($encoded));
    }

    public function testEncodeTokenInvalidRawInput()
    {
        $this->setExpectedException('InvalidArgumentException', 'Token payload requires fields: "username", "exp"');
         $this->encoder->encodeToken(array());
    }

    public function testIsTokenValidMethodWithValidToken()
    {
        $faker = Faker\Factory::create();

        $payload = array(
            'username' => $faker->userName,
            'exp' => time() + 3600
        );

        $encoded = $this->encoder->encodeToken($payload);
        $this->assertEquals($payload, $this->decoder->decodeToken($encoded));
        $this->assertTrue($this->encoder->isTokenValid($encoded));
    }

    public function testIsTokenValidMethodWithInvalidToken()
    {
        $faker = Faker\Factory::create();

        $this->assertFalse($this->encoder->isTokenValid($faker->uuid));
    }

    public function testDecodeTokenWithValidToken()
    {
        $faker = Faker\Factory::create();

        $payload = array(
            'username' => $faker->userName,
            'exp' => time() + 3600
        );

        $encoded = $this->encoder->encodeToken($payload);
        $this->assertEquals($payload, $this->decoder->decodeToken($encoded));
    }

    public function testDecodeTokenWithInvalidToken()
    {
        $faker = Faker\Factory::create();
        $this->assertFalse($this->decoder->decodeToken($faker->uuid));
    }

    public function testSupportsTokenMethod()
    {
        $payload = array(
            'username' => Token::USER_ANONYMOUS,
            'exp' => time() + 3600
        );

        /** @var TokenInterface $token */
        $token = new Token($this->decoder, Token::USER_ANONYMOUS, $this->encoder->encodeToken($payload));

        $this->assertTrue($this->encoder->supportsToken($token));
        $this->assertFalse($this->encoder->supportsToken(new MockToken()));
    }
}
 