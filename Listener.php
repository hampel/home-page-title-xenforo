<?php namespace Hampel\HomePageTitle;

class Listener
{
    /**
     * Declares which of this add-on's settings are secrets, for an add-on that reports a forum's
     * configuration with the secrets removed. Neither option is one: the title is published in
     * the page source, and the other is a switch. The add-on reads no config.php keys.
     *
     * Revisit this when an option is added or renamed.
     *
     * @param array $declarations
     */
    public static function adminApiRedaction(array &$declarations)
    {
        $declarations['Hampel/HomePageTitle'] = [];
    }
}
