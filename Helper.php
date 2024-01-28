<?php

class Helper
{
    public function generateDummyName()
    {
        $firstNames = ['John', 'Jane', 'Alice', 'Bob', 'Emily', 'Michael', 'Olivia', 'David', 'Sophia', 'Daniel', 'Liam', 'Ella', 'Thomas', 'Hannah', 'William'];
        $lastNames = ['Smith', 'Johnson', 'Brown', 'Taylor', 'Anderson', 'Williams', 'Jones', 'Clark', 'Davis', 'Miller', 'Martinez', 'Garcia', 'Müller', 'Schmidt', 'Lefèvre'];

        $randomFirstName = $firstNames[array_rand($firstNames)];
        $randomLastName = $lastNames[array_rand($lastNames)];

        return $randomFirstName . ' ' . $randomLastName;
    }

    public function generateRandomIBAN()
    {
        $countryCode = 'DE'; // Change this to the desired country code
        $length = 20; // Change this to the desired IBAN length

        // Generate a random numeric string for the IBAN, excluding the country code and checksum digits
        $randomDigits = '';
        for ($i = strlen($countryCode) + 2; $i < $length; $i++) {
            $randomDigits .= mt_rand(0, 9);
        }

        // Calculate the checksum using the MOD-97 algorithm
        $ibanDigits = '00' . $randomDigits;

        // Ensure that the IBAN is formatted as a string to prevent the "Argument is not well-formed" error
        $ibanDigits = (string)$ibanDigits;

        $checksum = bcmod($ibanDigits, '97');

        // Calculate the check digits
        $checkDigits = strval(98 - $checksum);

        // Ensure the check digits are two digits by padding with leading zeros if necessary
        $checkDigits = str_pad($checkDigits, 2, '0', STR_PAD_LEFT);

        // Format the IBAN with the calculated check digits
        $iban = $countryCode . $checkDigits . $randomDigits;

        $iban = 'CH5604835012345678009';
        return $iban;
    }
}

?>