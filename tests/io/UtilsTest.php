<?php

namespace Directus\Tests\Api\Io;

use Directus\Hash\Exception\HasherNotFoundException;
use Directus\Validator\Exception\InvalidRequestException;
use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    protected $availableHashers = [
        'bcrypt',
        'core',
        'md5',
        'sha1',
        'sha224',
        'sha256',
        'sha384',
        'sha512'
    ];

    public function testHash()
    {
        $string = 'secret';

        $this->tryHashWith($string);

        foreach ($this->availableHashers as $hasher) {
            $this->tryHashWith($string, $hasher);
        }

        $data = [
            'string' => $string
        ];

        $path = 'utils/hash';
        $data['hasher'] = 'none';
        $response = request_error_post($path, $data, ['query' => [
            'access_token' => 'token'
        ]]);

        assert_response_error($this, $response, [
            'code' => HasherNotFoundException::ERROR_CODE,
            'status' => 422
        ]);

        // Empty string passed
        $response = request_error_post($path, ['string' => ''], ['query' => [
            'access_token' => 'token'
        ]]);

        assert_response_error($this, $response, [
            'code' => InvalidRequestException::ERROR_CODE,
            'status' => 422
        ]);

        // Not string passed
        $response = request_error_post($path, [], ['query' => [
            'access_token' => 'token'
        ]]);

        assert_response_error($this, $response, [
            'code' => InvalidRequestException::ERROR_CODE,
            'status' => 422
        ]);
    }

    public function testHashMatch()
    {
        $string = 'secret';

        $this->tryHashMatchWith($string);

        foreach ($this->availableHashers as $hasher) {
            $this->tryHashMatchWith($string, $hasher);
        }

        $data = [
            'string' => $string
        ];

        $path = 'utils/hash/match';
        $data['hasher'] = 'none';
        $data['hash'] = 'invalid-hash';
        $response = request_error_post($path, $data, ['query' => [
            'access_token' => 'token'
        ]]);

        assert_response_error($this, $response, [
            'code' => HasherNotFoundException::ERROR_CODE,
            'status' => 422
        ]);

        // Empty string passed
        $response = request_error_post($path, ['string' => ''], ['query' => [
            'access_token' => 'token'
        ]]);

        assert_response_error($this, $response, [
            'code' => InvalidRequestException::ERROR_CODE,
            'status' => 422
        ]);

        // Not string passed
        $response = request_error_post($path, [], ['query' => [
            'access_token' => 'token'
        ]]);

        assert_response_error($this, $response, [
            'code' => InvalidRequestException::ERROR_CODE,
            'status' => 422
        ]);
    }

    public function testRandomString()
    {
        $path = 'utils/random/string';
        $queryParams = ['access_token' => 'token'];

        // default length
        $data = [];
        $response = request_post($path, $data, ['query' => $queryParams]);
        assert_response($this, $response);
        $result = response_to_object($response);
        $data = $result->data;
        $this->assertTrue(strlen($data->random) === 32);

        // specifying the length
        $data = ['length' => 16];
        $response = request_post($path, $data, ['query' => $queryParams]);
        assert_response($this, $response);
        $result = response_to_object($response);
        $data = $result->data;
        $this->assertTrue(strlen($data->random) === 16);

        // Length not numeric passed
        $response = request_error_post($path, ['length' => 'a'], ['query' => $queryParams]);
        assert_response_error($this, $response, [
            'code' => InvalidRequestException::ERROR_CODE,
            'status' => 422
        ]);
    }

    protected function tryHashWith($string, $hasher = null)
    {
        $path = 'utils/hash';
        $queryParams = ['access_token' => 'token'];
        $data = ['string' => $string, 'hasher' => $hasher];

        $response = request_post($path, $data, ['query' => $queryParams]);
        assert_response($this, $response);

        $result = response_to_object($response);

        $data = $result->data;
        $this->assertInternalType('string', $data->hash);

        return $data->hash;
    }

    protected function tryHashMatchWith($string, $hasher = null)
    {
        $hash = $this->tryHashWith($string, $hasher);

        $path = 'utils/hash/match';
        $queryParams = ['access_token' => 'token'];

        $data = ['string' => $string, 'hasher' => $hasher, 'hash' => $hash];
        $response = request_post($path, $data, ['query' => $queryParams]);
        assert_response($this, $response);

        $result = response_to_object($response);
        $this->assertTrue($result->data->valid);
    }
}
