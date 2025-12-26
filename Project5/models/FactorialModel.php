<?php
class FactorialModel
{
    public function calculateFactorial($number)
    {
        if (!is_numeric($number)) {
            return "Error: Please enter a valid number.";
        }
        if ($number < 0) {
            return "Error: Factorial is not defined for negative numbers.";
        }
        if ($number > 170) {
            return "Error: Number too large (Result exceeds infinity).";
        }

        $factorial = 1;
        for ($i = 1; $i <= $number; $i++) {
            $factorial *= $i;
        }
        return $factorial;
    }
}
?>