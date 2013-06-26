<?php

/**
* Inline Styles Mailer
*/
class InlineStylesMailer extends Mailer
{
	
	public function sendHTML($to, $from, $subject, $htmlContent, $attachedFiles = false, $customheaders = false,
			$plainContent = false) {
		$htmlContent = InlineStyler::convert($htmlContent);
		return parent::sendHTML($to, $from, $subject, $htmlContent, $attachedFiles, $customheaders, $plainContent);
	}

}
