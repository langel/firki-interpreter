# firki-interpreter

A php solution for the firki style markup language.

Read about firki syntax here : http://battleofthebits.org/lyceum/View/Firki+Markup/

This library needs to be converted to an extensible object.  Battle of the Bits has a few unique commands that won't work on other projects.  The base class should be independant with no requirements outside of PHP v5.x



Desired interface :

- firki::render($string);  // returns rendered string
- firki::strip($string);  // returns raw text (no html) w/o any firki applied
- firki::lazy_links($string, $anchor_text, $anchor_attr);  // returns all URLs as ready anchor tags


Obvious next step :

- firki_botb extends firki{}
- firki_botb::render($string);  // same as above but with added commands
- firki_botb::strip($string);  // same as above but added commands have strip handlers too
