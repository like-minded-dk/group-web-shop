<?php
function shortString($string, $cut = 50, $ellipsis = '...') {
    // Check if the string is longer than $cut characters
    if (mb_strlen($string) > $cut) {
        // Cut the string to the first $cut characters
        return mb_substr($string, 0, $cut) . $ellipsis;
    } else {
        // Return the original string if it's $cut characters or less
        return $string;
    }
}
