<?php

namespace Esenio\SecurityBundle\Tests\TokenAuthentication\Token;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface as BaseTokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

use Esenio\TestingBundle\UnitTesting\TestCase;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenAuthenticatorInterface;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenEncoderInterface;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenFactoryInterface;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenInterface;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenAuthenticator;
use Esenio\SecurityBundle\Model\UserInterface;


class TokenAuthenticatorTokenEncoderMockToken extends AbstractToken implements BaseTokenInterface
{
    public function getCredentials() {}
}


class TokenAuthenticatorTest extends TestCase
{
    const EXCEPTION_BAD_CREDENDIALS = 'Symfony\Component\Security\Core\Exception\BadCredentialsException';
    const EXCEPTION_USERNAME_NOT_FOUND = 'Symfony\Component\Security\Core\Exception\UsernameNotFoundException';
    const EXCEPTION_AUTHENTICATION = '\Symfony\Component\Security\Core\Exception\AuthenticationException';

    /**
     * @var TokenAuthenticator
     */
    protected $service;

    /**
     * @var TokenEncoderInterface $encoder
     */
    protected $encoder;

    /**
     * @var TokenFactoryInterface
     */
    protected $factory;

    protected function setUp()
    {
        parent::setUp();
        $this->boot();

        $this->service = $this->container->get('esenio_security.token_authentication.token_authenticator');
        $this->encoder = $this->container->get('esenio_security.token_authentication.token_encoder');
        $this->factory = $this->container->get('esenio_security.token_factory');
    }

    public function testInit()
    {
        $this->assertTrue($this->service instanceof TokenAuthenticatorInterface);
    }

    public function testTokenAuthentication()
    {
        $user = $this->getUser();

        $payload = array(
            'username'  => $user->getUsername(),
            'exp'       => time() + 3600
        );

        $bearerToken = $this->encoder->encodeToken($payload);

        $request = new Request();
        $request->headers->add(array(
            'Authorization' => sprintf('Bearer %s', $bearerToken)
        ));

        // create token
        $token = $this->service->createToken($request);
        $this->assertTrue($token instanceof TokenInterface);
        $this->assertFalse($token->getUser() instanceof UserInterface);  // no user attached just yeat
        $this->assertFalse($token->isAuthenticated());
        $this->assertEquals($user->getUsername(), $token->getUsername());

        // authenticate token
        $token = $this->service->authenticateToken($token);
        $this->assertTrue($token instanceof TokenInterface);
        $this->assertTrue($token->getUser() instanceof UserInterface);  // uset MUST be attached at this step
        $this->assertTrue($token->isAuthenticated());
        $this->assertEquals($user->getUsername(), $token->getUsername());
    }

    public function testTokenAuthenticationWithAnonymousToken()
    {
        $user = $this->getUser();

        $payload = array(
            'username'  => $user->getUsername(),
            'exp'       => time() + 3600
        );

        $bearerToken = $this->encoder->encodeToken($payload);

        $request = new Request();
        $request->headers->add(array(
            'Authorization' => sprintf('Bearer %s', $bearerToken)
        ));

        // create token
        $token = $this->factory->createToken();
        $this->assertTrue($token instanceof TokenInterface);
        $this->assertFalse($token->getUser() instanceof UserInterface);  // no user attached just yeat
        $this->assertFalse($token->isAuthenticated());
        $this->assertEquals('', $token->getUsername());

        $this->setExpectedException(self::EXCEPTION_AUTHENTICATION, 'Token user is not found in persistent store.');

        // authenticate token
        $token = $this->service->authenticateToken($token);
    }

    public function testTokenAuthenticationWithExpiredToken()
    {
        $user = $this->getUser();

        $payload = array(
            'username'  => $user->getUsername(),
            'exp'       => time() - 1
        );

        $bearerToken = $this->encoder->encodeToken($payload);

        $request = new Request();
        $request->headers->add(array(
            'Authorization' => sprintf('Bearer %s', $bearerToken)
        ));

        $token = $this->factory->createToken($user, $bearerToken);
        $this->setExpectedException(self::EXCEPTION_AUTHENTICATION, 'Expired Token');

        // authenticate token
        $token = $this->service->authenticateToken($token);
        $this->assertTrue($token instanceof TokenInterface);
        $this->assertTrue($token->getUser() instanceof UserInterface);  // user MUST be attached at this step
        $this->assertTrue($token->isAuthenticated());
        $this->assertEquals($user->getUsername(), $token->getUsername());

    }

    public function testTokenCreationWithExpiredToken()
    {
        $user = $this->getUser();

        $payload = array(
            'username'  => $user->getUsername(),
            'exp'       => time() - 1
        );

        $bearerToken = $this->encoder->encodeToken($payload);

        $request = new Request();
        $request->headers->add(array(
            'Authorization' => sprintf('Bearer %s', $bearerToken)
        ));

        $this->setExpectedException(self::EXCEPTION_AUTHENTICATION, 'Expired Token');

        // create token
        $token = $this->service->createToken($request);
        $this->assertTrue($token instanceof TokenInterface);
        $this->assertFalse($token->getUser() instanceof UserInterface);  // no user attached just yeat
        $this->assertFalse($token->isAuthenticated());
        $this->assertEquals($user->getUsername(), $token->getUsername());
    }

    public function testTokenAuthenticationWithInvalidToken()
    {
        $token = $this->factory->createToken();

        $this->setExpectedException(self::EXCEPTION_AUTHENTICATION, 'Token user is not found in persistent store.');

        $this->service->authenticateToken($token);
    }

    public function testTokenAuthenticationWithEmptyToken()
    {
        $user = $this->getUser();
        $token = $this->factory->createToken($user, '');

        $this->assertEquals('', $token->getCredentials());
        $token = $this->service->authenticateToken($token);
        $this->assertNotNull($token->getCredentials());
    }

    public function testTokenAuthenticationWithEmptyTokenAndAnonUser()
    {
        $token = $this->factory->createToken('root', '');

        $this->assertEquals('', $token->getCredentials());

        $this->setExpectedException(self::EXCEPTION_AUTHENTICATION, 'Token user is not found in persistent store.');
        $this->service->authenticateToken($token);
    }

    public function testCreateTokenWithoutToken()
    {
        $this->setExpectedException(self::EXCEPTION_BAD_CREDENDIALS, 'You must specify credentials');
        $request = new Request();
        $request->headers->add(array(
//            'Authorization' => sprintf('Bearer %s', '')
        ));

        // create token
        $token = $this->service->createToken($request);
        $this->assertTrue($token instanceof TokenInterface);
        $this->assertFalse($token->getUser() instanceof UserInterface);  // no user attached just yeat
        $this->assertFalse($token->isAuthenticated());
    }

    public function testCreateTokenWithEmptyToken()
    {
        $this->setExpectedException(self::EXCEPTION_BAD_CREDENDIALS, 'Cannot parse authorization header..');
        $request = new Request();
        $request->headers->add(array(
            'Authorization' => sprintf('Bearer %s', '')
        ));

        // create token
        $token = $this->service->createToken($request);
        $this->assertTrue($token instanceof TokenInterface);
        $this->assertFalse($token->getUser() instanceof UserInterface);  // no user attached just yeat
        $this->assertFalse($token->isAuthenticated());
    }

    public function testCreateTokenWithoutGarbageToken()
    {
        $this->setExpectedException(self::EXCEPTION_BAD_CREDENDIALS, 'Cannot decode token credentials.');
        $request = new Request();
        $request->headers->add(array(
            'Authorization' => sprintf('Bearer %s', 'some-garbage')
        ));

        // create token
        $token = $this->service->createToken($request);
        $this->assertTrue($token instanceof TokenInterface);
        $this->assertFalse($token->getUser() instanceof UserInterface);  // no user attached just yeat
        $this->assertFalse($token->isAuthenticated());
    }

    public function testSupportsTokenMethod()
    {
        $token = $this->container->get('esenio_security.token_factory')->createToken();
        $this->assertTrue($this->service->supportsToken($token));

        $token = new TokenAuthenticatorTokenEncoderMockToken();
        $this->assertFalse($this->service->supportsToken($token));
    }

    public function testOnAuthenticationFailureHandler()
    {
        $request = new Request();
        $request->headers->add(array(
            'Authorization' => sprintf('Bearer %s', 'some-junk')
        ));
        $request = new Request();

        $message = 'Something wrong';
        $response = $this->service->onAuthenticationFailure($request, new AuthenticationException($message));

        $this->assertTrue($response->headers->has('WWW-Authenticate'));
    }

    public function testOnAuthenticationSuccessHandler()
    {
        $request = new Request();
        $request->headers->add(array(
            'Authorization' => sprintf('Bearer %s', 'some-junk')
        ));
        $request = new Request();

        $token = $this->container->get('esenio_security.token_factory')->createToken();

        $this->assertNull($this->service->onAuthenticationSuccess($request, $token));
    }

    /**
     * @returns UserInterface
     */
    protected function getUser()
    {
        $this->loadFixtures();
        $userManager = $this->container->get('esenio_security.user_manager');
        $users = $userManager->findUsers();
        return $users[0];
    }
}
