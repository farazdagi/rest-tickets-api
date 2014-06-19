<?php

namespace Esenio\SecurityBundle\Tests\TokenAuthentication\Token;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

use Faker;
use Esenio\TestingBundle\UnitTesting\TestCase;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenIssuerInterface;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenInterface;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenEncoderInterface;
use Esenio\SecurityBundle\Model\UserManagerInterface;
use Esenio\SecurityBundle\Model\UserInterface;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenFactoryInterface;


class TokenIssuerTest extends TestCase
{
    const EXCEPTION_BAD_CREDENDIALS = 'Symfony\Component\Security\Core\Exception\BadCredentialsException';
    const EXCEPTION_USERNAME_NOT_FOUND = 'Symfony\Component\Security\Core\Exception\UsernameNotFoundException';

    /**
     * @var TokenIssuerInterface
     */
    protected $service;

    /**
     * @var TokenEncoderInterface
     */
    protected $encoder;

    protected function setUp()
    {
        parent::setUp();
        $this->boot();

        $this->service = $this->container->get('esenio_security.token_issuer');
        $this->encoder = $this->container->get('esenio_security.token_authentication.token_encoder');
    }

    public function testInit()
    {
        $this->assertTrue($this->service instanceof TokenIssuerInterface);
    }

    public function testIssueTokenWithValidAuthorizationHeader()
    {
        $faker = Faker\Factory::create();

        /** @var UserManagerInterface $userManager */
        $userManager = $this->container->get('esenio_security.user_manager');
        $users = $userManager->findUsers();

        /** @var UserInterface $user */
        $user = $users[0];

        $payload = array(
            'username'  => $user->getUsername(),
            'exp'       => time() + 3600
        );
        $encodedToken = $this->encoder->encodeToken($payload);

        $request = new Request();
        $request->headers->add(array(
            'Authorization' => sprintf('Bearer %s realm="some-realm" foo=bar', $encodedToken)
        ));

        /** @var TokenInterface $token */
        $token = $this->service->issueToken($request);

        $this->assertTrue($token instanceof TokenInterface);
        $this->assertEquals($encodedToken, $token->getCredentials());
        $this->assertEquals($user->getUsername(), $token->getUser());
        $this->assertEquals($user->getUsername(), $token->getUsername());
        $this->assertFalse($token->isAuthenticated());

        // try verifying
        try {
            $this->service->verifyToken($token);
            $this->fail('Exception expected');
        } catch (BadCredentialsException $e) {
            $this->assertEquals('No user associated with token.', $e->getMessage());
        }

        // sign and re-check
        $token = $this->service->signToken($token);

        try {
            $this->service->verifyToken($token);
        } catch (BadCredentialsException $e) {
            $this->fail(sprintf('Unexpected exception: %s', $e->getMessage()));
        }
        $this->assertTrue($token->isAuthenticated());
    }

    public function testIssueTokenWithValidAuthorizationHeaderButMissingUsernameInPayload()
    {
        $this->setExpectedException(self::EXCEPTION_BAD_CREDENDIALS, 'Cannot extract username from token payload.');
        /** @var UserManagerInterface $userManager */
        $userManager = $this->container->get('esenio_security.user_manager');
        $users = $userManager->findUsers();

        /** @var UserInterface $user */
        $user = $users[0];

        $payload = array(
            'exp'       => time() + 3600
        );
        $encodedToken =  \JWT::encode($payload,
            $this->container->getParameter('esenio_security.secret'),
            $this->container->getParameter('esenio_security.jwt.algo'));

        $request = new Request();
        $request->headers->add(array(
            'Authorization' => sprintf('Bearer %s realm="some-realm" foo=bar', $encodedToken)
        ));

        /** @var TokenInterface $token */
        $token = $this->service->issueToken($request);
    }

    public function testIssueTokenWithValidAuthorizationHeaderButInvalidPayload()
    {
        $this->setExpectedException(self::EXCEPTION_BAD_CREDENDIALS, 'Cannot decode token credentials.');

        $encodedToken = 'some-junk';

        $request = new Request();
        $request->headers->add(array(
            'Authorization' => sprintf('Bearer %s realm="some-realm" foo=bar', $encodedToken)
        ));

        /** @var TokenInterface $token */
        $token = $this->service->issueToken($request);
    }

    public function testSignTokenWithInvalidUsername()
    {
        $this->setExpectedException(self::EXCEPTION_BAD_CREDENDIALS, 'Token user is not found in persistent store.');
        /** @var UserManagerInterface $userManager */
        $userManager = $this->container->get('esenio_security.user_manager');
        $users = $userManager->findUsers();

        /** @var UserInterface $user */
        $user = $users[0];

        $payload = array(
            'username'  => $user->getUsername(),
            'exp'       => time() + 3600
        );
        $encodedToken = $this->encoder->encodeToken($payload);

        $request = new Request();
        $request->headers->add(array(
            'Authorization' => sprintf('Bearer %s realm="some-realm" foo=bar', $encodedToken)
        ));

        /** @var TokenInterface $token */
        $token = $this->service->issueToken($request);

        $this->assertTrue($token instanceof TokenInterface);
        $this->assertEquals($encodedToken, $token->getCredentials());
        $this->assertEquals($user->getUsername(), $token->getUser());
        $this->assertEquals($user->getUsername(), $token->getUsername());
        $this->assertFalse($token->isAuthenticated());

        $userManager->deleteUser($user); // remove user!!
        $this->service->signToken($token);

    }

    public function testIssueTokenWithInvalidAuthorizationHeader()
    {
        $this->setExpectedException('InvalidArgumentException', 'Cannot parse authorization header.');

        $request = new Request();
        $request->headers->add(array(
            'Authorization' => 'Rearer %s realm="some-realm" foo=bar'
        ));

        /** @var TokenInterface $token */
        $token = $this->service->issueToken($request);
    }

    public function testIssueTokenWithValidUsernameAndPassword()
    {
        $faker = Faker\Factory::create();

        /** @var UserManagerInterface $userManager */
        $userManager = $this->container->get('esenio_security.user_manager');
        $users = $userManager->findUsers();

        /** @var UserInterface $user */
        $user = $users[0];

        // change user password to one that is known to us
        $password = $faker->uuid;
        $user->setPlainPassword($password);
        $userManager
            ->encodePassword($user)
            ->saveUser($user);

        // setup token
        $payload = array(
            'username'  => $user->getUsername(),
            'exp'       => time() + 3600
        );
        $encodedToken = $this->encoder->encodeToken($payload);

        // setup request
        $request = new Request();
        $request->request->add(array(
            'username' => $user->getUsername(),
            'password' => $password // plain text password known to us
        ));

        /** @var TokenInterface $token */
        $token = $this->service->issueToken($request);

        $this->assertTrue($token instanceof TokenInterface);
        $this->assertEquals('', $token->getCredentials()); // NO TOKEN MUST BE SET YET (it will be assigned after signing)
        $this->assertNotNull($token->getUser());
        $this->assertTrue($user->isEqualTo($token->getUser()));
        $this->assertEquals($user->getUsername(), $token->getUsername());
        $this->assertFalse($token->isAuthenticated());

        // try verifying
        try {
            $this->service->verifyToken($token);
            $this->fail('Exception expected');
        } catch (BadCredentialsException $e) {
            // while user is set, token is not yet signed
            $this->assertEquals('No credentials found in token.', $e->getMessage());
        }

        // sign and re-check
        $token = $this->service->signToken($token);

        try {
            $this->service->verifyToken($token);
        } catch (BadCredentialsException $e) {
            $this->fail(sprintf('Unexpected exception: %s', $e->getMessage()));
        }
        $this->assertTrue($token->isAuthenticated());
    }

    public function testIssueTokenWithInvalidUsernameAndPassword()
    {
        $this->setExpectedException(self::EXCEPTION_BAD_CREDENDIALS, 'Authentication failed..');

        $faker = Faker\Factory::create();

        /** @var UserManagerInterface $userManager */
        $userManager = $this->container->get('esenio_security.user_manager');
        $users = $userManager->findUsers();

        /** @var UserInterface $user */
        $user = $users[0];

        // change user password to one that is known to us
        $password = $faker->uuid;
        $user->setPlainPassword($password);
        $userManager
            ->encodePassword($user)
            ->saveUser($user);

        // setup token
        $payload = array(
            'username'  => $user->getUsername(),
            'exp'       => time() + 3600
        );
        $encodedToken = $this->encoder->encodeToken($payload);

        // setup request
        $request = new Request();
        $request->request->add(array(
            'username' => $user->getUsername(),
            'password' => $password . 'some-junk-to-make-it-invalid'
        ));

        /** @var TokenInterface $token */
        $token = $this->service->issueToken($request);
    }

    public function testIssueTokenWithNonExistentUser()
    {
        $faker = Faker\Factory::create();
        $username = $faker->uuid;

        $this->setExpectedException(self::EXCEPTION_USERNAME_NOT_FOUND, sprintf(
            "Username '%s' does not exist.", $username
        ));

        // setup request
        $request = new Request();
        $request->request->add(array(
            'username' => $username,
            'password' => 'not-important-it-will-not-be-checked'
        ));

        /** @var TokenInterface $token */
        $this->service->issueToken($request);
    }

    public function testIssueTokenWithoutUsername()
    {
        $this->setExpectedException(self::EXCEPTION_BAD_CREDENDIALS, 'You must specify credentials.');

        // setup request
        $request = new Request();
        $request->request->add(array(
            'password' => 'top-secret'
        ));

        /** @var TokenInterface $token */
        $this->service->issueToken($request);
    }

    public function testIssueTokenWithoutPassword()
    {
        $faker = Faker\Factory::create();
        $username = $faker->uuid;

        $this->setExpectedException(self::EXCEPTION_BAD_CREDENDIALS, 'You must specify credentials.');

        // setup request
        $request = new Request();
        $request->request->add(array(
            'username' => $username,
        ));

        /** @var TokenInterface $token */
        $this->service->issueToken($request);
    }

    public function testVerifyMethod()
    {
        $faker = Faker\Factory::create();

        /** @var TokenFactoryInterface $factory */
        $factory = $this->container->get('esenio_security.token_factory');

        /** @var UserManagerInterface $userManager */
        $userManager = $this->container->get('esenio_security.user_manager');

        $user = $userManager->createUser();
        $user->setUsername($faker->userName);
        $user->setEmail($faker->email);
        $user->setPlainPassword($faker->uuid);

        $userManager
            ->encodePassword($user)
            ->saveUser($user);

        /** @var TokenEncoderInterface $encoder */
        $encoder = $this->container->get('esenio_security.token_authentication.token_encoder');

        // CASE 1: Valid token
        $payload = array(
            'username' => $user->getUsername(),
            'exp' => time() + 3600
        );
        $token = $factory->createToken($user, $encoder->encodeToken($payload));
        try {
            $this->service->verifyToken($token);
        } catch (BadCredentialsException $e) {
            $this->fail(sprintf('Unexpected exception: %s', $e->getMessage()));
        }

        // CASE 2: Token w/o credentials
        $token = $factory->createToken($user);
        try {
            $this->service->verifyToken($token);
            $this->fail('Exception expected');
        } catch (BadCredentialsException $e) {
            $this->assertEquals('No credentials found in token.', $e->getMessage());
        }

        // CASE 3: Credentials supplied, but are invalid
        $token = $factory->createToken($user, 'some-junk-as-credentials');
        try {
            $this->service->verifyToken($token);
            $this->fail('Exception expected');
        } catch (BadCredentialsException $e) {
            $this->assertEquals('Cannot decode token credentials.', $e->getMessage());
        }

        // CASE 4: Do not set username in payload
        $payload = array(
            'exp' => time() + 3600
        );
        $token = $factory->createToken($user, \JWT::encode( $payload,
            $this->container->getParameter('esenio_security.secret'),
            $this->container->getParameter('esenio_security.jwt.algo')));
        try {
            $this->service->verifyToken($token);
            $this->fail('Exception expected');
        } catch (BadCredentialsException $e) {
            $this->assertEquals('Cannot extract username from token payload.', $e->getMessage());
        }

        // CASE 5: Do not set exp in payload
        $payload = array(
            'username' => $user->getUsername(),
        );
        $token = $factory->createToken($user, \JWT::encode( $payload,
            $this->container->getParameter('esenio_security.secret'),
            $this->container->getParameter('esenio_security.jwt.algo')));
        try {
            $this->service->verifyToken($token);
            $this->fail('Exception expected');
        } catch (BadCredentialsException $e) {
            $this->assertEquals('Cannot extract expiration time from token payload.', $e->getMessage());
        }

        // CASE 6: Forge username in payload
        $payload = array(
            'username' => 'root', // FORGED!
            'exp' => time() + 3600
        );
        $token = $factory->createToken($user, $encoder->encodeToken($payload));
        try {
            $this->service->verifyToken($token);
            $this->fail('Token payload invalid exception is expected');
        } catch (BadCredentialsException $e) {
            $this->assertEquals('Token payload is invalid.', $e->getMessage());
        }

        // CASE 7: Forge username in user object
        $payload = array(
            'username' => $user->getUsername(),
            'exp' => time() + 3600
        );
        $user->setUsername('root'); // FORGED
        $token = $factory->createToken($user, $encoder->encodeToken($payload));
        try {
            $this->service->verifyToken($token);
            $this->fail('Token payload invalid exception is expected');
        } catch (BadCredentialsException $e) {
            $this->assertEquals('Token payload is invalid.', $e->getMessage());
        }

        // CASE 8: Expired token
        $payload = array(
            'username' => $user->getUsername(),
            'exp' => time() - 1
        );
        $token = $factory->createToken($user, $encoder->encodeToken($payload));
        try {
            $this->service->verifyToken($token);
            $this->fail('Token expired exception is expected');
        } catch (BadCredentialsException $e) {
            $this->assertEquals('Expired Token', $e->getMessage());
        }
    }
}
 