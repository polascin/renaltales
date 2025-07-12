<?php

declare(strict_types=1);

namespace RenalTales\Core;

/**
 * OutputSanitizer - Comprehensive output sanitization for XSS prevention
 * 
 * Provides methods to safely output data in different contexts
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */
class OutputSanitizer {
    
    /**
     * Sanitize for HTML output
     * 
     * @param mixed $data
     * @return string
     */
    public static function html($data): string {
        if (is_null($data)) {
            return '';
        }
        
        return htmlspecialchars((string)$data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Sanitize for HTML attribute output
     * 
     * @param mixed $data
     * @return string
     */
    public static function attribute($data): string {
        if (is_null($data)) {
            return '';
        }
        
        $value = (string)$data;
        
        // Remove any quotes that could break attribute syntax
        $value = str_replace(['"', "'"], ['&quot;', '&#x27;'], $value);
        
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Sanitize for JavaScript output
     * 
     * @param mixed $data
     * @return string
     */
    public static function javascript($data): string {
        if (is_null($data)) {
            return 'null';
        }
        
        if (is_bool($data)) {
            return $data ? 'true' : 'false';
        }
        
        if (is_numeric($data)) {
            return (string)$data;
        }
        
        if (is_string($data)) {
            return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        }
        
        if (is_array($data) || is_object($data)) {
            return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        }
        
        return json_encode((string)$data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Sanitize for CSS output
     * 
     * @param mixed $data
     * @return string
     */
    public static function css($data): string {
        if (is_null($data)) {
            return '';
        }
        
        $value = (string)$data;
        
        // Remove potentially dangerous CSS
        $value = preg_replace('/[^\w\-\s#%.,()!]/', '', $value);
        
        // Remove expressions and javascript
        $value = preg_replace('/expression\s*\(/i', '', $value);
        $value = preg_replace('/javascript:/i', '', $value);
        $value = preg_replace('/vbscript:/i', '', $value);
        
        return $value;
    }
    
    /**
     * Sanitize for URL output
     * 
     * @param mixed $data
     * @return string
     */
    public static function url($data): string {
        if (is_null($data)) {
            return '';
        }
        
        $url = (string)$data;
        
        // Only allow http, https, mailto, and relative URLs
        if (!preg_match('/^(https?:\/\/|mailto:|\/|#)/', $url) && !filter_var($url, FILTER_VALIDATE_URL)) {
            return '';
        }
        
        // Remove javascript and data URLs
        if (preg_match('/^(javascript|data|vbscript):/i', $url)) {
            return '';
        }
        
        return htmlspecialchars($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Sanitize rich HTML content
     * 
     * @param string $html
     * @param array $allowedTags
     * @param array $allowedAttributes
     * @return string
     */
    public static function richHtml(string $html, array $allowedTags = null, array $allowedAttributes = null): string {
        if (empty($html)) {
            return '';
        }
        
        // Default allowed tags
        if ($allowedTags === null) {
            $allowedTags = [
                'p', 'br', 'strong', 'em', 'u', 'ol', 'ul', 'li', 'a', 'img',
                'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'code', 'pre',
                'span', 'div'
            ];
        }
        
        // Default allowed attributes
        if ($allowedAttributes === null) {
            $allowedAttributes = [
                'a' => ['href', 'title', 'target'],
                'img' => ['src', 'alt', 'title', 'width', 'height'],
                'blockquote' => ['cite'],
                'span' => ['class'],
                'div' => ['class']
            ];
        }
        
        // Strip all tags except allowed ones
        $html = strip_tags($html, '<' . implode('><', $allowedTags) . '>');
        
        // Remove dangerous attributes
        $html = preg_replace('/(<[^>]+)\s+(on\w+|javascript:|vbscript:|data:)/i', '$1', $html);
        
        // Clean up attributes to only allow safe ones
        foreach ($allowedAttributes as $tag => $attrs) {
            $pattern = '/<' . $tag . '\s+([^>]*?)>/i';
            $html = preg_replace_callback($pattern, function($matches) use ($attrs) {
                $tag = strtolower(trim(explode(' ', $matches[0])[0], '<>'));
                $attributeString = $matches[1];
                
                // Parse attributes
                preg_match_all('/(\w+)\s*=\s*["\']([^"\']*)["\']/', $attributeString, $attrMatches, PREG_SET_ORDER);
                
                $cleanAttributes = [];
                foreach ($attrMatches as $attr) {
                    $attrName = strtolower($attr[1]);
                    $attrValue = $attr[2];
                    
                    if (in_array($attrName, $attrs)) {
                        // Special handling for URLs
                        if (in_array($attrName, ['href', 'src', 'cite'])) {
                            $attrValue = self::url($attrValue);
                            if (empty($attrValue)) {
                                continue;
                            }
                        } else {
                            $attrValue = self::attribute($attrValue);
                        }
                        
                        $cleanAttributes[] = $attrName . '="' . $attrValue . '"';
                    }
                }
                
                return '<' . $tag . (empty($cleanAttributes) ? '' : ' ' . implode(' ', $cleanAttributes)) . '>';
            }, $html);
        }
        
        // Remove script and style tags completely
        $html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $html);
        $html = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi', '', $html);
        
        return $html;
    }
    
    /**
     * Sanitize for plain text output (strip all HTML)
     * 
     * @param mixed $data
     * @param int $maxLength
     * @return string
     */
    public static function text($data, int $maxLength = 0): string {
        if (is_null($data)) {
            return '';
        }
        
        $text = strip_tags((string)$data);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        if ($maxLength > 0 && strlen($text) > $maxLength) {
            $text = substr($text, 0, $maxLength) . '...';
        }
        
        return $text;
    }
    
    /**
     * Sanitize filename for safe filesystem operations
     * 
     * @param string $filename
     * @param int $maxLength
     * @return string
     */
    public static function filename(string $filename, int $maxLength = 255): string {
        // Remove directory traversal attempts
        $filename = basename($filename);
        
        // Remove dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Prevent multiple dots in a row
        $filename = preg_replace('/\.{2,}/', '.', $filename);
        
        // Remove leading/trailing dots and dashes
        $filename = trim($filename, '.-');
        
        // Ensure it's not empty
        if (empty($filename)) {
            $filename = 'file';
        }
        
        // Limit length
        if (strlen($filename) > $maxLength) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $basename = pathinfo($filename, PATHINFO_FILENAME);
            $maxBasenameLength = $maxLength - strlen($extension) - 1;
            $filename = substr($basename, 0, $maxBasenameLength) . '.' . $extension;
        }
        
        return $filename;
    }
    
    /**
     * Sanitize for CSV output
     * 
     * @param mixed $data
     * @return string
     */
    public static function csv($data): string {
        if (is_null($data)) {
            return '';
        }
        
        $value = (string)$data;
        
        // Remove control characters except tab and newline
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
        
        // Escape quotes by doubling them
        $value = str_replace('"', '""', $value);
        
        // Wrap in quotes if needed
        if (strpbrk($value, ",\"\r\n") !== false) {
            $value = '"' . $value . '"';
        }
        
        return $value;
    }
    
    /**
     * Sanitize for email output
     * 
     * @param string $email
     * @return string
     */
    public static function email(string $email): string {
        $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }
        
        return '';
    }
    
    /**
     * Sanitize phone number
     * 
     * @param string $phone
     * @return string
     */
    public static function phone(string $phone): string {
        // Remove all non-digit characters except + at the beginning
        $phone = preg_replace('/[^\d+]/', '', $phone);
        
        // Ensure + is only at the beginning
        if (strpos($phone, '+') > 0) {
            $phone = str_replace('+', '', $phone);
        }
        
        return $phone;
    }
    
    /**
     * Sanitize array recursively
     * 
     * @param array $data
     * @param callable $sanitizer
     * @return array
     */
    public static function array(array $data, callable $sanitizer): array {
        $result = [];
        
        foreach ($data as $key => $value) {
            $cleanKey = self::html($key);
            
            if (is_array($value)) {
                $result[$cleanKey] = self::array($value, $sanitizer);
            } else {
                $result[$cleanKey] = $sanitizer($value);
            }
        }
        
        return $result;
    }
    
    /**
     * Escape for use in LIKE queries
     * 
     * @param string $value
     * @param string $escapeChar
     * @return string
     */
    public static function like(string $value, string $escapeChar = '\\'): string {
        // Escape the escape character first
        $value = str_replace($escapeChar, $escapeChar . $escapeChar, $value);
        
        // Escape wildcard characters
        $value = str_replace(['%', '_'], [$escapeChar . '%', $escapeChar . '_'], $value);
        
        return $value;
    }
    
    /**
     * Sanitize for regex pattern
     * 
     * @param string $pattern
     * @return string
     */
    public static function regex(string $pattern): string {
        return preg_quote($pattern, '/');
    }
    
    /**
     * Helper method to create a safe view context array
     * 
     * @param array $data
     * @return array
     */
    public static function viewContext(array $data): array {
        $context = [];
        
        foreach ($data as $key => $value) {
            $cleanKey = preg_replace('/[^a-zA-Z0-9_]/', '', $key);
            
            if (is_string($value)) {
                $context[$cleanKey] = [
                    'raw' => $value,
                    'html' => self::html($value),
                    'attr' => self::attribute($value),
                    'js' => self::javascript($value),
                    'url' => self::url($value),
                    'text' => self::text($value)
                ];
            } elseif (is_array($value)) {
                $context[$cleanKey] = self::viewContext($value);
            } else {
                $context[$cleanKey] = [
                    'raw' => $value,
                    'html' => self::html($value),
                    'attr' => self::attribute($value),
                    'js' => self::javascript($value),
                    'text' => self::text($value)
                ];
            }
        }
        
        return $context;
    }
}
