<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Sentry;

use FriendsOfHyperf\Sentry\Util\Carrier;
use Sentry\Tracing\Span;
use Sentry\Tracing\SpanContext;

beforeEach(function () {
    $this->testData = [
        'sentry-trace' => '12345678901234567890123456789012-1234567890123456-1',
        'baggage' => 'sentry-trace_id=12345678901234567890123456789012,sentry-public_key=public_key',
        'traceparent' => '00-12345678901234567890123456789012-1234567890123456-01',
        'custom_field' => 'custom_value',
        'publish_time' => 1234567890.123,
    ];
});

describe('Carrier Construction', function () {
    test('can be constructed with empty data', function () {
        $carrier = new Carrier();
        expect($carrier->toArray())->toBe([]);
    });

    test('can be constructed with initial data', function () {
        $carrier = new Carrier($this->testData);
        expect($carrier->toArray())->toBe($this->testData);
    });

    test('can be created from array', function () {
        $carrier = Carrier::fromArray($this->testData);
        expect($carrier->toArray())->toBe($this->testData);
    });

    test('can be created from valid JSON', function () {
        $json = json_encode($this->testData);
        $carrier = Carrier::fromJson($json);
        expect($carrier->toArray())->toBe($this->testData);
    });

    test('handles invalid JSON gracefully', function () {
        $carrier = Carrier::fromJson('invalid json');
        expect($carrier->toArray())->toBe([]);
    });

    test('handles non-array JSON gracefully', function () {
        $carrier = Carrier::fromJson('"string value"');
        expect($carrier->toArray())->toBe([]);
    });

    test('handles empty JSON', function () {
        $carrier = Carrier::fromJson('');
        expect($carrier->toArray())->toBe([]);
    });

    test('can be created from Span', function () {
        $spanContext = new SpanContext();
        $span = new Span($spanContext);

        $carrier = Carrier::fromSpan($span);
        $data = $carrier->toArray();

        expect($data)->toHaveKeys(['sentry-trace', 'baggage']);
        expect($data['sentry-trace'])->toBeString();
        expect($data['baggage'])->toBeString();
    });
});

describe('Data Manipulation', function () {
    test('can add data using with method', function () {
        $carrier = new Carrier(['existing' => 'value']);
        $newCarrier = $carrier->with(['new' => 'data']);

        expect($carrier->toArray())->toBe(['existing' => 'value']);
        expect($newCarrier->toArray())->toBe(['existing' => 'value', 'new' => 'data']);
    });

    test('with method merges data correctly', function () {
        $carrier = new Carrier(['key1' => 'value1', 'key2' => 'value2']);
        $newCarrier = $carrier->with(['key2' => 'updated', 'key3' => 'new']);

        expect($newCarrier->toArray())->toBe([
            'key1' => 'value1',
            'key2' => 'updated',
            'key3' => 'new',
        ]);
    });
});

describe('Data Access', function () {
    test('can check if key exists', function () {
        $carrier = new Carrier($this->testData);

        expect($carrier->has('sentry-trace'))->toBeTrue();
        expect($carrier->has('nonexistent'))->toBeFalse();
    });

    test('can get value by key', function () {
        $carrier = new Carrier($this->testData);

        expect($carrier->get('custom_field'))->toBe('custom_value');
        expect($carrier->get('nonexistent'))->toBeNull();
        expect($carrier->get('nonexistent', 'default'))->toBe('default');
    });

    test('getSentryTrace returns correct value', function () {
        $carrier = new Carrier($this->testData);
        expect($carrier->getSentryTrace())->toBe('12345678901234567890123456789012-1234567890123456-1');
    });

    test('getSentryTrace returns empty string when not set', function () {
        $carrier = new Carrier();
        expect($carrier->getSentryTrace())->toBe('');
    });

    test('getBaggage returns correct value', function () {
        $carrier = new Carrier($this->testData);
        expect($carrier->getBaggage())->toBe('sentry-trace_id=12345678901234567890123456789012,sentry-public_key=public_key');
    });

    test('getBaggage returns empty string when not set', function () {
        $carrier = new Carrier();
        expect($carrier->getBaggage())->toBe('');
    });

    test('getTraceparent returns correct value', function () {
        $carrier = new Carrier($this->testData);
        expect($carrier->getTraceparent())->toBe('00-12345678901234567890123456789012-1234567890123456-01');
    });

    test('getTraceparent returns empty string when not set', function () {
        $carrier = new Carrier();
        expect($carrier->getTraceparent())->toBe('');
    });
});

describe('Serialization', function () {
    test('implements JsonSerializable', function () {
        $carrier = new Carrier($this->testData);
        expect($carrier->jsonSerialize())->toBe($this->testData);
    });

    test('toArray returns correct data', function () {
        $carrier = new Carrier($this->testData);
        expect($carrier->toArray())->toBe($this->testData);
    });

    test('toJson returns valid JSON string', function () {
        $carrier = new Carrier($this->testData);
        $json = $carrier->toJson();

        expect($json)->toBeString();
        expect(json_decode($json, true))->toBe($this->testData);
    });

    test('toJson with custom options', function () {
        $data = ['key' => 'value with unicode: 中文'];
        $carrier = new Carrier($data);
        $json = $carrier->toJson(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        expect($json)->toContain('中文');
        expect($json)->toContain("\n"); // Pretty print adds newlines
    });

    test('toJson handles encoding errors gracefully', function () {
        // Create data that cannot be JSON encoded
        $resource = fopen('php://memory', 'r');
        $carrier = new Carrier(['resource' => $resource]);
        fclose($resource);

        $json = $carrier->toJson();
        expect($json)->toBe('{}');
    });

    test('__toString returns JSON representation', function () {
        $carrier = new Carrier($this->testData);
        $string = (string) $carrier;

        expect($string)->toBe($carrier->toJson());
        expect(json_decode($string, true))->toBe($this->testData);
    });

    test('can be JSON encoded directly', function () {
        $carrier = new Carrier($this->testData);
        $json = json_encode($carrier);

        expect(json_decode($json, true))->toBe($this->testData);
    });
});

describe('Edge Cases and Error Handling', function () {
    test('handles null values in data', function () {
        $data = ['key' => null, 'other' => 'value'];
        $carrier = new Carrier($data);

        expect($carrier->get('key'))->toBeNull();
        expect($carrier->has('key'))->toBeFalse(); // isset() returns false for null values
        expect($carrier->toArray())->toBe($data);
    });

    test('handles boolean and numeric values', function () {
        $data = [
            'bool_true' => true,
            'bool_false' => false,
            'int' => 42,
            'float' => 3.14,
        ];
        $carrier = new Carrier($data);

        expect($carrier->get('bool_true'))->toBeTrue();
        expect($carrier->get('bool_false'))->toBeFalse();
        expect($carrier->get('int'))->toBe(42);
        expect($carrier->get('float'))->toBe(3.14);
    });

    test('handles array and object values', function () {
        $data = [
            'array' => ['nested', 'array'],
            'object' => (object) ['property' => 'value'],
        ];
        $carrier = new Carrier($data);

        expect($carrier->get('array'))->toBe(['nested', 'array']);
        expect($carrier->get('object'))->toBeInstanceOf('stdClass');
    });

    test('immutability of original carrier in with method', function () {
        $original = new Carrier(['original' => 'data']);
        $modified = $original->with(['new' => 'data']);

        expect($original === $modified)->toBeFalse();
        expect($original->has('new'))->toBeFalse();
        expect($modified->has('new'))->toBeTrue();
    });

    test('deep cloning behavior', function () {
        $data = ['nested' => ['array' => 'value']];
        $original = new Carrier($data);
        $modified = $original->with(['other' => 'data']);

        // Modify the nested array in the modified carrier
        $modifiedData = $modified->toArray();
        $modifiedData['nested']['array'] = 'changed';

        // Original should remain unchanged
        expect($original->get('nested'))->toBe(['array' => 'value']);
    });
});

describe('Integration Tests', function () {
    test('round trip JSON serialization', function () {
        $original = new Carrier($this->testData);
        $json = $original->toJson();
        $restored = Carrier::fromJson($json);

        expect($restored->toArray())->toBe($original->toArray());
    });

    test('chaining operations', function () {
        $carrier = (new Carrier())
            ->with(['step1' => 'data1'])
            ->with(['step2' => 'data2'])
            ->with(['step1' => 'updated']); // Override step1

        expect($carrier->toArray())->toBe([
            'step1' => 'updated',
            'step2' => 'data2',
        ]);
    });

    test('works with Span integration', function () {
        $spanContext = new SpanContext();
        $span = new Span($spanContext);

        $carrier = Carrier::fromSpan($span)
            ->with(['custom' => 'data']);

        $data = $carrier->toArray();
        expect($data)->toHaveKey('custom');
        expect($data)->toHaveKeys(['sentry-trace', 'baggage']);

        // Test round trip
        $json = $carrier->toJson();
        $restored = Carrier::fromJson($json);
        expect($restored->getSentryTrace())->toBe($carrier->getSentryTrace());
        expect($restored->get('custom'))->toBe('data');
    });
});
