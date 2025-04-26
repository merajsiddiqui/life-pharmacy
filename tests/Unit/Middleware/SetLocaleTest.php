<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

/**
 * Class SetLocaleTest
 * 
 * This test suite covers the SetLocale middleware functionality including:
 * - Default language handling
 * - Language switching
 * - Unsupported language handling
 */
class SetLocaleTest extends TestCase
{
    /**
     * @var SetLocale
     */
    protected $middleware;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new SetLocale();
        $this->request = new Request();
    }

    /**
     * Test setting English locale when no language header is provided
     * 
     * This test verifies that:
     * - The middleware defaults to English when no header is present
     * - The application locale is properly set
     */
    public function test_sets_english_locale_when_no_header()
    {
        $this->middleware->handle($this->request, function ($request) {
            $this->assertEquals('en', App::getLocale());
        });
    }

    /**
     * Test setting Arabic locale when language header is set to Arabic
     * 
     * This test verifies that:
     * - The middleware correctly sets Arabic locale
     * - The application locale is properly updated
     * - The language switch is successful
     */
    public function test_sets_arabic_locale_when_header_is_ar()
    {
        $this->request->headers->set('Accept-Language', 'ar');
        
        $this->middleware->handle($this->request, function ($request) {
            $this->assertEquals('ar', App::getLocale());
        });
    }

    /**
     * Test setting English locale when unsupported language is provided
     * 
     * This test verifies that:
     * - The middleware defaults to English for unsupported languages
     * - The application locale is properly set
     * - The fallback mechanism works correctly
     */
    public function test_sets_english_locale_when_header_is_unsupported()
    {
        $this->request->headers->set('Accept-Language', 'fr');
        
        $this->middleware->handle($this->request, function ($request) {
            $this->assertEquals('en', App::getLocale());
        });
    }
}