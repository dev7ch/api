<?php

namespace Directus\Tests\Api\Io;
use PHPUnit\Framework\TestCase;

class GeneralTest extends TestCase
{
    public function testPing()
    {
        $response = request_get('server/ping', [], [
            'project' => false
        ]);
        assert_response_contents($this, $response, 'pong', [
            'status' => 200
        ]);
    }

    public function testErrorExtraInformation()
    {
        // TODO: Switch between production and development to add more error information
    }
}
