<?php

declare(strict_types=1);

namespace ReallySimpleJWT;

use ReallySimpleJWT\Validate;
use ReallySimpleJWT\Encode;
use ReallySimpleJWT\Helper\JsonEncoder;
use ReallySimpleJWT\Exception\Validate as ValidateException;

class Build
{
    use JsonEncoder;

    private $payload = [];

    private $header = [];

    private $validate;

    private $secret;

    private $encode;

    private $type;

    public function __construct(string $type, Validate $validate, Encode $encode)
    {
        $this->type = $type;

        $this->validate = $validate;

        $this->encode = $encode;
    }

    public function getHeader(): array
    {
        return ['alg' => $this->encode->getAlgorithm(), 'typ' => $this->type];
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setSecret(string $secret): self
    {
        if (!$this->validate->secret($secret)) {
            throw new ValidateException('Please set a valid secret. It must be at least twelve characters in length, contain lower and upper case letters, a number and one of the following characters *&!@%^#$.');
        }

        $this->secret = $secret;

        return $this;
    }

    public function setExpiration(int $timestamp): self
    {
        if (!$this->validate->expiration($timestamp)) {
            throw new ValidateException('The expiration timestamp you set has already expired.');
        }

        $this->payload['exp'] = $timestamp;

        return $this;
    }

    public function setIssuer(string $issuer): self
    {
        $this->payload['iss'] = $issuer;

        return $this;
    }

    public function setPrivateClaim(string $key, $value): self
    {
        $this->payload[$key] = $value;

        return $this;
    }

    public function build(): Jwt
    {
        return new Jwt(
            $this->encode->encode($this->jsonEncode($this->getHeader())) . "." .
            $this->encode->encode($this->jsonEncode($this->getPayload())) . "." .
            $this->encode->signature($this->jsonEncode($this->getHeader()), $this->jsonEncode($this->getPayload()), $this->secret),
            $this->secret
        );
    }

    public function reset(): self
    {
        $this->payload = [];
        $this->header = [];
        $this->secret = '';

        return $this;
    }
}
