<?php

namespace App\Services;

use DOMDocument;
use DOMNode;
use DOMText;

class ListingDescription
{
    public function clean(string $html): string
    {
        $document = new DOMDocument;
        $document->loadHTML('<!doctype html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>',
            LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);

        $body = $document->getElementsByTagName('body')->item(0);

        return $body ? $this->children($body) : '';
    }

    public function plain(string $html): string
    {
        $withBreaks = preg_replace('~</(?:p|div|h3|li|blockquote)>|<br>~', "\n", $html);

        return trim(html_entity_decode(strip_tags($withBreaks), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private function children(DOMNode $parent): string
    {
        $html = '';
        foreach ($parent->childNodes as $node) {
            if ($node instanceof DOMText) {
                $html .= htmlspecialchars($node->data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                continue;
            }

            $tag = strtolower($node->nodeName);
            $html .= match ($tag) {
                'script', 'style', 'iframe', 'object', 'template' => '',
                'br' => '<br>',
                'p', 'div', 'strong', 'b', 'em', 'i', 'h3', 'ul', 'ol', 'li', 'blockquote' => "<$tag>".$this->children($node)."</$tag>",
                default => $this->children($node),
            };
        }

        return $html;
    }
}
