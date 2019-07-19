<?php

namespace Directus\Tests\Api\Io;
use PHPUnit\Framework\TestCase;

class CollectionPresetsTest extends TestCase
{
    /**
     * @var array
     */
    protected $data = [
        ['view_type' => 'tabular', 'collection' => 'products'],
        ['view_type' => 'tabular', 'collection' => 'products', 'user' => 2],
        ['view_type' => 'tabular', 'collection' => 'orders', 'user' => 1],
        ['view_type' => 'tabular', 'collection' => 'categories', 'user' => 1],
        ['view_type' => 'tabular', 'collection' => 'orders', 'user' => 2],
        ['view_type' => 'tabular', 'collection' => 'customers', 'user' => 1]
    ];

    public static function resetDatabase()
    {
        $db = create_db_connection();
        reset_table_id($db, 'directus_collection_presets', 4);
    }

    public static function setUpBeforeClass()
    {
        static::resetDatabase();
    }

    public static function tearDownAfterClass()
    {
        static::resetDatabase();
    }

    public function testCreate()
    {
        $path = 'collection_presets';

        $data = $this->data[0];
        $response = request_post($path, $data, ['query' => ['access_token' => 'token']]);

        assert_response($this, $response);
        assert_response_data_contains($this, $response, $data);
    }

    public function testUpdate()
    {
        $path = 'collection_presets/4';

        $data = [
            'collection' => 'products',
            'search_query' => 'a product'
        ];
        $response = request_patch($path, $data, ['query' => ['access_token' => 'token']]);

        assert_response($this, $response);
        assert_response_data_contains($this, $response, $data);
    }

    public function testGetOne()
    {
        $path = 'collection_presets/4';

        $data = [
            'id' => 4,
            'collection' => 'products',
            'search_query' => 'a product'
        ];

        $response = request_get($path, ['access_token' => 'token']);
        assert_response($this, $response);
        assert_response_data_contains($this, $response, $data);
        assert_response_data_fields($this, $response, [
            'id',
            'title',
            'user',
            'role',
            'collection',
            'search_query',
            'filters',
            'view_type',
            'view_query',
            'view_options',
            'translation',
        ]);
    }

    public function testList()
    {
        $path = 'collection_presets';

        $response = request_get($path, ['access_token' => 'token']);
        assert_response($this, $response, [
            'data' => 'array',
            'count' => 4
        ]);
    }

    public function testDelete()
    {
        $path = 'collection_presets/4';
        $response = request_delete($path, ['query' => ['access_token' => 'token']]);

        assert_response_empty($this, $response);
    }

    public function testAllUserCollectionPresets()
    {
        $path = 'collection_presets';
        $data = $this->data;

        foreach ($data as $item) {
            request_post($path, $item, ['query' => ['access_token' => 'token']]);
        }

        $response = request_get($path, [
            'access_token' => 'token',
            'filter' => [
                'user' => 1
            ]
        ]);

        assert_response($this, $response, [
            'data' => 'array',
            'count' => 3
        ]);

        $response = request_get($path, [
            'access_token' => 'token',
            'filter' => [
                'user' => 2
            ]
        ]);

        assert_response($this, $response, [
            'data' => 'array',
            'count' => 2
        ]);
    }

    public function testAllCollectionPresets()
    {
        $path = 'collection_presets';

        $response = request_get($path, [
            'access_token' => 'token',
            'filter' => [
                'collection' => 'products'
            ]
        ]);

        assert_response($this, $response, [
            'data' => 'array',
            'count' => 2
        ]);

        $response = request_get($path, [
            'access_token' => 'token',
            'filter' => [
                'collection' => 'customers'
            ]
        ]);

        assert_response($this, $response, [
            'data' => 'array',
            'count' => 1
        ]);
    }
}
