<?php

class ExtCompatibilityTest extends \PHPUnit\Framework\TestCase
{
    const WELL_KNOWN_TYPES = [
        "Google\Protobuf\Any",
        "Google\Protobuf\Api",
        "Google\Protobuf\BoolValue",
        "Google\Protobuf\BytesValue",
        "Google\Protobuf\DoubleValue",
        "Google\Protobuf\Duration",
        "Google\Protobuf\Enum",
        "Google\Protobuf\EnumValue",
        "Google\Protobuf\Field",
        "Google\Protobuf\FieldMask",
        "Google\Protobuf\Field\Cardinality",
        "Google\Protobuf\Field\Kind",
        "Google\Protobuf\FloatValue",
        "Google\Protobuf\GPBEmpty",
        "Google\Protobuf\Int32Value",
        "Google\Protobuf\Int64Value",
        "Google\Protobuf\ListValue",
        "Google\Protobuf\Method",
        "Google\Protobuf\Mixin",
        "Google\Protobuf\NullValue",
        "Google\Protobuf\Option",
        "Google\Protobuf\SourceContext",
        "Google\Protobuf\StringValue",
        "Google\Protobuf\Struct",
        "Google\Protobuf\Syntax",
        "Google\Protobuf\Timestamp",
        "Google\Protobuf\Type",
        "Google\Protobuf\UInt32Value",
        "Google\Protobuf\UInt64Value",
        "Google\Protobuf\Value",
        "Google\Protobuf\RepeatedField",
    ];

    const DESCRIPTOR_TYPES = [
        "Google\Protobuf\OneofDescriptor",
        "Google\Protobuf\FieldDescriptor",
        "Google\Protobuf\EnumValueDescriptor",
        "Google\Protobuf\EnumDescriptor",
        "Google\Protobuf\Descriptor",
        "Google\Protobuf\DescriptorPool",
    ];

    // List of entries that are intentionally incompatible between PHP and C extension.
    // @TODO - These are likely actualy intentional differences, More likely the 2 implementations deviated over time.
    static $IGNORE = [
        // ##################################
        // #### Google\Protobuf ####
        // ##################################

        // For the C extension, Google\Protobuf\Internal\DescriptorPool is not a separate object ?
        '^Google\\\\Protobuf\\\\DescriptorPool::getDescriptorByProtoName$',
        '^Google\\\\Protobuf\\\\DescriptorPool::internalAddGeneratedFile$',

        // Not compatible with the C extension, probably needs to be fixed.
        '^Google\\\\Protobuf\\\\EnumDescriptor::getPublicDescriptor$',
        '^Google\\\\Protobuf\\\\FieldDescriptor::getPublicDescriptor$',
        '^Google\\\\Protobuf\\\\FieldDescriptor::hasOptionalKeyword$',

        '^Google\\\\Protobuf\\\\OneofDescriptor::getPublicDescriptor$',
        '^Google\\\\Protobuf\\\\OneofDescriptor::isSynthetic$',

        '^Google\\\\Protobuf\\\\RepeatedField::getType$',
        '^Google\\\\Protobuf\\\\RepeatedField::getClass$',
        '^Google\\\\Protobuf\\\\RepeatedField::append$',

        // ##################################
        // #### Google\Protobuf\Internal ####
        // ##################################

        // Internal not compatible
        '^Google\\\\Protobuf\\\\Internal\\\\DescriptorPool::.*$',

        '^Google\\\\Protobuf\\\\Internal\\\\[^:]+::byteSize$',
        '^Google\\\\Protobuf\\\\Internal\\\\[^:]+::jsonByteSize$',
        '^Google\\\\Protobuf\\\\Internal\\\\[^:]+::mergeFromArray$',
        '^Google\\\\Protobuf\\\\Internal\\\\[^:]+::parseFromStream$',
        '^Google\\\\Protobuf\\\\Internal\\\\[^:]+::getGeneratedPool$',
        '^Google\\\\Protobuf\\\\Internal\\\\[^:]+::serializeToStream$',
        '^Google\\\\Protobuf\\\\Internal\\\\[^:]+::mergeFromJsonArray$',
        '^Google\\\\Protobuf\\\\Internal\\\\[^:]+::parseFromJsonStream$',
        '^Google\\\\Protobuf\\\\Internal\\\\[^:]+::serializeToJsonStream$',

        '^Google\\\\Protobuf\\\\Internal\\\\Message::appendHelper$',
        '^Google\\\\Protobuf\\\\Internal\\\\Message::convertJsonValueToProtoValue$',
        '^Google\\\\Protobuf\\\\Internal\\\\Message::defaultValue$',
        '^Google\\\\Protobuf\\\\Internal\\\\Message::existField$',
        '^Google\\\\Protobuf\\\\Internal\\\\Message::fieldByteSize$',
        '^Google\\\\Protobuf\\\\Internal\\\\Message::fieldDataOnlyByteSize$',
        '^Google\\\\Protobuf\\\\Internal\\\\Message::fieldDataOnlyJsonByteSize$',
        '^Google\\\\Protobuf\\\\Internal\\\\Message::fieldJsonByteSize$',
        '^Google\\\\Protobuf\\\\Internal\\\\Message::initWithDescriptor$',
        '^Google\\\\Protobuf\\\\Internal\\\\Message::initWithGeneratedPool$',
        '^Google\\\\Protobuf\\\\Internal\\\\Message::kvUpdateHelper$',
        '^Google\\\\Protobuf\\\\Internal\\\\Message::mergeFromArrayJsonImpl$',
        '^Google\\\\Protobuf\\\\Internal\\\\Message::normalizeArrayElementsToMessageType$',
        '^Google\\\\Protobuf\\\\Internal\\\\Message::normalizeToMessageType$',
        '^Google\\\\Protobuf\\\\Internal\\\\Message::parseFieldFromStream$',
        '^Google\\\\Protobuf\\\\Internal\\\\Message::parseFieldFromStreamNoTag$',
        '^Google\\\\Protobuf\\\\Internal\\\\Message::repeatedFieldDataOnlyByteSize$',
        '^Google\\\\Protobuf\\\\Internal\\\\Message::serializeFieldToJsonStream$',
        '^Google\\\\Protobuf\\\\Internal\\\\Message::serializeFieldToStream$',
        '^Google\\\\Protobuf\\\\Internal\\\\Message::serializeMapFieldToStream$',
        '^Google\\\\Protobuf\\\\Internal\\\\Message::serializeRepeatedFieldToStream$',
        '^Google\\\\Protobuf\\\\Internal\\\\Message::serializeSingularFieldToStream$',
        '^Google\\\\Protobuf\\\\Internal\\\\Message::skipField$',
        '^Google\\\\Protobuf\\\\Internal\\\\Message::writeWrapperValue$',

        '^Google\\\\Protobuf\\\\Internal\\\\MapField::checkKey$',
        '^Google\\\\Protobuf\\\\Internal\\\\MapField::getKeyType$',
        '^Google\\\\Protobuf\\\\Internal\\\\MapField::getValueType$',
        '^Google\\\\Protobuf\\\\Internal\\\\MapField::getValueClass$',
        '^Google\\\\Protobuf\\\\Internal\\\\MapField::getLegacyValueClass$',

        // Not implemented by the C extension.
        'Google\\\\Protobuf\\\\Internal\\\\GPBUtil::.*$',
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
        $expectedClasses = array_merge(self::WELL_KNOWN_TYPES, self::DESCRIPTOR_TYPES);
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
        $this->assertEqualsCanonicalizing($phpClassMetadata['extends'], $extClassMetadata['extends'], "Inconsistent parent class for '$className'");

        $phpMethods = $this->filterCLassMethods($className, array_keys($phpClassMetadata['methods']));
        $extMethods = $this->filterCLassMethods($className, array_keys($extClassMetadata['methods']));

        $this->assertEqualsCanonicalizing(
            $phpMethods,
            $extMethods,
            sprintf(
                "Methods mismatch for class %s: \n\nPHP (%d): %s, \nEXT (%d): %s \n",
                $className,
                count($phpMethods), implode(', ', $phpMethods),
                count($extMethods), implode(', ', $extMethods)
            )
        );
    }

    private function filterCLassMethods($className, array $methods)
    {
        $result = array_filter($methods, function ($method) use ($className) {
            // Ignore magic methods.
            if (strpos($method, '__') === 0) {
                return false;
            }

            // Ignore methods.
            foreach (self::$IGNORE as $pattern) {
                if (preg_match("/$pattern/", "$className::$method")) {
                    return false;
                }
            }

            return true;
        });

        sort($result);

        return $result;
    }
}
