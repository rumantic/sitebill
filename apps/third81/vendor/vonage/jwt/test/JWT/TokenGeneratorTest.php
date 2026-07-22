<?php
declare(strict_types=1);

namespace Vonage\JWT;

/**
 * Mock time call to help with testing
 */
function time()
{
    return 1590087267;
}

namespace VonageTest\JWT;

use Generator;
use Ramsey\Uuid\Uuid;
use Lcobucci\JWT\Parser;
use Vonage\JWT\TokenGenerator;
use PHPUnit\Framework\TestCase;
use Vonage\JWT\Exception\InvalidJTIException;
use stdClass;

class TokenGeneratorTest extends TestCase
{
    /**
     * Generating a token with just an App ID and a Private Key
     */
    public function testGenerateSimpleToken()
    {
        $generator = new TokenGenerator(
            'd70425f2-1599-4e4c-81c4-cffc66e49a12',
            file_get_contents(__DIR__ . '/resources/private.key')
        );
        $token = $generator->generate();

        $parsedToken = $generator->getParser()->parse($token);
        $this->assertSame('RS256', $parsedToken->headers()->get('alg'));
        $this->assertSame('JWT', $parsedToken->headers()->get('typ'));
        $this->assertSame(1590087267, $parsedToken->claims()->get('iat')->getTimestamp());
        $this->assertSame(1590087267 + 900, $parsedToken->claims()->get('exp')->getTimestamp());
        $this->assertTrue(Uuid::isValid($parsedToken->claims()->get('jti')));
        $this->assertFalse($parsedToken->headers()->has('acl'));
        $this->assertFalse($parsedToken->headers()->has('nbf'));
        $this->assertFalse($parsedToken->claims()->has('sub'));
    }

    /**
     * User should be able to override the expiration time
     */
    public function testCanChangeTTL()
    {
        $generator = new TokenGenerator(
            'd70425f2-1599-4e4c-81c4-cffc66e49a12',
            file_get_contents(__DIR__ . '/resources/private.key')
        );
        $generator->setTTL(50);
        $token = $generator->generate();

        $parsedToken = $generator->getParser()->parse($token);
        $this->assertSame(1590087267 + 50, $parsedToken->claims()->get('exp')->getTimestamp());
    }

    /**
     * User should be able to supply their own JWT ID
     */
    public function testCanSetJWTID()
    {
        $uuid = Uuid::uuid4()->toString();

        $generator = new TokenGenerator(
            'd70425f2-1599-4e4c-81c4-cffc66e49a12',
            file_get_contents(__DIR__ . '/resources/private.key')
        );
        $generator->setJTI($uuid);
        $token = $generator->generate();

        $parsedToken = $generator->getParser()->parse($token);
        $this->assertTrue(Uuid::isValid($parsedToken->claims()->get('jti')));
        $this->assertSame($uuid, $parsedToken->claims()->get('jti'));
    }

    /**
     * JWT ID must reject anything that isn't a UUIDv4
     */
    public function testRejectsInvalidJTI()
    {
        $this->expectException(InvalidJTIException::class);

        $generator = new TokenGenerator(
            'd70425f2-1599-4e4c-81c4-cffc66e49a12',
            file_get_contents(__DIR__ . '/resources/private.key')
        );
        $generator->setJTI('abcd');
    }

    /**
     * User can see a "Not Before" time
     */
    public function testCanSetNBF()
    {
        $nbf = new \DateTimeImmutable('2025-01-01 00:00:00');

        $generator = new TokenGenerator(
            'd70425f2-1599-4e4c-81c4-cffc66e49a12',
            file_get_contents(__DIR__ . '/resources/private.key')
        );
        $generator->setNotBefore($nbf);
        $token = $generator->generate();

        $parsedToken = $generator->getParser()->parse($token);
        $this->assertEquals($nbf, $parsedToken->claims()->get('nbf'));
    }

    /**
     * User can set bulk path ACL information
     */
    public function testCanSetACLPaths()
    {
        $paths = [
            '/*/users/**',
            '/*/conversations/**'
        ];

        $generator = new TokenGenerator(
            'd70425f2-1599-4e4c-81c4-cffc66e49a12',
            file_get_contents(__DIR__ . '/resources/private.key')
        );
        $generator->setPaths($paths);
        $token = $generator->generate();

        $parsedToken = $generator->getParser()->parse($token);
        $this->assertTrue($parsedToken->claims()->has('acl'));
        $acl = $parsedToken->claims()->get('acl');
        if ($acl instanceof \stdClass) {
            $acl = json_decode(json_encode($acl), true);
        }

        $this->assertCount(2, $acl['paths']);
        $this->assertArrayHasKey($paths[0], $acl['paths']);
        $this->assertArrayHasKey($paths[1], $acl['paths']);
    }

    /**
     * User can set complex bulk path ACL information
     */
    public function testCanSetComplexACLInformation()
    {
        $paths = [
            '/*/users/**',
            '/*/conversations/**' => [
                'methods' => ['GET']
            ]
        ];

        $generator = new TokenGenerator(
            'd70425f2-1599-4e4c-81c4-cffc66e49a12',
            file_get_contents(__DIR__ . '/resources/private.key')
        );
        $generator->setPaths($paths);
        $token = $generator->generate();

        $parsedToken = $generator->getParser()->parse($token);
        $this->assertTrue($parsedToken->claims()->has('acl'));
        $acl = $parsedToken->claims()->get('acl');
        if ($acl instanceof \stdClass) {
            $acl = json_decode(json_encode($acl), true);
        }

        $this->assertCount(2, (array) $acl['paths']);

        $convoPath = '/*/conversations/**';
        $this->assertTrue(is_array($acl['paths'][$convoPath]['methods']));
    }

    /**
     * User can add individual ACL paths
     */
    public function testCanAddACLPath()
    {
        $path = '/*/users/**';

        $generator = new TokenGenerator(
            'd70425f2-1599-4e4c-81c4-cffc66e49a12',
            file_get_contents(__DIR__ . '/resources/private.key')
        );
        $generator->addPath($path);
        $token = $generator->generate();

        $parsedToken = $generator->getParser()->parse($token);
        $this->assertTrue($parsedToken->claims()->has('acl'));
        $acl = $parsedToken->claims()->get('acl');
        if ($acl instanceof \stdClass) {
            $acl = json_decode(json_encode($acl), true);
        }

        $this->assertCount(1, (array) $acl['paths']);
    }

    /**
     * User can add individual ACL path information with additional constraints
     */
    public function testCanAddACLPathWithOptions()
    {
        $path = '/';
        $options = [
            'methods' => ['GET']
        ];

        $generator = new TokenGenerator(
            'd70425f2-1599-4e4c-81c4-cffc66e49a12',
            file_get_contents(__DIR__ . '/resources/private.key')
        );
        $generator->addPath($path, $options);
        $token = $generator->generate();

        $parsedToken = $generator->getParser()->parse($token);
        $this->assertTrue($parsedToken->claims()->has('acl'));
        $acl = $parsedToken->claims()->get('acl');
        if ($acl instanceof \stdClass) {
            $acl = json_decode(json_encode($acl), true);
        }

        $this->assertCount(1, (array) $acl['paths']);
        $this->assertSame($options['methods'], $acl['paths'][$path]['methods']);
    }

    public function testFactoryGeneratesValidToken()
    {
        $generator = new TokenGenerator(
            'd70425f2-1599-4e4c-81c4-cffc66e49a12',
            file_get_contents(__DIR__ . '/resources/private.key')
        );

        $token = TokenGenerator::factory(
            'd70425f2-1599-4e4c-81c4-cffc66e49a12',
            file_get_contents(__DIR__ . '/resources/private.key')
        );

        $parsedToken = $generator->getParser()->parse($token);
        $this->assertSame('RS256', $parsedToken->headers()->get('alg'));
        $this->assertSame('JWT', $parsedToken->headers()->get('typ'));
        $this->assertSame(1590087267, $parsedToken->claims()->get('iat')->getTimestamp());
        $this->assertSame(1590087267 + 900, $parsedToken->claims()->get('exp')->getTimestamp());
        $this->assertTrue(Uuid::isValid($parsedToken->claims()->get('jti')));
        $this->assertFalse($parsedToken->headers()->has('acl'));
        $this->assertFalse($parsedToken->headers()->has('nbf'));
    }

    public function testFactoryUsesPassedOptions()
    {
        $uuid = Uuid::uuid4()->toString();
        $paths = [
            '/*/users/**',
            '/*/conversations/**' => [
                'methods' => ['GET']
            ]
        ];
        $nbf = strtotime('2025-01-01 00:00:00');

        $token = TokenGenerator::factory(
            'd70425f2-1599-4e4c-81c4-cffc66e49a12',
            file_get_contents(__DIR__ . '/resources/private.key'),
            [
                'ttl' => 50,
                'jti' => $uuid,
                'paths' => $paths,
                'not_before' => $nbf,
                'sub' => 'foo'
            ]
        );
        $generator = new TokenGenerator(
            'd70425f2-1599-4e4c-81c4-cffc66e49a12',
            file_get_contents(__DIR__ . '/resources/private.key')
        );

        $parsedToken = $generator->getParser()->parse($token);
        $this->assertSame('RS256', $parsedToken->headers()->get('alg'));
        $this->assertSame('JWT', $parsedToken->headers()->get('typ'));

        $this->assertSame(1590087267, $parsedToken->claims()->get('iat')->getTimestamp());
        $this->assertSame(1590087267 + 50, $parsedToken->claims()->get('exp')->getTimestamp());

        $this->assertTrue(Uuid::isValid($parsedToken->claims()->get('jti')));
        $this->assertSame($uuid, $parsedToken->claims()->get('jti'));

        $acl = $parsedToken->claims()->get('acl');
        if ($acl instanceof \stdClass) {
            $acl = json_decode(json_encode($acl), true);
        }
        $this->assertCount(2, $acl['paths']);
        $convoPath = '/*/conversations/**';
        $this->assertTrue(is_array($acl['paths'][$convoPath]['methods']));

        $this->assertSame($nbf, $parsedToken->claims()->get('nbf')->getTimestamp());
    }

    public function testCanSetTheSubject()
    {
        $generator = new TokenGenerator(
            'd70425f2-1599-4e4c-81c4-cffc66e49a12',
            file_get_contents(__DIR__ . '/resources/private.key')
        );
        $generator->setSubject('foo');
        $token = $generator->generate();

        $parsedToken = $generator->getParser()->parse($token);

        $this->assertTrue($parsedToken->claims()->has('sub'));
        $this->assertSame('foo', $parsedToken->claims()->get('sub'));
    }

    public function testCanAddGenericClaims()
    {
        $generator = new TokenGenerator(
            'd70425f2-1599-4e4c-81c4-cffc66e49a12',
            file_get_contents(__DIR__ . '/resources/private.key')
        );
        $generator->addClaim('foo', 'bar');
        $token = $generator->generate();

        $parsedToken = $generator->getParser()->parse($token);
        $this->assertTrue($parsedToken->claims()->has('foo'));
        $this->assertSame('bar', $parsedToken->claims()->get('foo'));
    }

    public function testCanAddGenericClaimsThroughFactory()
    {
        $generator = new TokenGenerator(
            'd70425f2-1599-4e4c-81c4-cffc66e49a12',
            file_get_contents(__DIR__ . '/resources/private.key')
        );

        $token = TokenGenerator::factory(
            'd70425f2-1599-4e4c-81c4-cffc66e49a12',
            file_get_contents(__DIR__ . '/resources/private.key'),
            [
                'foo' => 'bar'
            ]
        );

        $parsedToken = $generator->getParser()->parse($token);
        $this->assertTrue($parsedToken->claims()->has('foo'));
        $this->assertSame('bar', $parsedToken->claims()->get('foo'));
    }

    public function testCanValidateJWTWithSignatureSecret()
    {
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE1ODc0OTQ5NjIsImp0aSI6ImM1YmE4ZjI0LTFhMTQtNGMxMC1iZmRmLTNmYmU4Y2U1MTFiNSIsImlzcyI6IlZvbmFnZSIsInBheWxvYWRfaGFzaCI6ImQ2YzBlNzRiNTg1N2RmMjBlM2I3ZTUxYjMwYzBjMmE0MGVjNzNhNzc4NzliNmYwNzRkZGM3YTIzMTdkZDAzMWIiLCJhcGlfa2V5IjoiYTFiMmMzZCIsImFwcGxpY2F0aW9uX2lkIjoiYWFhYWFhYWEtYmJiYi1jY2NjLWRkZGQtMDEyMzQ1Njc4OWFiIn0.JQRKi1d0SQitmjPINfTWMpt3XZkGsLbD7EjCdXoNSbk';
        $secret = 'ZYtdTtGV3BCFN7tWmOWr1md66XsquMggr4W2cTtXtcPgfnI0Xw';
        $this->assertTrue(TokenGenerator::verifySignature($token, $secret));
    }

    public function testWillNotValidateJWTWithBadSecret()
    {
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE1ODc0OTQ5NjIsImp0aSI6ImM1YmE4ZjI0LTFhMTQtNGMxMC1iZmRmLTNmYmU4Y2U1MTFiNSIsImlzcyI6IlZvbmFnZSIsInBheWxvYWRfaGFzaCI6ImQ2YzBlNzRiNTg1N2RmMjBlM2I3ZTUxYjMwYzBjMmE0MGVjNzNhNzc4NzliNmYwNzRkZGM3YTIzMTdkZDAzMWIiLCJhcGlfa2V5IjoiYTFiMmMzZCIsImFwcGxpY2F0aW9uX2lkIjoiYWFhYWFhYWEtYmJiYi1jY2NjLWRkZGQtMDEyMzQ1Njc4OWFiIn0.JQRKi1d0SQitmjPINfTWMpt3XZkGsLbD7EjCdXoNSbk';
        $secret = 'ZYtdTtGV3BCFN7tWmOWr1md66XsquMggr4W2cTtXtcPgf55555';
        $this->assertFalse(TokenGenerator::verifySignature($token, $secret));
    }
}
