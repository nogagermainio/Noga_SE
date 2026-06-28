<?php

class Sqlast
{
    public function lexer(string $sql): array
    {
        $i = 0;
        $len = strlen($sql);

        $tokens = [];
        $buffer = '';

        while ($i < $len) {

            $char = $sql[$i];

            // espace = fin du mot
            if (ctype_space($char)) {

                if ($buffer !== '') {
                    $tokens[] = $buffer;
                    $buffer = '';
                }

                $i++;
                continue;
            }

            // caractères spéciaux
            if (in_array($char, [',', '(', ')', ';'])) {

                if ($buffer !== '') {
                    $tokens[] = $buffer;
                    $buffer = '';
                }

                $tokens[] = $char;

                $i++;
                continue;
            }

     if ($char === '>' && ($i + 1) < $len && $sql[$i + 1] === '=') {
    $token[] = '>=';
    $i += 2;
    continue;
}

            // construction du mot
            $buffer .= $char;

            $i++;
        }

        // dernier mot
        if ($buffer !== '') {
            $tokens[] = $buffer;
        }

        return $tokens;
    }
}