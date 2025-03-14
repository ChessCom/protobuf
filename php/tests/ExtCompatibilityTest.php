<?php

use Google\Protobuf as PB;

class ExtCompatibilityTest extends \PHPUnit\Framework\TestCase
{
    static $WELL_KNOWN_TYPES = [
        PB\Any::class,
        PB\Api::class,
        PB\BoolValue::class,
        PB\BytesValue::class,
        PB\DoubleValue::class,
        PB\Duration::class,
        PB\Enum::class,
        PB\EnumValue::class,
        PB\Field::class,
        PB\FieldMask::class,
        PB\Field\Cardinality::class,
        PB\Field\Kind::class,
        PB\FloatValue::class,
        PB\GPBEmpty::class,
        PB\Int32Value::class,
        PB\Int64Value::class,
        PB\ListValue::class,
        PB\Method::class,
        PB\Mixin::class,
        PB\NullValue::class,
        PB\Option::class,
        PB\SourceContext::class,
        PB\StringValue::class,
        PB\Struct::class,
        PB\Syntax::class,
        PB\Timestamp::class,
        PB\Type::class,
        PB\UInt32Value::class,
        PB\UInt64Value::class,
        PB\Value::class,
        PB\RepeatedField::class,
    ];

    static $DESCRIPTOR_TYPES = [
        PB\OneofDescriptor::class,
        PB\FieldDescriptor::class,
        PB\EnumValueDescriptor::class,
        PB\EnumDescriptor::class,
        PB\Descriptor::class,
        PB\DescriptorPool::class,
    ];

    // List of entries that are intentionally incompatible between PHP and C extension.
    // @TODO - These are likely actualy intentional differences, More likely the 2 implementations deviated over time.
    static $IGNORE = [
        // For the C ext Google\Protobuf\Internal\DescriptorPool and Google\Protobuf\DescriptorPool are not a separate object
        [
            'classes' => [PB\Internal\DescriptorPool::class],
            'ignore_methods' => true
        ],
        [
            'classes' => [PB\DescriptorPool::class],
            'ignore_methods' => [
                'getEnumDescriptorByClassName',
                'getDescriptorByClassName',
                'getDescriptorByProtoName',
                'internalAddGeneratedFile',
                'getGeneratedPool'
            ],
        ],
        [
            'classes' => [
                PB\EnumDescriptor::class,
                PB\FieldDescriptor::class,
                PB\OneofDescriptor::class
            ],
            'ignore_methods' => [
                'getDescriptorByProtoName',
                'internalAddGeneratedFile',
                'getPublicDescriptor',
                'hasOptionalKeyword',
                'isSynthetic'
            ]
        ],
        // Classes with incompatible parent classes
        [
            'classes' => [
                // php = 'Google\Protobuf\Internal\AnyBase'
                // ext = 'Google\Protobuf\Internal\Message
                PB\Any::class,

                // php = 'Google\Protobuf\Internal\AnyBase'
                // ext = 'Google\Protobuf\Internal\Message
                PB\Timestamp::class,
            ],
            'ignore_parent' => true
        ],
        // Data structures missing some methods..
        [
            'classes' => [
                PB\RepeatedField::class,
                PB\Internal\MapField::class,
            ],
            'ignore_methods' => [
                'getType',
                'getClass',
                'append',

                'checkKey',
                'getKeyType',
                'getValueType',
                'getValueClass',
                'getLegacyValueClass'
            ]
        ],
        // Not implemented in the C extension
        [
            'classes' => [
                PB\Internal\GPBUtil::class
            ],
            'ignore_methods' => true
        ],
        [
            'classes' => [
                PB\Internal\Message::class,
            ],
            'ignore_methods' => [
                'skipField',
                'existField',
                'appendHelper',
                'defaultValue',
                'fieldByteSize',
                'kvUpdateHelper',
                'writeWrapperValue',
                'fieldJsonByteSize',
                'initWithDescriptor',
                'parseFieldFromStream',
                'initWithGeneratedPool',
                'fieldDataOnlyByteSize',
                'mergeFromArrayJsonImpl',
                'normalizeToMessageType',
                'serializeFieldToStream',
                'fieldDataOnlyJsonByteSize',
                'parseFieldFromStreamNoTag',
                'serializeMapFieldToStream',
                'serializeFieldToJsonStream',
                'convertJsonValueToProtoValue',
                'repeatedFieldDataOnlyByteSize',
                'serializeRepeatedFieldToStream',
                'serializeSingularFieldToStream',
                'normalizeArrayElementsToMessageType',
            ]
        ],
        // SourceContex
        [
            'classes' => [
                PB\Api::class,
                PB\Enum::class,
                PB\Type::class,
            ],
            'ignore_methods' => ['clearSourceContext', 'hasSourceContext']
        ],
        [
            'classes' => [
                PB\Option::class,
            ],
            'ignore_methods' => ['clearValue', 'hasValue']
        ],
        // see https://github.com/protocolbuffers/protobuf/pull/20636
        [
            'classes' => [
                PB\Value::class,
            ],
            'ignore_methods' => [
                'hasBoolValue',
                'hasListValue',
                'hasNullValue',
                'hasNumberValue',
                'hasStringValue',
                'hasStructValue'
            ]
        ],
        [
            'wellKnownClasses' => true, // All well known types
            'classes' => [
                // Everithing in Google\Protobuf\Internal
                'Google\Protobuf\Internal',
            ],
            'ignore_methods' => [
                'byteSize',
                'jsonByteSize',
                'mergeFromArray',
                'parseFromStream',
                'getGeneratedPool',
                'serializeToStream',
                'mergeFromJsonArray',
                'parseFromJsonStream',
                'serializeToJsonStream',
            ]
        ],
    ];

    public function setUp(): void
    {
        $this->classMetadataPhpFile = getenv('CLASS_METADATA_PHP_FILE') ?: dirname(__DIR__) . '/tmp/class-metadata/src-php.json';
        $this->classMetadataExtFile = getenv('CLASS_METADATA_EXT_FILE') ?: dirname(__DIR__) . '/tmp/class-metadata/src-ext.json';

        if (!file_exists($this->classMetadataPhpFile)) {
            $this->markTestSkipped(
<<<EOD
Class metadata file '$this->classMetadataPhpFile' not found.
Run the following command to generate:

$ composer generate_class_metadata_php
EOD
            );
        }

        if (!file_exists($this->classMetadataExtFile)) {
            $this->markTestSkipped(
<<<EOD
Class metadata file '$this->classMetadataExtFile' not found.
Run the following command to generate:

$ composer generate_class_metadata_ext
EOD
            );
        }
    }

    public function testWellKnownTypesMetadata()
    {
        $expectedClasses = array_merge(self::$WELL_KNOWN_TYPES, self::$DESCRIPTOR_TYPES);
        $phpMetadata = json_decode(file_get_contents($this->classMetadataPhpFile), true);
        $extMetadata = json_decode(file_get_contents($this->classMetadataExtFile), true);

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

        foreach ($expectedClasses as $className) {
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
        $this->assertArrayHasKey('extends', $phpClassMetadata);
        $this->assertArrayHasKey('is_final', $phpClassMetadata);
        $this->assertArrayHasKey('namespace', $phpClassMetadata);

        $this->assertArrayHasKey('name', $extClassMetadata);
        $this->assertArrayHasKey('methods', $extClassMetadata);
        $this->assertArrayHasKey('extends', $extClassMetadata);
        $this->assertArrayHasKey('is_final', $extClassMetadata);
        $this->assertArrayHasKey('namespace', $extClassMetadata);

        $this->assertEquals($extClassMetadata['name'], $extClassMetadata['name']);
        $this->assertEquals($phpClassMetadata['namespace'], $extClassMetadata['namespace'], "Inconsistent namespace for '$className'");
        $this->assertEquals($phpClassMetadata['is_final'], $extClassMetadata['is_final'], "Inconsistent final modifier for '$className'");

        if (!isset(self::$IGNORE[$className]['ignore_parent']) || !self::$IGNORE[$className]['ignore_parent']) {
            $this->assertEquals($phpClassMetadata['extends'], $extClassMetadata['extends'], "Inconsistent parent class for '$className'");
        }

        $phpMethods = $this->filterClassMethods($className, array_keys($phpClassMetadata['methods']));
        $extMethods = $this->filterClassMethods($className, array_keys($extClassMetadata['methods']));

        $this->assertEqualsCanonicalizing(
            $phpMethods,
            $extMethods,
            sprintf(
<<<'EOD'
Inconsistent methods for class '%s':
    PHP:
        count=%d,
        missing=[%s]
        method=[%s],
    EXT:
        count=%d,
        missing=[%s]
        method=[%s],
EOD,
                $className,
                count($phpMethods), implode(', ', array_diff($extMethods, $extMethods)), implode(', ', $phpMethods),
                count($extMethods), implode(', ', array_diff($phpMethods, $extMethods)), implode(', ', $extMethods)
            )
        );
    }

    private function assertGeneratedClassMetadataMethod($className, $method, $phpMethodMetadata, $extMethodMetadata)
    {
        $this->assertArrayHasKey('name', $phpMethodMetadata);
        $this->assertArrayHasKey('modifiers', $phpMethodMetadata);
        $this->assertArrayHasKey('parameters', $phpMethodMetadata);

        $this->assertArrayHasKey('name', $extMethodMetadata);
        $this->assertArrayHasKey('modifiers', $extMethodMetadata);
        $this->assertArrayHasKey('parameters', $extMethodMetadata);

        $this->assertEquals(
            $extMethodMetadata['name'],
            $extMethodMetadata['name'],
            "Inconsistent name '$className::$method'"
        );

        $this->assertEquals(
            $phpMethodMetadata['modifiers'],
            $extMethodMetadata['modifiers'],
            "Inconsistent modifiers for '$className::$method'"
        );

        $this->assertEquals(
            count($phpMethodMetadata['parameters']),
            count($extMethodMetadata['parameters']),
            "Inconsistent final parameters count for '$className::$method'"
        );

        for ($i = 0; $i < count($phpMethodMetadata['parameters']); $i++) {
            $phpParameter = $phpMethodMetadata['parameters'][$i];
            $extParameter = $extMethodMetadata['parameters'][$i];

            $this->assertEquals(
                $phpParameter['name'],
                $extParameter['name'],
                "Inconsistent parameter name for '$className::$method'"
            );

            $this->assertEquals(
                $phpParameter['type'],
                $extParameter['type'],
                "Inconsistent parameter type for '$className::$method'"
            );

            $this->assertEquals(
                $phpParameter['is_optional'],
                $extParameter['is_optional'],
                "Inconsistent parameter optional flag for '$className::$method'"
            );
        }
    }

    private function filterClassMethods($className, array $methods)
    {
        $result = array_filter($methods, function ($method) use ($className) {
            // Ignore magic methods.
            if (strpos($method, '__') === 0) {
                return false;
            }

            if ($rule = $this->findIgnoreRule($className)) {

                if ($className == 'Google\Protobuf\Internal\Message')
                {
                    var_dump([
                        'class' => $className,
                        'method' => $method,
                        'rule' => $rule
                    ]);
                }

                if (isset($rule['ignore_methods']) && $rule['ignore_methods'] === true) {
                    return false;
                }

                if (isset($rule['ignore_methods']) && is_array($rule['ignore_methods']) && in_array($method, $rule['ignore_methods'])) {
                    return false;
                }
            }

            return true;
        });

        sort($result);

        return $result;
    }

    private function findIgnoreRule($className)
    {
        foreach (self::$IGNORE as $rule) {
            foreach ($rule['classes'] as $ignoreClass) {
                if ($className === $ignoreClass || strpos($className, $ignoreClass) === 0) {
                    return $rule;
                }

                if (isset($rule['wellKnownClasses']) && $rule['wellKnownClasses'] && in_array($className, self::$WELL_KNOWN_TYPES)) {
                    return $rule;
                }
            }
        }

        return null;
    }
}
