<?php
if (! isset($argv[1])) {
    die("usage: php src/convert.php <input> <output>\nexample: php src/convert.php 'src/test-for-convert.php' 'output.php'\n");
}
$file = $argv[1];


// prepare array tokens
$allTokens = token_get_all(file_get_contents($file));
$allTokens = array_filter($allTokens, function ($v)
{
    if (is_array($v) && $v[0] == T_WHITESPACE) {
        return false;
    }
    
    return $v;
});
$allTokens = array_values($allTokens);

// prepare tokens
$tokens = array_map(function ($t)
{
    return is_array($t) ? $t[0] : $t;
}, $allTokens);

// start converting
$total = count($tokens);
searchArrayStartToken(0, function ($i)
{
    GLOBAL $tokens;
    $tokens[$i] = 'array(';
});

searchArrayStopToken(0, function ($i)
{
    GLOBAL $tokens;
    $tokens[$i] = ')';
});

// echo converted
file_put_contents($argv[2], convertTokensToCode());

function convertTokensToCode()
{
    GLOBAL $tokens, $allTokens, $total;
    $code = '';
    
    for ($i = 0; $i < $total; $i ++) {
        $v = $tokens[$i];
        if (is_numeric($v)) {
            $v = $allTokens[$i][1];
        }
        $code .= $v . ' ';
    }
    
    return $code;
}

function searchArrayStartToken($start, $callback)
{
    GLOBAL $total, $tokens;
    for ($i = $start; $i < $total; $i ++) {
        if ($tokens[$i] == '[' && $tokens[$i - 1] != T_VARIABLE) {
            $callback($i);
        }
    }
}

function searchArrayStopToken($start, $callback)
{
    GLOBAL $total, $tokens;
    for ($i = $start; $i < $total; $i ++) {
        if ($tokens[$i] == ']') {
            // echo token_name($tokens[$i - 3]).PHP_EOL;
            // echo token_name($tokens[$i - 2]).PHP_EOL;
            // echo token_name($tokens[$i - 1]).PHP_EOL;
        }
        if ($tokens[$i] == ']' && ! ($tokens[$i - 3] == T_VARIABLE && $tokens[$i - 2] == '[' && $tokens[$i - 1] == T_CONSTANT_ENCAPSED_STRING)) {
            $callback($i);
        }
    }
}
