<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use Normalizer;

class FormatDataService
{
    const Red = 'd40c18';
    const Blue = '26348b';
    const Yellow = 'ffc500';
    const Gray = '515151';
    private array $convertStyles = [
        'color' => [
            '#000000' => '',
            '#1d1b11' => '',
            '#3a3a3a' => '',
            '#1c1e21' => '',
            '#fa0909' => "text-".self::Blue,
            '#0068ff' => "text-".self::Blue,
            '#3426fa' => "text-".self::Blue,
            '#236fa1' => "text-".self::Blue,
            '#073af2' => "text-".self::Blue,
            '#0b99ff' => "text-".self::Blue,
            '#002aff' => "text-".self::Blue,
            '#51a1fb' => "text-".self::Blue,
            '#4a66ef' => "text-".self::Blue,
            '#004bf9' => "text-".self::Blue,
            '#f50d0d' => "text-".self::Blue,
            '#0011ff' => "text-".self::Blue,
            '#0f26fc' => "text-".self::Blue,
            '#2dc26b' => "text-".self::Blue,
            '#33cccc' => "text-".self::Blue,
            '#231f20' => '',
            '#008000' => '',
            '#0087cb' => "text-".self::Blue,
            'red' => 'text-'.self::Red,
            '#f51010' => 'text-'.self::Red,
            '#fa024d' => 'text-'.self::Red,
            '#ff0707' => 'text-'.self::Red,
            '#f90909' => 'text-'.self::Red,
            '#f41414' => 'text-'.self::Red,
            '#008080' => "text-".self::Blue,
            '#1155cc' => "text-".self::Blue,
            '#0000ff' => "text-".self::Blue,
            '#ba372a' => 'text-'.self::Red,
            '#d40c18' => 'text-'.self::Red,
            '#fc0202' => 'text-'.self::Red,
            '#ff060a' => 'text-'.self::Red,
            '#26348b' => "text-".self::Blue,
            '#515151' => 'text-'.self::Gray,
            '#ffc500' => 'text-'.self::Yellow,
            '#808000' => 'text-'.self::Yellow,
            '#f80d0d' => 'text-'.self::Red,
            '#e03e2d' => 'text-'.self::Red,
            '#ff0000' => 'text-'.self::Red,
            '#3598db' => 'text-'.self::Blue,
            '#5da0f9' => "text-".self::Blue,
            '#179eff' => "text-".self::Blue,
            '#fd0000' => 'text-'.self::Red,
            '#f41d1d' => 'text-'.self::Red,
            '#0073f9' => "text-".self::Blue,
            '#333333' => 'text-'.self::Gray,
            '#434343' => 'text-'.self::Gray,
            '#222222' => 'text-'.self::Gray,
            '#800000' => 'text-'.self::Red,
            '#fe0202' => 'text-'.self::Red,
            '#fa0404' => 'text-'.self::Red,
            'black' => '',
            '#001dff' => "text-".self::Blue,
            '#f91d2b' => 'text-'.self::Red,
            '#000080' => "text-".self::Blue,
            '#3366ff' => "text-".self::Blue,
            '#005bff' => "text-".self::Blue,
            '#0005ff' => "text-".self::Blue,
        ],
        'text-align' => [
            'center' => 'text-center',
            'right' => 'text-end',
            'justify' => '',
            'left' => 'text-start',
            'start' => 'text-start',
        ],
        'background-color' => [
            '#ffffff' => '',
            '#a7ed60' => '',
            '#fbeeb8' => '',
            '#def1ff' => '',
            'white' => '',
            '#ecf8e8' => '',
            'transparent' => '',
            '#f8e8f6' => '',
            '#d40c18' => 'bg-'.self::Red,
            '#ff0000' => 'bg-'.self::Red,
            '#f51010' => 'bg-'.self::Red,
            '#fcff00' => 'bg-'.self::Yellow,
            '#f9f108' => 'bg-'.self::Yellow,
            '#f5ff00' => 'bg-'.self::Yellow,
            '#fcf40d' => 'bg-'.self::Yellow,
            '#fef35b' => 'bg-'.self::Yellow,
            '#edf416' => 'bg-'.self::Yellow,
            '#e9fe02' => 'bg-'.self::Yellow,
            '#26348b' => 'bg-'.self::Blue,
            '#515151' => 'bg-'.self::Gray,
            '#ffc500' => 'bg-'.self::Yellow,
            '#f3f703' => 'bg-'.self::Yellow,
            '#e8f809' => 'bg-'.self::Yellow,
            '#f2e638' => 'bg-'.self::Yellow,
            '#e3fbda' => '',
            '#ecf0f1' => '',
            '#c1efff' => '',
            '#eaf9f8' => '',
            '#eaff00' => 'bg-'.self::Yellow,
            '#f1c40f' => 'bg-'.self::Yellow,
            '#fcfc02' => 'bg-'.self::Yellow,
            '#ffff00' => 'bg-'.self::Yellow,
            '#f4e800' => 'bg-'.self::Yellow,
            '#f7e815' => 'bg-'.self::Yellow,
            '#ffff99' => 'bg-'.self::Yellow,
            '#fdff00' => 'bg-'.self::Yellow,
            '#fbfb07' => 'bg-'.self::Yellow,
            '#f5fa00' => 'bg-'.self::Yellow,
            '#e7ff0a' => 'bg-'.self::Yellow,
            '#ffff06' => 'bg-'.self::Yellow,
            '#ecfd08' => 'bg-'.self::Yellow,
            '#f8ff00' => 'bg-'.self::Yellow,
        ],
        'background' => [
            'white' => '',
            'white none repeat scroll 0% 0%' => '',
            'yellow' => 'bg-'.self::Yellow,
        ],
        'border' => [
            'none' => '',
            'solid #000000 0.8333325pt' => '',
            'solid #000000 0.75pt' => '',
            'solid #000000 1pt' => '',
            '1pt solid #000000' => '',
            'solid #808080 0.75pt' => '',
            '0.5pt solid #000000' => '',
            '0.75pt solid #000000' => '',
            'solid #236fa1 0.75pt' => '',
            '1px dashed #e38f00' => '',
            '2px dotted #089bcc' => '',
            '1pt none windowtext' => '',
        ],
        'border-color' => [
            '#000000' => '',
        ],
        'border-style' => [
            'none' => '',
        ],
        'border-collapse' => [
            'collapse' => '',
        ],
        'border-left' => [
            'solid #000000 1pt' => '',
            'solid #000000 0.50000025pt' => '',
            'solid #000000 0.55555575pt' => '',
            'solid #000000 0.75pt' => '',
            'solid #cccccc 0.75pt' => '',
            'solid #808080 0.75pt' => '',
            'solid #236fa1 0.75pt' => '',
        ],
        'border-right' => [
            'solid #808080 0.75pt' => '',
            'solid #000000 0.75pt' => '',
            'solid #236fa1 0.75pt' => '',
        ],
        'border-bottom' => [
            'solid #000000 0.75pt' => '',
            'solid #808080 0.75pt' => '',
            'solid #236fa1 0.75pt' => '',
        ],
        'border-top' => [
            'solid #000000 0.75pt' => '',
            'solid #236fa1 0.75pt' => '',
        ],
        'font-weight' => [
            'bold' => 'fw-6',
            '400' => '',
            'inherit' => '',
            'normal' => '',
        ],
        'font-size' => [
            '12pt' => 'fs-12',
            '12px' => 'fs-12',
            '13px' => 'fs-13',
            '15px' => '',
            'unset !important' => '',
            '10pt' => 'fs-10',
            '11pt' => 'fs-11',
            '14pt' => 'fs-14',
            '13.5pt' => 'fs-14',
            '14.0pt' => 'fs-14',
            '18pt' => 'fs-18',
            '18.6667px' => 'fs-18',
            '18px' => 'fs-18',
            '16pt' => 'fs-16',
            '16px' => 'fs-16',
            '13pt' => 'fs-13',
            '13.999999999999998pt' => 'fs-14',
            '15pt' => 'fs-16',
            '0.6em' => '',
            '1.6em' => '',
            '32px' => '',
            '24pt' => 'fs-24',
            '24px' => 'fs-24',
            '9pt' => 'fs-10',
            'medium' => '',
        ],
        'table-layout' => [
            'fixed' => 'table-layout-fixed'
        ],
        'text-decoration' => [
            'none' => '',
            'underline' => 'text-decoration-underline'
        ],
        'text-decoration-line' => [
            'underline' => 'text-decoration-underline',
            'none' => '',
        ],
        'text-decoration-style' => [
            'initial' => ''
        ],/*
        'text-decoration-color' => [
            'initial' => ''
        ],
        'word-spacing' => [
            '0px' => ''
        ],
        'list-style-type' => [
            'disc' => ''
        ],
        'text-decoration-skip-ink' => [
            'none' => ''
        ],
        'float' => [
            'left' => ''
        ],*/
        'font-style' => [
            'italic' => 'fst-italic',
            'normal' => '',
        ]
    ];
    private array $removeStyleAttribute = [
        'line-height', 'float', 'text-decoration-skip-ink', 'list-style-type', 'word-spacing', 'text-decoration-color',
        '-webkit-text-stroke-width', 'widows', 'orphans', 'white-space-collapse', 'white-space', 'line-height',
        'overflow-wrap', 'overflow', 'vertical-align', 'height', 'width', 'max-width', 'font-variant-east-asian',
        'font-variant-numeric', 'font-variant-caps', 'font-variant-ligatures', 'font-variant', 'font-family',
        'margin-bottom', 'margin-left', 'margin-top', 'margin-right', 'margin', 'display', 'background-repeat',
        'top', 'left', 'position', 'background-image', 'background-attachment', 'background-position', 'padding',
        'padding-left', 'padding-top', 'padding-right', 'padding-bottom', '-webkit-text-decoration-skip',
        'margin-block-start', 'margin-block-end', 'margin-inline-start', 'margin-inline-end', 'padding-inline-start',
        'padding-inline-end', 'tab-stops', 'text-indent', 'break-after', 'letter-spacing', 'text-transform',
        'background-size', 'background-origin', 'background-clip', 'box-sizing', 'font-variant-alternates',
        'font-variant-position'
    ];
    private array $removeElements = [
        'colgroup'
    ];

    public function clean(?string $html): ?string
    {
        if (! $html) return null;
        if (preg_match('/<[^>]+>/', $html) === 1) {
            /*replace box xem thêm*/
            $html = str_replace('div style="font-weight: bold; margin-top: 5px; margin-bottom: 5px; height: auto; min-height: 50px; background-color: #ecf0f1; display: flex; border-radius: 3px; border-left: 4px solid #0083cf; align-items: center;"', 'div class="xem-them"', $html);
            $html = str_replace('div style="padding-left: 1em; padding-right: 1em;"', 'div', $html);
            $html = str_replace('span class="ctaText" style="color: #ee2713;"', 'span', $html);

            libxml_use_internal_errors(true);

            $dom = new DOMDocument();
            $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            foreach (iterator_to_array($dom->getElementsByTagName('*')) as $i){
                /** @var DOMElement $i */
                $this->formatIframe($i);
                $this->clearFormatBlockquoteTag($i);
                $this->cleanTable($i);
                $this->cleanAttribute($i);
                $this->removeStyleAttributes($i);
                $this->removeEmptyTags($i);
                $this->removeAppUrl($i);
                $this->removeEl($i);
                $this->removeStrongEmpty($i);
                $this->convertStyle2Class($i);
                $this->removeDuplicateClass($i);
            }
            $html = str_replace(['<?xml encoding="UTF-8">'], [''], html_entity_decode($dom->saveHTML()));
            $this->cleanBrTag($html);
            $this->cleanRelDofollow($html);
            $this->minify($html);
        }

        $this->cleanEncodeChar($html);
        $this->nfcContent($html);
        $this->cleanEmDash($html);
        return $html;
    }

    private function convertStyle2Class(DOMElement $el): void
    {
        if (!$el->hasAttribute('style')) {
            return;
        }

        $style = $el->getAttribute('style');
        $rules = array_filter(array_map('trim', explode(';', $style)));

        $newClasses = [];
        $newRules = [];

        foreach ($rules as $rule) {
            if (strpos($rule, ':') === false) {
                continue;
            }

            [$prop, $val] = array_map('trim', explode(':', $rule, 2));

            if (isset($this->convertStyles[$prop][$val])) {
                $class = $this->convertStyles[$prop][$val];
                if ($class !== '') {
                    $newClasses[] = $class;
                }
                // bỏ style này, không add lại
            } else {
                // giữ style không có trong mapping
                $newRules[] = $prop . ':' . $val;
            }
        }

        // Gộp class mới vào class cũ
        if (!empty($newClasses)) {
            $existing = $el->getAttribute('class') ?: '';
            $classArr = array_filter(explode(' ', $existing));
            $classArr = array_merge($classArr, $newClasses);
            $classArr = array_unique(array_filter(array_map('trim', $classArr))); // Đảm bảo sạch dữ liệu
            $newClass = implode(' ', $classArr);
            $el->setAttribute('class', $newClass);
        }

        // Nếu còn style khác thì giữ lại
        if (!empty($newRules)) {
            $el->setAttribute('style', implode(';', $newRules));
        } else {
            $el->removeAttribute('style');
        }
    }
    private function removeEl(DOMElement $el): void
    {
        if (in_array(strtolower($el->tagName), $this->removeElements, true)) {
            $el->parentNode?->removeChild($el);
        }
    }
    private function removeStrongEmpty(DOMElement $i): void
    {
        /** @var \DOMNodeList $strongs */
        $strongs = $i->getElementsByTagName('strong');

        // vì NodeList là live, ta cần copy ra array trước khi xử lý
        $list = [];
        foreach ($strongs as $s) {
            $list[] = $s;
        }

        foreach ($list as $strong) {
            // case 1: strong trong heading
            $parent = $strong->parentNode;
            if ($parent instanceof DOMElement && in_array(strtolower($parent->tagName), ['h1','h2','h3','h4','h5','h6'])) {
                // unwrap: đưa con ra ngoài, bỏ strong
                while ($strong->firstChild) {
                    $parent->insertBefore($strong->firstChild, $strong);
                }
                $parent->removeChild($strong);
                continue;
            }

            // case 2: strong > img
            $onlyImg = true;
            foreach ($strong->childNodes as $child) {
                if ($child instanceof DOMText && trim($child->textContent) === '') {
                    continue; // ignore khoảng trắng
                }
                if (!($child instanceof DOMElement && $child->tagName === 'img')) {
                    $onlyImg = false;
                    break;
                }
            }

            if ($onlyImg) {
                $parent = $strong->parentNode;
                while ($strong->firstChild) {
                    $parent->insertBefore($strong->firstChild, $strong);
                }
                $parent->removeChild($strong);
            }
        }
    }

    private function cleanTable(DOMElement $el): void
    {
        $tags = ['table', 'col', 'tr', 'th', 'td'];
        if (!in_array(strtolower($el->nodeName), $tags)) return;

        // Bỏ các attribute không cần
        foreach (['style', 'border', 'cellspacing', 'cellpadding', 'width'] as $attr) {
            $el->removeAttribute($attr);
        }

        // Nếu là <td> hoặc <th>, check con
        if (in_array(strtolower($el->nodeName), ['td', 'th'])) {
            $pChildren = [];
            foreach (iterator_to_array($el->childNodes) as $child) {
                if ($child instanceof DOMElement && strtolower($child->nodeName) === 'p') {
                    $pChildren[] = $child;
                }
            }

            // Nếu có nhiều <p>
            if (count($pChildren) > 1) {
                $first = true;
                foreach ($pChildren as $p) {
                    if (!$first) {
                        // thêm <br> trước nội dung p
                        $br = $el->ownerDocument->createElement('br');
                        $el->insertBefore($br, $p);
                    }
                    while ($p->firstChild) {
                        $el->insertBefore($p->firstChild, $p);
                    }
                    $next = $p->nextSibling;
                    $el->removeChild($p);
                    $first = false;
                }
            } else {
                // unwrap bình thường nếu chỉ có 1 <p>
                foreach ($pChildren as $p) {
                    while ($p->firstChild) {
                        $el->insertBefore($p->firstChild, $p);
                    }
                    $el->removeChild($p);
                }
            }
        }
    }

    private function formatIframe(DOMElement $el): void
    {
        if (strtolower($el->nodeName) !== 'iframe') return;

        // Format src
        $src = $el->getAttribute('src');
        if ($src) {
            $src = preg_replace('/\x{00A0}|\s/u', '', $src); // loại bỏ khoảng trắng, nbsp
            if (!str_starts_with($src, 'https://')) {
                $src = str_starts_with($src, 'http://')
                    ? preg_replace('/^http:\/\//', 'https://', $src)
                    : 'https://' . ltrim($src, '/');
            }
            $el->setAttribute('src', $src);
        }

        // Chỉ giữ lại các attribute được cho phép
        $allowed = ['title', 'src'];
        foreach (iterator_to_array($el->attributes) as $attr) {
            if (!in_array($attr->name, $allowed, true)) {
                $el->removeAttribute($attr->name);
            }
        }

        // Nếu cần: loại bỏ wrapper không hợp lệ
        $this->unwrapToBlockLevel($el);
    }

    private function cleanAttribute(DOMElement $el): void
    {
        $el->removeAttribute('dir');
        $el->removeAttribute('role');
        $el->removeAttribute('aria-level');
//        $el->removeAttribute('class');
        $el->removeAttribute('id');
        $el->removeAttribute('align');
        foreach ($el->attributes as $attr) {
            if (str_starts_with($attr->name, 'data-')) {
                $el->removeAttribute($attr->name);
            }
        }
    }
    private function cleanBrTag(&$content): void
    {
        $content = str_replace(['<br />', '<br/>'], '<br>', $content);
    }
    private function cleanRelDofollow(&$content): void
    {
        $content = str_replace([' rel="dofollow"', ' rel="Dofollow"'], '', $content);
    }
    private function minify(&$content): void
    {
        // Xoá \r, \n, \t
        $content = str_replace(["\r", "\n", "\t"], '', $content);

        // Rút gọn khoảng trắng giữa các thẻ HTML
        $content = preg_replace('/>\s+</', '><', $content);

        // Rút gọn các chuỗi nhiều space liên tiếp thành 1 space
        $content = preg_replace('/\s{2,}/', ' ', $content);

        // Trim lại toàn bộ nội dung
        $content = trim($content);
    }
    private function cleanEncodeChar(&$content): void
    {
        $content = str_replace("\xC2\xA0", ' ', $content); // UTF-8 NBSP → space
        $content = str_replace("\u{200B}", '', $content); // UTF-8 NBSP non space -> delete
    }
    private function cleanEmDash(string &$content): void
    {
        $content = str_replace('–', '-', $content);
    }
    private function unwrapToBlockLevel(DOMElement $el): void
    {
        $blockTags = ['body', 'div', 'section', 'article', 'main', 'aside'];

        while ($el->parentNode && $el->parentNode instanceof DOMElement) {
            $parent = $el->parentNode;
            if (in_array(strtolower($parent->nodeName), $blockTags)) break;

            $grand = $parent->parentNode;
            if ($grand) {
                $grand->insertBefore($el, $parent);
                if ($parent->childNodes->length === 0) {
                    $grand->removeChild($parent);
                }
            } else {
                break;
            }
        }
    }
    private function removeEmptyTags(DOMElement $el): void
    {
        $tag = strtolower($el->tagName);
        if (!in_array($tag, ['p', 'span'])) return;

        $innerHTML = '';
        foreach ($el->childNodes as $child) {
            $innerHTML .= $el->ownerDocument->saveHTML($child);
        }

        // Loại bỏ các nội dung "rác" không hiển thị thực: &nbsp;, br, khoảng trắng
        $cleaned = preg_replace([
            '/&nbsp;/i',
            '/<br\s*\/?>/i',
            '/\s+/u'
        ], '', $innerHTML);

        if ($cleaned === '') {
            $el->parentNode?->removeChild($el);
        }
    }
    private function removeAppUrl(DOMElement $el): void
    {
        $attributesToCheck = ['href', 'src', 'action'];

        foreach ($attributesToCheck as $attr) {
            if (!$el->hasAttribute($attr)) {
                continue;
            }

            // Bỏ qua iframe src
            if ($el->tagName === 'iframe' && $attr === 'src') {
                continue;
            }

            $val = $el->getAttribute($attr);
            $val = $this->cleanUrl($val);

            $el->setAttribute($attr, $val);
        }
    }
    private function nfcContent(?string &$content): void
    {
        $content = Normalizer::normalize($content, Normalizer::FORM_C);
    }
    private function clearFormatBlockquoteTag(DOMElement $el): void
    {
        if (strtolower($el->tagName) === 'blockquote') {
            while ($el->attributes->length > 0) {
                $el->removeAttributeNode($el->attributes->item(0));
            }
        }
    }
    private function removeDuplicateClass(DOMElement $el): void
    {
        if ($el->hasAttribute('class')) {
            $classes = preg_split('/\s+/', trim($el->getAttribute('class')));
            $classes = array_unique(array_filter($classes)); // loại bỏ trùng và rỗng
            if ($classes) {
                $el->setAttribute('class', implode(' ', $classes));
            } else {
                $el->removeAttribute('class'); // không còn class thì bỏ hẳn attr
            }
        }
    }
    private function removeStyleAttributes(DOMElement $el): void
    {
        if ($el->hasAttribute('style')) {
            $style = $el->getAttribute('style');

            // tách các rule style
            $rules = array_filter(array_map('trim', explode(';', $style)));

            $cleanRules = [];
            foreach ($rules as $rule) {
                [$prop, $val] = array_map('trim', explode(':', $rule, 2) + [null, null]);
                if ($prop && !in_array(strtolower($prop), $this->removeStyleAttribute, true)) {
                    $cleanRules[] = "$prop: $val";
                }
            }

            if ($cleanRules) {
                $el->setAttribute('style', implode('; ', $cleanRules) . ';');
            } else {
                $el->removeAttribute('style');
            }
        }
    }

    public function cleanUrl(?string $url): string
    {
        if (empty($url)) {
            return '';
        }

        $base = rtrim(config('app.url'), '/');
        $url = preg_replace("#^$base#", '', $url);

        if ($url === '') {
            $url = '/';
        }

        $url = preg_replace('#^(\.\./)+#','/', $url);

        $this->nfcContent($url);

        return $url;
    }
}
