<?php

// Inspiration: https://gist.github.com/humantorch/d255e39a8ab4ea2e7005
function prefered_language($available_languages, $http_accept_language, $default_language)
{
    $available_languages = array_flip($available_languages);
    $langs = array();

    preg_match_all('~([\w-]+)(?:[^,\d]+([\d.]+))?~', strtolower($http_accept_language), $matches, PREG_SET_ORDER);

    foreach ($matches as $match)
    {
        list($a, $b) = explode('-', $match[1]) + array('', '');
        $value = isset($match[2]) ? (float) $match[2] : 1.0;

        if (isset($available_languages[$match[1]]))
        {
            if (!isset($langs[$match[1]]))
                $langs[$match[1]] = $value;
        }
        else if (isset($available_languages[$a]))
        {
            if (!isset($langs[$a]))
                $langs[$a] = $value - 0.1;
        }
    }

    if($langs)
    {
        arsort($langs);
        return key($langs); // We don't need the whole array of choices since we have a match
    }
    else
    {
        return $default_language;
    }
}
