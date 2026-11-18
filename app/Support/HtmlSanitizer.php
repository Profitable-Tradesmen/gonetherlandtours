<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Allow-list HTML sanitiser for admin-entered rich text (Trix output and seeded copy).
 * Parses the HTML, then rebuilds it from the allowed tags only: the old regex version let encoded
 * "javascript:" / "data:" links (e.g. href="jav&#97;script:...") through.
 *
 *  - allowed tags: see ALLOWED; any other tag is removed but its text is kept
 *  - dangerous tags (script, style, iframe, svg ...) are removed together with their content
 *  - every attribute is dropped except href on <a> (http, https, mailto, tel, #anchor or a relative path);
 *    links get rel="noopener noreferrer" and may open in a new tab
 */
class HtmlSanitizer
{
    public const ALLOWED = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li', 'h2', 'h3', 'h4', 'a', 'blockquote', 'div', 'span'];

    private const DROP_WITH_CONTENT = ['script', 'style', 'iframe', 'frame', 'frameset', 'object', 'embed', 'applet', 'svg', 'math', 'noscript', 'template', 'textarea', 'select', 'option', 'button', 'form', 'input', 'link', 'meta', 'base', 'title', 'head', 'audio', 'video', 'canvas'];

    private const SAFE_SCHEMES = ['http', 'https', 'mailto', 'tel'];

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><body>'.$html.'</body>', LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $body = $dom->getElementsByTagName('body')->item(0);
        if (! $body) {
            return '';
        }
        self::sanitizeChildren($body);

        $out = '';
        foreach ($body->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return trim($out);
    }

    private static function sanitizeChildren(DOMNode $parent): void
    {
        // Copy the list first: the tree changes while we walk it.
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if ($node->nodeType === XML_COMMENT_NODE || $node->nodeType === XML_PI_NODE || $node->nodeType === XML_CDATA_SECTION_NODE) {
                $parent->removeChild($node);

                continue;
            }
            if (! $node instanceof DOMElement) {
                continue; // text nodes are escaped on output
            }

            $tag = strtolower($node->tagName);
            if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
                $parent->removeChild($node);

                continue;
            }

            self::sanitizeChildren($node);

            if (! in_array($tag, self::ALLOWED, true)) {
                // Unwrap: keep the (already cleaned) children, drop the tag itself.
                while ($node->firstChild) {
                    $parent->insertBefore($node->firstChild, $node);
                }
                $parent->removeChild($node);

                continue;
            }

            self::cleanAttributes($node, $tag);
        }
    }

    private static function cleanAttributes(DOMElement $el, string $tag): void
    {
        $href = $tag === 'a' ? $el->getAttribute('href') : null;
        $target = $tag === 'a' ? strtolower($el->getAttribute('target')) : null;

        foreach (iterator_to_array($el->attributes) as $attr) {
            $el->removeAttribute($attr->nodeName);
        }

        if ($tag !== 'a') {
            return;
        }
        $safe = self::safeUrl((string) $href);
        if ($safe === null) {
            return; // an <a> without href is just text
        }
        $el->setAttribute('href', $safe);
        if ($target === '_blank') {
            $el->setAttribute('target', '_blank');
        }
        $el->setAttribute('rel', 'noopener noreferrer');
    }

    /** The URL if it is safe to link to, otherwise null. Entities are already decoded by the parser. */
    public static function safeUrl(string $url): ?string
    {
        $url = trim($url);
        // Browsers ignore tabs/newlines/control characters inside a URL scheme ("java\tscript:"): compare without them.
        $probe = strtolower(preg_replace('/[\x00-\x20\x7f-\x9f\x{200B}-\x{200F}\x{2028}\x{2029}\x{FEFF}]+/u', '', $url) ?? '');
        if ($probe === '') {
            return null;
        }
        if (preg_match('/^([a-z][a-z0-9+.\-]*):/', $probe, $m)) {
            return in_array($m[1], self::SAFE_SCHEMES, true) ? $url : null;
        }
        // No scheme: relative path, #anchor or ?query. A leading "//" is a protocol-relative link, still plain http(s).
        return $url;
    }
}
