<?php

class InlineStylesMailer extends Mailer{
	
	function sendHTML($to, $from, $subject, $htmlContent, $attachedFiles = false, $customheaders = false, $plainContent = false, $inlineImages = false) {
		return $this->htmlEmail($to, $from, $subject, $htmlContent, $attachedFiles, $customheaders, $plainContent, $inlineImages);
	}
	
	function htmlEmail($to, $from, $subject, $htmlContent, $attachedFiles = false, $customheaders = false, $plainContent = false, $inlineImages = false) {
		if ($customheaders && is_array($customheaders) == false) {
			echo "htmlEmail($to, $from, $subject, ...) could not send mail: improper \$customheaders passed:<BR>";
			dieprintr($headers);
		}
	
	    
		$subjectIsUnicode = (strpos($subject,"&#") !== false);
		$bodyIsUnicode = (strpos($htmlContent,"&#") !== false);
	    $plainEncoding = "";
		
		// We generate plaintext content by default, but you can pass custom stuff
		$plainEncoding = '';
		if(!$plainContent) {
			$plainContent = Convert::xml2raw($htmlContent);
			if(isset($bodyIsUnicode) && $bodyIsUnicode) $plainEncoding = "base64";
		}
	
	
		// If the subject line contains extended characters, we must encode the 
		$subject = Convert::xml2raw($subject);
		if(isset($subjectIsUnicode) && $subjectIsUnicode)
			$subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
	
	
		// Make the plain text part
		$headers["Content-Type"] = "text/plain; charset=\"utf-8\"";
		$headers["Content-Transfer-Encoding"] = $plainEncoding ? $plainEncoding : "quoted-printable";
	
		$plainPart = processHeaders($headers, ($plainEncoding == "base64") ? chunk_split(base64_encode($plainContent),60) : wordwrap($plainContent,120));
	
		// Make the HTML part
		$headers["Content-Type"] = "text/html; charset=\"utf-8\"";
	        
		
		// Add basic wrapper tags if the body tag hasn't been given
		if(stripos($htmlContent, '<body') === false) {
			$htmlContent =
				"<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n" .
				"<HTML><HEAD>\n" .
				"<META http-equiv=Content-Type content=\"text/html; charset=utf-8\">\n" .
				"<STYLE type=3Dtext/css></STYLE>\n\n".
				"</HEAD>\n" .
				"<BODY bgColor=#ffffff>\n" .
					$htmlContent .
				"\n</BODY>\n" .
				"</HTML>";
		}
		
	    //this is the best place for Emogrification
	    
	    //TODO: grab css from files at Requirements::backend()->get_css();
	    $e = new CSSToInlineStyles(utf8_decode($htmlContent));
	    $e->setUseInlineStylesBlock();
	    $htmlContent = $e->convert();
	    
		if($inlineImages) {
			$htmlPart = wrapImagesInline($htmlContent);
		} else {
			$headers["Content-Transfer-Encoding"] = "quoted-printable";
			$htmlPart = processHeaders($headers, wordwrap(QuotedPrintable_encode($htmlContent),120));
		}
		
		list($messageBody, $messageHeaders) = encodeMultipart(array($plainPart,$htmlPart), "multipart/alternative");
	
		// Messages with attachments are handled differently
		if($attachedFiles && is_array($attachedFiles)) {
			
			// The first part is the message itself
			$fullMessage = processHeaders($messageHeaders, $messageBody);
			$messageParts = array($fullMessage);
	
			// Include any specified attachments as additional parts
			foreach($attachedFiles as $file) {
				if(isset($file['tmp_name']) && isset($file['name'])) {
					$messageParts[] = encodeFileForEmail($file['tmp_name'], $file['name']);
				} else {
					$messageParts[] = encodeFileForEmail($file);
				}
			}
				
			// We further wrap all of this into another multipart block
			list($fullBody, $headers) = encodeMultipart($messageParts, "multipart/mixed");
	
		// Messages without attachments do not require such treatment
		} else {
			$headers = $messageHeaders;
			$fullBody = $messageBody;
		}
	
		// Email headers
		$headers["From"] = validEmailAddr($from);
	
		// Messages with the X-SilverStripeMessageID header can be tracked
	    if(isset($customheaders["X-SilverStripeMessageID"]) && defined('BOUNCE_EMAIL')) {
	            $bounceAddress = BOUNCE_EMAIL;
	    } else {
	            $bounceAddress = $from;
	    }
	
	    // Strip the human name from the bounce address
	    if(ereg('^([^<>]*)<([^<>]+)> *$', $bounceAddress, $parts)) $bounceAddress = $parts[2];	
		
		// $headers["Sender"] 		= $from;
		$headers["X-Mailer"]	= X_MAILER;
		if (!isset($customheaders["X-Priority"])) $headers["X-Priority"]	= 3;
		
		$headers = array_merge((array)$headers, (array)$customheaders);
	
		// the carbon copy header has to be 'Cc', not 'CC' or 'cc' -- ensure this.
		if (isset($headers['CC'])) { $headers['Cc'] = $headers['CC']; unset($headers['CC']); }
		if (isset($headers['cc'])) { $headers['Cc'] = $headers['cc']; unset($headers['cc']); }
		
		// the carbon copy header has to be 'Bcc', not 'BCC' or 'bcc' -- ensure this.
		if (isset($headers['BCC'])) {$headers['Bcc']=$headers['BCC']; unset($headers['BCC']); }
		if (isset($headers['bcc'])) {$headers['Bcc']=$headers['bcc']; unset($headers['bcc']); }
			
		
		// Send the email
		$headers = processHeaders($headers);
		$to = validEmailAddr($to);
		
		// Try it without the -f option if it fails
		if(!($result = @mail($to, $subject, $fullBody, $headers, "-f$bounceAddress"))) {
			$result = mail($to, $subject, $fullBody, $headers);
		}
		
		return $result;
	}
	
}

?>
