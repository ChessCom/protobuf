<?php

require_once('test_base.php');

class ExtCompatibilityTest extends TestBase
{
    const WELL_KNOWN_TYPES_METADATA_PHP_FILE = __DIR__ . '/../tmp/class-metadata/well-known-types-php.json';
    const WELL_KNOWN_TYPES_METADATA_EXT_FILE = __DIR__ . '/../tmp/class-metadata/well-known-types-ext.json';

    const WELL_KNOWN_TYPES = [
        'Google\Protobuf\Any',
        'Google\Protobuf\Api',
        'Google\Protobuf\BoolValue',
        'Google\Protobuf\BytesValue',
        'Google\Protobuf\DoubleValue',
        'Google\Protobuf\Duration',
        'Google\Protobuf\Enum',
        'Google\Protobuf\EnumValue',
        'Google\Protobuf\Field',
        'Google\Protobuf\FieldMask',
        'Google\Protobuf\Field\Cardinality',
        'Google\Protobuf\Field\Kind',
        'Google\Protobuf\FloatValue',
        'Google\Protobuf\GPBEmpty',
        'Google\Protobuf\Int32Value',
        'Google\Protobuf\Int64Value',
        'Google\Protobuf\ListValue',
        'Google\Protobuf\Method',
        'Google\Protobuf\Mixin',
        'Google\Protobuf\NullValue',
        'Google\Protobuf\Option',
        'Google\Protobuf\SourceContext',
        'Google\Protobuf\StringValue',
        'Google\Protobuf\Struct',
        'Google\Protobuf\Syntax',
        'Google\Protobuf\Timestamp',
        'Google\Protobuf\Type',
        'Google\Protobuf\UInt32Value',
        'Google\Protobuf\UInt64Value',
        'Google\Protobuf\Value',
    ];

    public function setUp(): void
    {
        if (!file_exists(self::WELL_KNOWN_TYPES_METADATA_PHP_FILE) || !file_exists(self::WELL_KNOWN_TYPES_METADATA_EXT_FILE)) {
            $this->markTestSkipped('Metadata files not found run `composer generate_class_metadata_php && composer generate_class_metadata_ext`');
        }
    }

    public function testWellKnownTypesMetadata()
    {
        $phpMetadata = json_decode(file_get_contents(static::WELL_KNOWN_TYPES_METADATA_PHP_FILE), true);
        $extMetadata = json_decode(file_get_contents(static::WELL_KNOWN_TYPES_METADATA_EXT_FILE), true);

        $this->assertArrayHasKey('classes', $phpMetadata);
        $this->assertArrayHasKey('extensions', $phpMetadata);
        $this->assertNotContains('protobuf', $phpMetadata['extensions']);

        $this->assertArrayHasKey('classes', $extMetadata);
        $this->assertArrayHasKey('extensions', $extMetadata);
        $this->assertContains('protobuf', $extMetadata['extensions']);

        $phpClasses = array_keys($phpMetadata['classes']);
        $extClasses = array_keys($extMetadata['classes']);
        $allClasses = array_unique(array_merge($phpClasses, $extClasses));

        $this->assertEqualsCanonicalizing($phpClasses, $extClasses);

        foreach (self::WELL_KNOWN_TYPES as $className) {
            $this->assertContains($className, $phpClasses, "Class $className not found in PHP metadata");
            $this->assertContains($className, $extClasses, "Class $className not found in Ext metadata");
        }

        foreach ($allClasses as $className) {
            $extClassMetadata = $extMetadata['classes'][$className];
            $phpClassMetadata = $phpMetadata['classes'][$className];

            $this->assertGeneratedClassMetadata($className, $phpClassMetadata, $extClassMetadata);
        }
    }

    private function assertGeneratedClassMetadata($className, $phpClassMetadata, $extClassMetadata)
    {
        $this->assertArrayHasKey('name', $phpClassMetadata);
        $this->assertArrayHasKey('methods', $phpClassMetadata);
        $this->assertArrayHasKey('namespace', $phpClassMetadata);

        $this->assertArrayHasKey('name', $extClassMetadata);
        $this->assertArrayHasKey('methods', $extClassMetadata);
        $this->assertArrayHasKey('namespace', $extClassMetadata);

        $this->assertEquals($extClassMetadata['name'], $extClassMetadata['name']);
        $this->assertEquals($phpClassMetadata['namespace'], $extClassMetadata['namespace'], "Namespace mismatch for class $className");

        $phpMethods = $this->filterMagicMethods(array_keys($phpClassMetadata['methods']));
        $extMethods = $this->filterMagicMethods(array_keys($extClassMetadata['methods']));

        $this->assertEqualsCanonicalizing($phpMethods, $extMethods, "Method names mismatch for class $className");

        //var_dump($phpMethods, $extMethods);
    }

    private function filterMagicMethods(array $methods)
    {
        return array_filter($methods, function ($method) {
            return strpos($method, '__') !== 0;
        });
    }
}
