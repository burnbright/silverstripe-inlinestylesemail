# Inline Styles

Convert stylesheets to inline styles with this SilverStripe module

## Usage

Here are a few ways to inline style html:

 1. Arbitrary HTML

	$html = InlineStyler::convert($html);

 2. Some emails

 	$email = new InlineStyledEmail();

 3. All emails

 	Email::set_mailer(new InlineStylesMailer());


## Research

 * http://classes.verkoyen.eu/css_to_inline_styles
 * http://www.pelagodesign.com/sidecar/emogrifier/
 * http://www.xavierfrenette.com/articles/css-support-in-webmail/#properties
 * http://www.email-standards.org/