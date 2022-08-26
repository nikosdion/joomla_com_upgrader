<?php
abstract class JHtmlExample
{
    public static function derp(): string
    {
        return "derp";
    }

    static function lol(): string
    {
        return "lol";
    }
}
?>
-----
<?php

namespace Acme\Example\Administrator\Service\Html;

class Example
{
    public function derp(): string
    {
        return "derp";
    }

	function lol(): string
	{
		return "lol";
	}
}
?>
