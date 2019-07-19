<?php

namespace Directus\Tests\Api\Io;

use Directus\Database\Connection;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ActivityCommentTest extends TestCase
{
    /**
     * @var Connection
     */
    protected static $db;

    /**
     * @var array
     */
    protected $flags = [];

    public static function setUpBeforeClass()
    {
        static::$db = create_db_connection();
        static::resetDatabase();
        request_post('collections', [
            'collection' => 'test',
            'fields' => [
                [
                    'field' => 'id',
                    'auto_increment' => true,
                    'type' => 'integer',
                    'datatype' => 'integer',
                    'primary_key' => true,
                    'interface' => 'primary_key',
                    'length' => 11,
                ],
                [
                    'field' => 'name',
                    'type' => 'string',
                    'datatype' => 'varchar',
                    'interface' => 'text_input',
                    'length' => 255,
                ],
                [
                    'field' => 'status',
                    'type' => 'status',
                    'datatype' => 'integer',
                    'interface' => 'status',
                    'default_value' => 2,
                    'length' => 11,
                ],
            ]
        ], ['query' => ['access_token' => 'token']]);

        $query = 'CREATE TABLE `objects` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;';

        static::$db->execute($query);

        table_insert(static::$db, 'directus_collections', [
            'collection' => 'objects'
        ]);
    }

    public static function tearDownAfterClass()
    {
        static::resetDatabase();
    }

    public static function resetDatabase()
    {
        self::clearData();
        truncate_table(static::$db, 'directus_permissions');
        truncate_table(static::$db, 'directus_folders');
        truncate_table(static::$db, 'directus_activity');
        drop_table(static::$db, 'test');
        drop_table(static::$db, 'test2');
        drop_table(static::$db, 'objects');
    }

    public static function clearData()
    {
        if (table_exists(static::$db, 'test')) {
            truncate_table(static::$db, 'test');
        }

        if (table_exists(static::$db, 'test2')) {
            truncate_table(static::$db, 'test2');
        }

        truncate_table(static::$db, 'directus_permissions');
        truncate_table(static::$db, 'directus_collection_presets');
        truncate_table(static::$db, 'directus_settings');
        reset_table_id(static::$db, 'directus_roles', 4);
        reset_table_id(static::$db, 'directus_users', 4);
        truncate_table(static::$db, 'directus_files');
        truncate_table(static::$db, 'directus_folders');
        $storagePath = __DIR__ . '/../../public/uploads/_/originals';

        clear_storage($storagePath);
    }

    public function testWithoutFlag()
    {
        $this->clearData();
        $this->flags = [];

        $this->doCollectionPresets();
        $this->doCollections();
        $this->doFields();
        $this->doFiles();
        $this->doFilesFolders();
        $this->doRoles();
        $this->doItems();
        $this->doPermissions();
        $this->doSettings();
        $this->doUsers();
    }

    public function testWithFlagOff()
    {
        $this->clearData();
        $this->flags = [];

        $this->setFlagOff('directus_collection_presets');
        $this->doCollectionPresets();

        $this->setFlagOff('directus_collections');
        $this->doCollections();

        $this->setFlagOff('directus_collections');
        $this->doFields();

        $this->setFlagOff('directus_files');
        $this->doFiles();

        $this->setFlagOff('directus_folders');
        $this->doFilesFolders();

        $this->setFlagOff('directus_roles');
        $this->doRoles();

        $this->setFlagOff('test');
        $this->doItems();

        $this->setFlagOff('test', 0);
        $this->doItemsWithStatus(0);
        $this->setFlagOff('test', 1);
        $this->doItemsWithStatus(0);

        $this->setFlagOff('directus_permissions');
        $this->doPermissions();

        $this->setFlagOff('directus_settings');
        $this->doSettings();

        $this->setFlagOff('directus_users');
        $this->doUsers();
    }

    public function testWithFlagOnAlways()
    {
        $this->clearData();
        $this->flags = [];

        $this->setFlagOnAlways('directus_collection_presets');
        $this->doCollectionPresets(true);
        $this->doCollectionPresets(false, 'message');

        $this->setFlagOnAlways('directus_collections');
        $this->doCollections(true);
        $this->doCollections(false, 'message');

        $this->setFlagOnAlways('directus_fields');
        $this->doFields(true);
        $this->doFields(false, 'message');

        $this->setFlagOnAlways('directus_files');
        $this->doFiles(true);
        $this->doFiles(false, 'message');

        $this->setFlagOnAlways('directus_folders');
        $this->doFilesFolders(true);
        $this->doFilesFolders(false, 'message');

        $this->setFlagOnAlways('directus_roles');
        $this->doRoles(true);
        $this->doRoles(false, 'message');

        $this->setFlagOnAlways('test');
        $this->doItems(true);
        $this->doItems(false, 'message');

        $this->setFlagOnAlways('test', 0);
        $this->doItemsWithStatus(0, true);
        $this->doItemsWithStatus(0, false, 'message');

        $this->setFlagOnAlways('test', 1);
        $this->doItemsWithStatus(1, true);
        $this->doItemsWithStatus(1, false, 'message');

        $this->setFlagOnAlways('directus_permissions');
        $this->doPermissions(true);
        $this->doPermissions(false, 'message');

        $this->setFlagOnAlways('directus_settings');
        $this->doSettings(true);
        $this->doSettings(false, 'message');

        $this->setFlagOnAlways('directus_users');
        $this->doUsers(true);
        $this->doUsers(false, 'message');
    }

    protected function doFiles($error = false, $message = null)
    {
        $data = [
            'filename' => 'activity.jpg',
            'data' => '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAUDBAQEAwUEBAQFBQUGBwwIBwcHBw8LCwkMEQ8SEhEPERETFhwXExQaFRERGCEYGh0dHx8fExciJCIeJBweHx7/2wBDAQUFBQcGBw4ICA4eFBEUHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh7/wAARCAB4AKADASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAX/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFgEBAQEAAAAAAAAAAAAAAAAAAAUH/8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEQMRAD8AugILDAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH/9k='
        ];
        $this->create('files', $data, $error, $message);
        $this->update('files/1', ['title' => 'Title test'], $error, $message);
        $this->delete('files/1', $error, $message);
    }

    protected function doFilesFolders($error = false, $message = null)
    {
        $data = [
            'name' => 'folder'
        ];
        $this->create('files/folders', $data, $error, $message);
        $this->update('files/folders/1', ['name' => 'logos'], $error, $message);
        $this->delete('files/folders/1', $error, $message);
    }

    protected function doRoles($error = false, $message = null)
    {
        $data = ['name' => 'new-role'];
        $this->create('roles', $data, $error, $message);
        $this->update('roles/4', $data, $error, $message);
        $this->delete('roles/4', $error, $message);
    }

    protected function doItems($error = false, $message = null)
    {
        $data = ['name' => 'new'];
        $this->create('items/test', $data, $error, $message);
        $this->update('items/test/1', $data, $error, $message);
        $this->delete('items/test/1', $error, $message);
    }

    protected function doItemsWithStatus($status, $error = false, $message = null)
    {
        if (table_exists(static::$db, 'test')) {
            truncate_table(static::$db, 'test');
        }

        $data = ['name' => 'new', 'status' => $status];
        $this->create('items/test', $data, $error, $message);
        $this->update('items/test/1', $data, $error, $message);
        $this->delete('items/test/1', $error, $message);
    }

    protected function doPermissions($error = false, $message = null)
    {
        $data = ['collection' => 'something', 'role' => 1];
        $this->create('permissions', $data, $error, $message);
        $this->update('permissions/2', ['create' => 1], $error, $message);
        $this->delete('permissions/2', $error, $message);
    }

    protected function doSettings($error = false, $message = null)
    {
        $data = [
            'scope' => 'scope-name',
            'key' => 'value',
            'value' => 1
        ];

        $this->create('settings', $data, $error, $message);
        $this->update('settings/1', ['value' => 'new-value'], $error, $message);
        $this->delete('settings/1', $error, $message);
    }

    protected function doUsers($error = false, $message = null)
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'password' => 'test',
            'email' => 'test@getdirectus.com'
        ];

        $this->create('users', $data, $error, $message);
        $this->update('users/4', ['password' => 'password'], $error, $message);
        $this->delete('users/4', $error, $message);
    }

    protected function doCollectionPresets($error = false, $message = null)
    {
        $data = [
            'role' => 1,
            'collection' => 'test2',
            'search_query' => 'name',
            'view_type' => 'tabular'
        ];

        $this->create('collection_presets', $data, $error, $message);
        $this->update('collection_presets/1', ['search_query' => 'id+name'], $error, $message);
        $this->delete('collection_presets/1', $error, $message);
    }

    protected function doCollections($error = false, $message = null)
    {
        $data = [
            'collection' => 'test2',
            'fields' => [
                [
                    'field' => 'id',
                    'interface' => 'primary_key',
                    'type' => 'integer',
                    'length' => 11,
                    'auto_increment' => true,
                    'datatype' => 'integer',
                    'primary_key' => true,
                ],
                [
                    'field' => 'name',
                    'interface' => 'text_input',
                    'type' => 'string',
                    'datatype' => 'varchar',
                    'length' => 255
                ],
            ]
        ];

        $this->create('collections', $data, $error, $message);
        $this->update('collections/test2', ['hidden' => 1], $error, $message);
        $this->delete('collections/test2', $error, $message);
    }

    protected function doFields($error = false, $message = null)
    {
        $data = [
            'field' => 'title',
            'interface' => 'text_input',
            'type' => 'string',
            'datatype' => 'varchar',
            'length' => 255,
        ];

        $this->create('fields/objects', $data, $error, $message);
        $this->update('fields/objects/title', ['required' => 1], $error, $message);
        $this->delete('fields/objects/title', $error, $message);
    }

    /**
     * @param $path
     * @param $data
     * @param null $error
     * @param null $message
     *
     * @return ResponseInterface
     */
    protected function create($path, $data, $error = null, $message = null)
    {
        return $this->request('post', $path, [
            'data' => $data,
            'error' => $error,
            'query' => ['comment' => $message]
        ]);
    }

    protected function update($path, $data, $error = null, $message = null)
    {
        $this->request('patch', $path, [
            'data' => $data,
            'error' => $error,
            'query' => ['comment' => $message]
        ]);
    }

    protected function delete($path, $error, $message)
    {
        $this->request('delete', $path, [
            'error' => $error,
            'query' => ['comment' => $message]
        ]);
    }

    protected function request($type, $path, array $options = [])
    {
        $error = isset($options['error']) ? $options['error'] : false;
        $extraQuery = isset($options['query']) ? $options['query'] : [];
        $query = array_merge($extraQuery, ['access_token' => 'token']);
        $data = isset($options['data']) ? $options['data'] : [];

        $function = sprintf('request_%s%s',
            $error ? 'error_' : '',
            $type
        );

        switch ($type) {
            case 'patch':
            case 'post':
                $args = [
                    $data,
                    ['query' => $query]
                ];
                break;
            case 'delete':
                $args = [
                    ['query' => $query]
                ];
                break;
            default:
                $args = [$query];
        }

        array_unshift($args, $path);

        $response = call_user_func_array($function, $args);

        if ($error) {
            assert_response_error($this, $response);
        } else if ($type === 'delete') {
            assert_response_empty($this, $response);
        } else {
            assert_response($this, $response);
        }

        return $response;
    }

    protected function setFlag($collection, $value, $status)
    {
        $data = [
            'collection' => $collection,
            'role' => 1,
            'status' => $status,
            'explain' => $value
        ];

        $options = [
            'query' => [
                'access_token' => 'token',
                'comment' => 'setting flag'
            ]
        ];

        if (isset($this->flags[$collection])) {
            request_patch('permissions/' . $this->flags[$collection], $data, $options);
        } else {
            $this->flags[$collection] = count($this->flags) + 1;
            request_post('permissions', $data, $options);
        }
    }

    protected function setFlagOn($collection, $level, $status = null)
    {
        $this->setFlag($collection, $level, $status);
    }

    protected function setFlagOnAlways($collection, $status = null)
    {
        $this->setFlagOn($collection, 'always', $status);
    }

    protected function setFlagOff($collection, $status = null)
    {
        $this->setFlag($collection, null, $status);
    }
}
