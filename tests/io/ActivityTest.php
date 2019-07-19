<?php

namespace Directus\Tests\Api\Io;

use Directus\Database\Connection;
use Directus\Database\Exception\ItemNotFoundException;
use Directus\Database\TableGateway\DirectusActivityTableGateway;
use GuzzleHttp\Exception\RequestException;
use PHPUnit\Framework\TestCase;

class ActivityTest extends TestCase
{
    /**
     * @var Connection
     */
    protected $db;

    public function setUp()
    {
        $this->db = create_db_connection();

        $this->truncateTable();
        $this->dropSampleTables();
        $this->createSampleTables();

        $currentTime = time();
        for ($i = 15; $i >= 1; $i--) {
            $date = date(
                'Y-m-d',
                strtotime(
                    sprintf('-%d day', $i),
                    $currentTime
                )
            );
            table_insert($this->db, 'directus_activity', [
                'id' => null,
                'action' => 'authentication',
                'action_by' => 1,
                'action_on' => sprintf('%s 15:52:37', $date),
                'ip' => '::1',
                'user_agent' => 'GuzzleHttp/6.2.1 curl/7.52.1 PHP/5.5.38',
                'collection' => 'directus_users',
                'item' => 1,
                'comment' => null

            ]);
        }
    }

    public function tearDown()
    {
        $this->truncateTable();
        $this->dropSampleTables();
    }

    public function testColumns()
    {
        $columns = [
            'id',
            'action',
            'action_by',
            'action_on',
            'ip',
            'user_agent',
            'collection',
            'item',
            'edited_on',
            'comment',
            'comment_deleted_on',
        ];

        $path = 'activity';

        // Not selecting columns
        $response = request_get($path, ['access_token' => 'token']);
        assert_response($this, $response, [
            'data' => 'array',
            'fields' => $columns
        ]);

        // Using Asterisk
        $response = request_get($path, ['access_token' => 'token', 'fields' => '*']);
        assert_response($this, $response, [
            'data' => 'array',
            'fields' => $columns
        ]);

        // Using a list of columns (array)
        $response = request_get($path, ['access_token' => 'token', 'fields' => $columns]);
        assert_response($this, $response, [
            'data' => 'array',
            'fields' => $columns
        ]);

        // Using a list of columns (csv)
        $response = request_get($path, ['access_token' => 'token', 'fields' => implode(',', $columns)]);
        assert_response($this, $response, [
            'data' => 'array',
            'fields' => $columns
        ]);

        // Selecting some columns (array)
        $someColumns = ['id', 'action'];
        $response = request_get($path, ['access_token' => 'token', 'fields' => $someColumns]);
        assert_response($this, $response, [
            'data' => 'array',
            'fields' => $someColumns
        ]);

        // Selecting some columns (csv)
        $response = request_get($path, ['access_token' => 'token', 'fields' => implode(',', $someColumns)]);
        assert_response($this, $response, [
            'data' => 'array',
            'fields' => $someColumns
        ]);
    }

    public function testMeta()
    {
        $path = 'activity';
        $response = request_get($path, [
            'meta' => '*',
            'access_token' => 'token'
        ]);

        assert_response($this, $response, [
            'data' => 'array'
        ]);
        assert_response_meta($this, $response, [
            'collection' => 'directus_activity',
            'type' => 'collection'
        ]);
    }

    public function testLimit()
    {
        $path = 'activity';
        $response = request_get($path, [
            'meta' => '*',
            'access_token' => 'token',
            'limit' => 10
        ]);

        assert_response($this, $response, [
            'count' => 10,
            'data' => 'array'
        ]);
        assert_response_meta($this, $response, [
            'collection' => 'directus_activity',
            'type' => 'collection',
            'result_count' => 10
        ]);
    }

    public function testId()
    {
        $path = 'activity/1';
        $response = request_get($path, [
            'meta' => '*',
            'access_token' => 'token'
        ]);

        assert_response($this, $response);
        assert_response_meta($this, $response, [
            'collection' => 'directus_activity',
            'type' => 'item'
        ]);
    }

    public function testActivity()
    {
        $this->truncateTable();

        // Authenticate
        request_post('auth/authenticate', [
            'email' => 'admin@getdirectus.com',
            'password' => 'password'
        ]);

        request_post('items/test', [
            'name' => 'Product 1'
        ], ['query' => ['access_token' => 'token']]);

        request_patch('items/test/1', [
            'name' => 'Product 01'
        ], ['query' => ['access_token' => 'token']]);

        request_delete('items/test/1', ['query' => ['access_token' => 'token']]);

        $response = request_get('activity', ['access_token' => 'token']);

        assert_response($this, $response, [
            'data' => 'array',
            'count' => 4
        ]);

        $result = response_to_object($response);
        $data = $result->data;
        $actions = [
            DirectusActivityTableGateway::ACTION_AUTHENTICATE,
            DirectusActivityTableGateway::ACTION_CREATE,
            DirectusActivityTableGateway::ACTION_UPDATE,
            DirectusActivityTableGateway::ACTION_DELETE
        ];

        foreach ($data as $item) {
            $this->assertSame(array_shift($actions), $item->action);
        }
    }

    public function testGetActivity()
    {
        $response = request_get('activity/1', ['access_token' => 'token']);
        assert_response($this, $response);

        $this->truncateTable();

        try {
            $response = request_get('activity/1', ['access_token' => 'token']);
        } catch (RequestException $e) {
            $response = $e->getResponse();
        }

        assert_response_error($this, $response, [
            'status' => 404,
            'code' => ItemNotFoundException::ERROR_CODE
        ]);
    }

    public function testCreateComment()
    {
        $response = request_post('activity/comment', [
            'collection' => 'categories',
            'item' => 1,
            'comment' => 'a comment'
        ], ['query' => ['access_token' => 'token']]);

        assert_response($this, $response, [
            'data' => 'object'
        ]);

        assert_response_data_contains($this, $response, [
            'action' => DirectusActivityTableGateway::ACTION_COMMENT,
            'comment' => 'a comment'
        ]);
    }

    protected function truncateTable()
    {
        truncate_table($this->db, 'directus_activity');
    }

    protected function createSampleTables()
    {
        if (!$this->db) {
            return;
        }

        $query = 'CREATE TABLE `test` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;';

        $this->db->execute($query);

        table_insert($this->db, 'directus_collections', [
            'collection' => 'test'
        ]);
    }

    protected function dropSampleTables()
    {
        drop_table($this->db, 'test');
    }
}
