<?php

/**
* Inline Styles Mailer
*/
class InlineStylesMailer extends Mailer
{
	
	public function sendHTML($to, $from, $subject, $htmlContent, $attachedFiles = false, $customheaders = false,
			$plainContent = false) {

		require_once(ISEMAIL_PATH.'/thirdparty/CssToInlineStyles/CssToInlineStyles.php');
		
		$cssToInlineStyles = new CssToInlineStyles($htmlContent,implode("\n\n",$this->getCSS($htmlContent)));
		$htmlContent = $cssToInlineStyles->convert();

		return parent::sendHTML($to, $from, $subject, $htmlContent, $attachedFiles, $customheaders, $plainContent);
	}

	protected function getCSS($html){
        $dom = new \DOMDocument();
        $dom->formatOutput = true;
        // strip illegal XML UTF-8 chars
        // remove all control characters except CR, LF and tab
        $html = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $html); // 00-09, 11-31, 127
        $dom->loadHTML($html);
        return $this->extractStylesheets($dom);
	}

	/**
     * Recursively extracts the stylesheet nodes from the DOMNode
     *
     * @param \DOMNode $node leave empty to extract from the whole document
     * @param string $base The base URI for relative stylesheets
     * @return array the extracted stylesheets
     */
    protected function extractStylesheets($node = null, $base = '')
    {
        $stylesheets = array();
        if(strtolower($node->nodeName) === "style") {
            $stylesheets[] = $node->nodeValue;
            $node->parentNode->removeChild($node);
        }
        else if(strtolower($node->nodeName) === "link") {
            if($node->hasAttribute("href")) {
                $href = $node->getAttribute("href");

                if($base && false === strpos($href, "://")) {
                    $href = "{$base}/{$href}";
                }
                $ext = @file_get_contents($href);
                if($ext) {
                    $stylesheets[] = $ext;
                    $node->parentNode->removeChild($node);
                }
            }
        }
        if($node->hasChildNodes()) {
            foreach($node->childNodes as $child) {
                $stylesheets = array_merge($stylesheets,
                    $this->extractStylesheets($child, $base));
            }
        }
        return $stylesheets;
    }

}
