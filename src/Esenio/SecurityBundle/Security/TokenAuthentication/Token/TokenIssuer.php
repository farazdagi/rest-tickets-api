<?php

namespace Esenio\SecurityBundle\Security\TokenAuthentication\Token;

use Esenio\SecurityBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

use Esenio\SecurityBundle\Security\TokenAuthentication\Provider\UserProviderInterface;


class TokenIssuer implements TokenIssuerInterface
{
    /**
     * @var TokenFactoryInterface
     */
    protected $factory;

    /**
     * @var UserProviderInterface
     */
    protected $userProvider;

    /**
     * @var TokenEncoderInterface
     */
    protected $encoder;

    /**
     * @var TokenDecoderInterface
     */
    protected $decoder;

    /**
     * @var EncoderFactoryInterface
     */
    protected $encoderFactory;

    /**
     * @param TokenFactoryInterface $factory Factory producing TokenInterface objects
     * @param UserProviderInterface $userProvider User provider, to get users for username/token
     * @param TokenEncoderInterface $encoder Encoder capable to sign/encode raw token
     * @param TokenDecoderInterface $decoder Decoder capable to decode encoded token
     * @param EncoderFactoryInterface $encoderFactory Encoder factory interface (for password encoding)
     */
    public function __construct(TokenFactoryInterface $factory, UserProviderInterface $userProvider,
                                TokenEncoderInterface $encoder, TokenDecoderInterface $decoder,
                                EncoderFactoryInterface $encoderFactory)
    {
        $this->factory = $factory;
        $this->userProvider = $userProvider;
        $this->encoder = $encoder;
        $this->decoder = $decoder;
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * Initializes token using incoming Request values.
     *
     * If "Authorization: Bearer {{ token }}" header is passed, then token is initialized to anonymous user
     * with {{ token }} credentials i.e. it will FAIL verifyToken() test. To turn it into valid token
     * use signToken() on it.
     *
     * Otherwise, following steps are taken:
     *
     * 1. POST[username] and POST[password] parameters are extracted (from Request) into local variables.
     *    BadCredentialsException is thrown if values are not found.
     * 2. Some service (implementing UserProviderInterface) is used to get UserInterface object.
     *    This object is used to validate the incoming username/password pair.
     * 3. If credentials are valid, user TokenInterface object is created, and returned.
     *    UserInterface object obtained during step 2, is associated with the given token.
     *    Resultant token has valid credentials set as well i.e. it will PASS verifyToken() test.
     *    If credentials are invalid, BadCredentialsException is thrown.
     *
     * @param Request $request
     *
     * @throws BadCredentialsException
     * @return TokenInterface
     */
    public function issueToken(Request $request)
    {
        if ($request->headers->has('Authorization')) {
            return $this->issueTokenUsingAuthorizationHeader($request->headers->get('Authorization'));
        }

        if (!$username = $request->request->has('username')) {
            throw new BadCredentialsException('You must specify credentials.');
        }

        if (!$password = $request->request->has('password')) {
            throw new BadCredentialsException('You must specify credentials.');
        }

        return $this->issueTokenUsingUsernameAndPassword(
            $request->request->get('username'), $request->request->get('password')
        );
    }

    /**
     * Creates signed version of a given token.
     *
     * Procedure:
     *
     * 1. If token has both user and credentials set: credentials get verified.
     *    If verification passes - the very same TokenInterface object is returned (as it is already signed).
     *    Otherwise, BadCredentialsException is thrown.
     *
     * 2. If credentials are set, but user is anonymous i.e. we got Authentication: Bearer {{ token }} type of request:
     *    Decode token, create signed token with a username encoded as a part of decoded token's payload.
     *    If token cannot be decoded, throw BadCredentialsException.
     *
     * 3. User is set, but no credentials provided i.e. we got POST['username'] + POST['password'] type of request:
     *    Create payload, using incoming username, produce signed version of token.
     *
     * @param TokenInterface $token Token to sign
     * @param int $lifeTime Token lifetime in seconds
     *
     * @throws BadCredentialsException
     * @return TokenInterface
     */
    public function signToken(TokenInterface $token, $lifeTime = 3600)
    {
        try {
            $username = $this->userProvider->getUsernameForToken($token);
            $user = $this->userProvider->loadUserByUsername($username);
        } catch (UsernameNotFoundException $e) {
            throw new BadCredentialsException('Token user is not found in persistent store.');
        }

        if ($token->getCredentials()) { // credentials are set, but UserInterface object needs to be associated with token
            $token = $this->factory->createToken($user, $token->getCredentials()); // associate user, reuse credentials
        } else {  // user is set, generate encoded payload
            $payload = array(
                'username' => $user->getUsername(),
                'exp' => time() + $lifeTime,
            );
            $token = $this->factory->createToken($user, $this->encoder->encodeToken($payload)); // generate credentials
        }

        $this->verifyToken($token); // if no exceptions are thrown token is valid i.e. signed
        $token->setAuthenticated(true);

        return $token;
    }


    /**
     * Verifies that token is valid and correctly signed.
     * Assumed input is valid token, therefore on validation failure exceptions will be thrown.
     *
     * Possible failures:
     * - no user supplied to token
     * - no credentials supplied to token
     * - token can not be decoded
     * - username in token's user and payload do not match
     * - token is expired
     *
     * @param TokenInterface $token
     *
     * @throws BadCredentialsException
     * @return void Nothing is returned, if token is valid - procedure will complete w/o exceptions.
     */
    public function verifyToken(TokenInterface $token)
    {
        $user = $token->getUser();
        if (!($user instanceof UserInterface)) {
            throw new BadCredentialsException('No user associated with token.');
        }

        if (!$token->getCredentials()) {
            throw new BadCredentialsException('No credentials found in token.');
        }

        // decode
        $payload = $this->decoder->decodeToken($token->getCredentials());
        if (!$payload) {
            throw new BadCredentialsException('Cannot decode token credentials.');
        }

        // make sure that "username" in token is equals to that of supplied user
        if (!isset($payload['username']) || empty($payload['username'])) {
            throw new BadCredentialsException('Cannot extract username from token payload.');
        }
        if ($payload['username'] !== $user->getUsername()) {
            throw new BadCredentialsException('Token payload is invalid.');
        }

        // make sure that token is not expired
        if (!isset($payload['exp']) || empty($payload['exp'])) {
            throw new BadCredentialsException('Cannot extract expiration time from token payload.');
        }
        if ($payload['exp'] < time()) {
            throw new BadCredentialsException('Token is expired.');
        }
    }

    /**
     * Issues anonymous token, populating credentials by extracting value from Authorization HTTP header.
     *
     * @param string $header
     * @return TokenInterface
     * @throws InvalidArgumentException
     * @throws BadCredentialsException
     */
    private function issueTokenUsingAuthorizationHeader($header)
    {
        $matches = array();
        preg_match('/Bearer\s([^\s]+)/si', $header, $matches);
        if (!isset($matches[1])) {
            throw new InvalidArgumentException('Cannot parse authorization header..');
        }

        $credentials = $matches[1];

        // decode
        $payload = $this->decoder->decodeToken($credentials);
        if (!$payload) {
            throw new BadCredentialsException('Cannot decode token credentials.');
        }
        // make sure that 'username' is encoded
        if (!isset($payload['username']) || empty($payload['username'])) {
            throw new BadCredentialsException('Cannot extract username from token payload.');
        }

        $token = $this->factory->createToken($payload['username'], $credentials); // initialize anonymous user token
        $token->setAuthenticated(false);

        return $token;
    }

    /**
     * Issues user-associated token, for a given username/password combination.
     * Username/password pair MUST be valid, for such a token to be issued.
     *
     * @param $username
     * @param $password
     * @return TokenInterface
     * @throws BadCredentialsException
     * @throws UsernameNotFoundException
     */
    private function issueTokenUsingUsernameAndPassword($username, $password)
    {
        $user = $this->userProvider->loadUserByUsername($username); // UsernameNotFoundException thrown if not found
        $token = null;

        // check password
        $encoder = $this->encoderFactory->getEncoder($user);
        if ($user->getPassword() == $encoder->encodePassword($password, $user->getSalt())) { // passwords do match
            $token = $this->factory->createToken($user);
            $token->setAuthenticated(false);
        }

        // token issued, return it
        if ($token) {
            return $token;
        }

        throw new BadCredentialsException('Authentication failed..');
    }
}