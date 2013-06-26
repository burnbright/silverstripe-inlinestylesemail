<?php

class InlineStyledEmail extends Email {

	protected function parseVariables($isPlain = false) {
		parent::parseVariables($isPlain);
		$this->body = InlineStyler::convert($this->body);
	}

}