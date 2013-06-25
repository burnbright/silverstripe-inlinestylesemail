<?php

Email::set_mailer(new InlineStylesMailer());
define("ISEMAIL_PATH",BASE_PATH.DIRECTORY_SEPARATOR.basename(__DIR__));
