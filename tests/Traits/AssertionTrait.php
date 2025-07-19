<?php

declare(strict_types=1);

namespace RenalTales\Tests\Traits;

/**
 * Assertion trait for custom test assertions
 */
trait AssertionTrait
{
    /**
     * Assert that a value is an array with specific keys
     */
    protected function assertArrayHasKeys(array $expectedKeys, array $array, string $message = ''): void
    {
        $this->assertIsArray($array);

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $array, $message ?: "Array should have key '{$key}'");
        }
    }

    /**
     * Assert that a value is an array without specific keys
     */
    protected function assertArrayNotHasKeys(array $keys, array $array, string $message = ''): void
    {
        $this->assertIsArray($array);

        foreach ($keys as $key) {
            $this->assertArrayNotHasKey($key, $array, $message ?: "Array should not have key '{$key}'");
        }
    }

    /**
     * Assert that a string contains all substrings
     */
    protected function assertStringContainsAll(array $needles, string $haystack, string $message = ''): void
    {
        foreach ($needles as $needle) {
            $this->assertStringContainsString($needle, $haystack, $message ?: "String should contain '{$needle}'");
        }
    }

    /**
     * Assert that a string does not contain any of the substrings
     */
    protected function assertStringContainsNone(array $needles, string $haystack, string $message = ''): void
    {
        foreach ($needles as $needle) {
            $this->assertStringNotContainsString($needle, $haystack, $message ?: "String should not contain '{$needle}'");
        }
    }

    /**
     * Assert that an object has specific properties
     */
    protected function assertObjectHasProperties(array $properties, object $object, string $message = ''): void
    {
        foreach ($properties as $property) {
            $this->assertTrue(
                property_exists($object, $property),
                $message ?: "Object should have property '{$property}'"
            );
        }
    }

    /**
     * Assert that an object has specific methods
     */
    protected function assertObjectHasMethods(array $methods, object $object, string $message = ''): void
    {
        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists($object, $method),
                $message ?: "Object should have method '{$method}'"
            );
        }
    }

    /**
     * Assert that a value is a valid email address
     */
    protected function assertValidEmail(string $email, string $message = ''): void
    {
        $this->assertTrue(
            filter_var($email, FILTER_VALIDATE_EMAIL) !== false,
            $message ?: "'{$email}' is not a valid email address"
        );
    }

    /**
     * Assert that a value is a valid URL
     */
    protected function assertValidUrl(string $url, string $message = ''): void
    {
        $this->assertTrue(
            filter_var($url, FILTER_VALIDATE_URL) !== false,
            $message ?: "'{$url}' is not a valid URL"
        );
    }

    /**
     * Assert that a value is a valid JSON string
     */
    protected function assertValidJson(string $json, string $message = ''): void
    {
        json_decode($json);
        $this->assertEquals(
            JSON_ERROR_NONE,
            json_last_error(),
            $message ?: "'{$json}' is not valid JSON"
        );
    }

    /**
     * Assert that a value is a valid timestamp
     */
    protected function assertValidTimestamp(int $timestamp, string $message = ''): void
    {
        $this->assertGreaterThan(
            0,
            $timestamp,
            $message ?: "'{$timestamp}' is not a valid timestamp"
        );

        $this->assertLessThanOrEqual(
            time(),
            $timestamp,
            $message ?: "'{$timestamp}' is a future timestamp"
        );
    }

    /**
     * Assert that a value is a valid date string
     */
    protected function assertValidDate(string $date, string $format = 'Y-m-d', string $message = ''): void
    {
        $dateTime = \DateTime::createFromFormat($format, $date);
        $this->assertInstanceOf(
            \DateTime::class,
            $dateTime,
            $message ?: "'{$date}' is not a valid date in format '{$format}'"
        );

        $this->assertEquals(
            $date,
            $dateTime->format($format),
            $message ?: "'{$date}' is not a valid date in format '{$format}'"
        );
    }

    /**
     * Assert that a value is within a range
     */
    protected function assertInRange($value, $min, $max, string $message = ''): void
    {
        $this->assertGreaterThanOrEqual(
            $min,
            $value,
            $message ?: "Value '{$value}' should be >= '{$min}'"
        );

        $this->assertLessThanOrEqual(
            $max,
            $value,
            $message ?: "Value '{$value}' should be <= '{$max}'"
        );
    }

    /**
     * Assert that a file exists and is readable
     */
    protected function assertFileExistsAndReadable(string $filename, string $message = ''): void
    {
        $this->assertFileExists($filename, $message ?: "File '{$filename}' should exist");
        $this->assertTrue(
            is_readable($filename),
            $message ?: "File '{$filename}' should be readable"
        );
    }

    /**
     * Assert that a file exists and is writable
     */
    protected function assertFileExistsAndWritable(string $filename, string $message = ''): void
    {
        $this->assertFileExists($filename, $message ?: "File '{$filename}' should exist");
        $this->assertTrue(
            is_writable($filename),
            $message ?: "File '{$filename}' should be writable"
        );
    }

    /**
     * Assert that a directory exists and is writable
     */
    protected function assertDirectoryExistsAndWritable(string $directory, string $message = ''): void
    {
        $this->assertDirectoryExists($directory, $message ?: "Directory '{$directory}' should exist");
        $this->assertTrue(
            is_writable($directory),
            $message ?: "Directory '{$directory}' should be writable"
        );
    }

    /**
     * Assert that an exception is thrown with a specific message
     */
    protected function assertExceptionMessage(string $expectedMessage, callable $callback): void
    {
        $exceptionThrown = false;
        $actualMessage = '';

        try {
            $callback();
        } catch (\Exception $e) {
            $exceptionThrown = true;
            $actualMessage = $e->getMessage();
        }

        $this->assertTrue($exceptionThrown, 'Expected exception was not thrown');
        $this->assertEquals($expectedMessage, $actualMessage, 'Exception message does not match expected');
    }

    /**
     * Assert that a collection contains only instances of a specific class
     */
    protected function assertCollectionContainsOnly(string $expectedClass, array $collection, string $message = ''): void
    {
        $this->assertNotEmpty($collection, $message ?: 'Collection should not be empty');

        foreach ($collection as $item) {
            $this->assertInstanceOf(
                $expectedClass,
                $item,
                $message ?: "Collection should contain only instances of '{$expectedClass}'"
            );
        }
    }

    /**
     * Assert that a response has a specific HTTP status code
     */
    protected function assertResponseStatus(int $expectedStatus, $response, string $message = ''): void
    {
        $actualStatus = $response->getStatusCode();
        $this->assertEquals(
            $expectedStatus,
            $actualStatus,
            $message ?: "Expected status code {$expectedStatus} but got {$actualStatus}"
        );
    }

    /**
     * Assert that a response contains specific headers
     */
    protected function assertResponseHasHeaders(array $expectedHeaders, $response, string $message = ''): void
    {
        foreach ($expectedHeaders as $header => $value) {
            $this->assertTrue(
                $response->hasHeader($header),
                $message ?: "Response should have header '{$header}'"
            );

            if ($value !== null) {
                $this->assertEquals(
                    $value,
                    $response->getHeaderLine($header),
                    $message ?: "Header '{$header}' should have value '{$value}'"
                );
            }
        }
    }

    /**
     * Assert that a language code is valid
     */
    protected function assertValidLanguageCode(string $code, string $message = ''): void
    {
        $this->assertMatchesRegularExpression(
            '/^[a-z]{2}(-[A-Z]{2})?$/',
            $code,
            $message ?: "'{$code}' is not a valid language code"
        );
    }

    /**
     * Assert that a cache key is valid
     */
    protected function assertValidCacheKey(string $key, string $message = ''): void
    {
        $this->assertMatchesRegularExpression(
            '/^[a-zA-Z0-9_\-\.]+$/',
            $key,
            $message ?: "'{$key}' is not a valid cache key"
        );

        $this->assertLessThanOrEqual(
            250,
            strlen($key),
            $message ?: "Cache key '{$key}' is too long (max 250 characters)"
        );
    }
}
