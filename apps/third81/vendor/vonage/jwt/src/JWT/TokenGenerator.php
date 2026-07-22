<?php
declare(strict_types=1);

namespace Vonage\JWT;

use Ramsey\Uuid\Uuid;
use RuntimeException;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Hmac\Sha256 as Sha256HMAC;
use Lcobucci\JWT\Signer\Key\InMemory;
use stdClass;
use Lcobucci\JWT\Token\Parser as TokenParser;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;
use Vonage\JWT\Exception\InvalidJTIException;

class TokenGenerator
{
    /**
     * UUID of the application we are generating a UUID for
     */
    protected string $applicationId;

    /**
     * Set of generic claims to add to a JWT
     * @var array<mixed>
     */
    protected array $claims = [];

    /**
     * Configuration of the token we are using
     */
    protected Configuration $config;

    /**
     * Number of seconds to expire in, defaults to 15 minutes
     */
    protected int $ttl = 900;

    /**
     * UUIDv4 ID for the JWT
     */
    protected string $jti;

    /**
     * Unix Timestamp at which this token becomes valid
     */
    protected \DateTimeImmutable $nbf;

    /**
     * ACL Path information
     * @var array<string, stdClass>
     */
    protected array $paths = [];

    /**
     * Private key text used for signing
     */
    protected InMemory $privateKey;

    /**
     * Subject to use in the JWT
     */
    protected string $subject;

    public function __construct(string $applicationId, string $privateKey)
    {
        $this->applicationId = $applicationId;
        $this->privateKey = InMemory::plainText($privateKey);

        $this->config = Configuration::forSymmetricSigner(new Sha256(), $this->privateKey);
    }

    /**
     * @param array<string, array<string, string>> $options
     */
    public function addPath(string $path, array $options = []) : self
    {
        $this->paths[$path] = (object) $options;
        return $this;
    }

    /**
     * Factory to create a token in one call
     * $options format:
     *  - ttl: string
     *  - jti: string
     *  - paths: array<string, \stdClass>
     *  - not_before: int|\DateTimeImmutable
     *  - sub: string
     *
     * @param array<string, mixed> $options
     */
    public static function factory(string $applicationId, string $privateKey, array $options = []) : string
    {
        $generator = new self($applicationId, $privateKey);

        if (array_key_exists('ttl', $options)) {
            $generator->setTTL($options['ttl']);
            unset($options['ttl']);
        }

        if (array_key_exists('jti', $options)) {
            $generator->setJTI($options['jti']);
            unset($options['jti']);
        }

        if (array_key_exists('paths', $options)) {
            $generator->setPaths($options['paths']);
            unset($options['paths']);
        }

        if (array_key_exists('not_before', $options)) {
            if (is_int($options['not_before'])) {
                $options['not_before'] = (new \DateTimeImmutable())->setTimestamp($options['not_before']);
            }
            $generator->setNotBefore($options['not_before']);
            unset($options['not_before']);
        }

        if (array_key_exists('sub', $options)) {
            $generator->setSubject($options['sub']);
            unset($options['sub']);
        }

        foreach ($options as $key => $value) {
            $generator->addClaim($key, $value);
        }

        return $generator->generate();
    }

    public function generate() : string
    {
        $iat = time();
        $exp = $iat + $this->ttl;

        $builder = $this->config->builder()
            ->issuedAt((new \DateTimeImmutable())->setTimestamp($iat))
            ->expiresAt((new \DateTimeImmutable())->setTimestamp($exp))
            ->identifiedBy($this->getJTI())
            ->withClaim('application_id', $this->applicationId);

        if (!empty($this->getPaths())) {
            $builder = $builder->withClaim('acl', ['paths' => $this->getPaths()]);
        }

        try {
            $builder = $builder->canOnlyBeUsedAfter($this->getNotBefore());
        } catch (RuntimeException $e) {
            // This is fine, NBF isn't required
        }

        try {
            $builder = $builder->relatedTo($this->getSubject());
        } catch (RuntimeException $e) {
            // This is fine, Subject isn't required
        }

        foreach ($this->claims as $key => $value) {
            $builder = $builder->withClaim($key, $value);
        }

        return $builder->getToken($this->config->signer(), $this->config->signingKey())->toString();
    }

    public function getJTI() : string
    {
        if (!isset($this->jti)) {
            $this->jti = Uuid::uuid4()->toString();
        }

        return $this->jti;
    }

    public function getNotBefore() : \DateTimeImmutable
    {
        if (!isset($this->nbf)) {
            throw new RuntimeException('Not Before time has not been set');
        }

        return $this->nbf;
    }

    public function getParser(): Parser
    {
        return $this->config->parser();
    }

    /**
     * @return array<string, stdClass>
     */
    public function getPaths() : array
    {
        return $this->paths;
    }

    public function getSubject() : string
    {
        if (!isset($this->subject)) {
            throw new RuntimeException('Subject has not been set');
        }

        return $this->subject;
    }

    public function addClaim(string $claim, mixed $value): self
    {
        $this->claims[$claim] = $value;
        return $this;
    }

    public function setTTL(int $seconds) : self
    {
        $this->ttl = $seconds;
        return $this;
    }

    public function setJTI(string $uuid) : self
    {
        if (!Uuid::isValid($uuid)) {
            throw new InvalidJTIException('JTI must be a UUIDv4 string');
        }

        $this->jti = $uuid;
        return $this;
    }

    public function setNotBefore(\DateTimeImmutable $timestamp) : self
    {
        $this->nbf = $timestamp;
        return $this;
    }

    /**
     * Sets the ACL path information for this token
     * WARNING: This will reset the paths to the new list, overriding any
     * existing paths.
     *
     * @param array<string|int, array<string, mixed>|string> $pathData
     */
    public function setPaths(array $pathData) : self
    {
        $this->paths = [];
        foreach ($pathData as $key => $data) {
            if (is_string($key)) {
                $this->addPath($key, $data);
            } else {
                $this->addPath($data);
            }
        }

        return $this;
    }

    public function setSubject(string $subject) : self
    {
        $this->subject = $subject;
        return $this;
    }

    public function getTTL() : int
    {
        return $this->ttl;
    }

    public static function verifySignature(string $token, string $secret): bool
    {
        $parser = new TokenParser(new JoseEncoder());
        $validator = new Validator();

        $token = $parser->parse($token);

        return $validator->validate(
            $token, new SignedWith(
                new Sha256HMAC(),
                InMemory::plainText($secret)
            ));
    }
}
