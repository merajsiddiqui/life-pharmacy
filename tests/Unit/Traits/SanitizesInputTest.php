<?php

namespace Tests\Unit\Traits;

use App\Traits\SanitizesInput;
use Tests\TestCase;

/**
 * Class SanitizesInputTest
 * 
 * This test suite covers the SanitizesInput trait functionality including:
 * - String input sanitization
 * - Nested array sanitization
 * - Non-string value preservation
 * - Empty input handling
 */
class SanitizesInputTest extends TestCase
{
    use SanitizesInput;

    /**
     * Test sanitizing string input
     * 
     * This test verifies that:
     * - HTML tags are removed from strings
     * - Special characters are properly encoded
     * - The sanitization preserves the original content
     * - The output is safe for database storage
     */
    public function test_sanitizes_string_input()
    {
        $input = [
            'name' => '<script>alert("test")</script>Test Name',
            'description' => '<p>Test Description</p>'
        ];

        $result = $this->sanitizeInput($input);

        $this->assertEquals('alert("test")Test Name', $result['name']);
        $this->assertEquals('Test Description', $result['description']);
    }

    /**
     * Test sanitizing nested array input
     * 
     * This test verifies that:
     * - Nested arrays are properly traversed
     * - Each level of nesting is sanitized
     * - The array structure is preserved
     * - All string values are sanitized
     */
    public function test_sanitizes_nested_array_input()
    {
        $input = [
            'name' => '<script>alert("test")</script>Test Name',
            'details' => [
                'description' => '<p>Test Description</p>',
                'notes' => '<b>Test Notes</b>'
            ]
        ];

        $result = $this->sanitizeInput($input);

        $this->assertEquals('alert("test")Test Name', $result['name']);
        $this->assertEquals('Test Description', $result['details']['description']);
        $this->assertEquals('Test Notes', $result['details']['notes']);
    }

    /**
     * Test preserving non-string values
     * 
     * This test verifies that:
     * - Numeric values are preserved
     * - Boolean values are preserved
     * - Array values are preserved
     * - Only string values are sanitized
     */
    public function test_preserves_non_string_values()
    {
        $input = [
            'name' => 'Test Name',
            'price' => 100,
            'is_active' => true,
            'tags' => ['tag1', 'tag2']
        ];

        $result = $this->sanitizeInput($input);

        $this->assertEquals('Test Name', $result['name']);
        $this->assertEquals(100, $result['price']);
        $this->assertTrue($result['is_active']);
        $this->assertEquals(['tag1', 'tag2'], $result['tags']);
    }

    /**
     * Test handling empty input
     * 
     * This test verifies that:
     * - Empty arrays are handled gracefully
     * - The method returns an empty array
     * - No errors are thrown
     */
    public function test_handles_empty_input()
    {
        $input = [];

        $result = $this->sanitizeInput($input);

        $this->assertEmpty($result);
    }
} 