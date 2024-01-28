<?php
require_once 'vendor/autoload.php'; // Include Composer autoload
use Symfony\Component\Yaml\Yaml;

function generateDummyName()
{
    $firstNames = ['John', 'Jane', 'Alice', 'Bob', 'Emily', 'Michael', 'Olivia', 'David', 'Sophia', 'Daniel', 'Liam', 'Ella', 'Thomas', 'Hannah', 'William'];
    $lastNames = ['Smith', 'Johnson', 'Brown', 'Taylor', 'Anderson', 'Williams', 'Jones', 'Clark', 'Davis', 'Miller', 'Martinez', 'Garcia', 'Müller', 'Schmidt', 'Lefèvre'];

    $randomFirstName = $firstNames[array_rand($firstNames)];
    $randomLastName = $lastNames[array_rand($lastNames)];

    return $randomFirstName . ' ' . $randomLastName;
}

function generateRandomIBAN()
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


// Check if an argument for the input file path is provided
if (isset($argv[1])) {
    $inputFile = $argv[1];
} else {
    echo "Usage: php script.php <full_input_file_path>\n";
    exit(1);
}

if (isset($argv[2])) {
    $inputSourceType = $argv[2];
} else {
    $inputSourceType = 'pain.008';
    exit(1);
}

// Load the configuration from the YAML file
$configFile = 'config/config.yml'; // Path to the configuration YAML file
$config = Yaml::parseFile($configFile);

// Access the configuration values
$xmlns = $config['document']['xmlns'];
$xmlns_xsi = $config['document']['xmlns_xsi'];
$xsi_schemaLocation = $config['document']['xsi_schemaLocation'];

$acctIban = $config['acct']['iban'];
$acctCcy = $config['acct']['ccy'];
$acctCreDtTm = $config['acct']['credttm'];

$acctOwnrNm = $config['acct']['ownr']['nm'];
$acctSvcrBic = $config['acct']['svcr']['fin_instn_id']['bic'];
$acctSvcrNm = $config['acct']['svcr']['fin_instn_id']['nm'];
$acctSvcrOthrId = $config['acct']['svcr']['fin_instn_id']['othr']['id'];
$acctSvcrOthrIssr = $config['acct']['svcr']['fin_instn_id']['othr']['issr'];


// Extract the directory path and input file name from the full file path
$inputDirectory = dirname($inputFile);
$inputFileName = basename($inputFile);

// Create a new DOMDocument
$doc = new DOMDocument('1.0', 'UTF-8');

// Create the root <Document> element
$document = $doc->createElement('Document');
$document->setAttribute('xmlns', $xmlns);
$document->setAttribute('xmlns:xsi', $xmlns_xsi);
$document->setAttribute('xsi:schemaLocation', $xsi_schemaLocation);
$doc->appendChild($document);

// Create the <BkToCstmrDbtCdtNtfctn> element
$bkToCstmrDbtCdtNtfctn = $doc->createElement('BkToCstmrDbtCdtNtfctn');
$document->appendChild($bkToCstmrDbtCdtNtfctn);

// Create the <GrpHdr> element
$grpHdr = $doc->createElement('GrpHdr');
$bkToCstmrDbtCdtNtfctn->appendChild($grpHdr);

// Create the <MsgId> element and set its value
$msgId = $doc->createElement('MsgId', 'M/4101997235/CAM');
// Create the <MsgId> element and set its value
$msgId = $doc->createElement('MsgId', '54D20220404T2224498418240N220000008');
$grpHdr->appendChild($msgId);

// Create the <CreDtTm> element and set its value
$creDtTm = $doc->createElement('CreDtTm', $acctCreDtTm);
$grpHdr->appendChild($creDtTm);

// Create the <MsgPgntn> element
$msgPgntn = $doc->createElement('MsgPgntn');
$grpHdr->appendChild($msgPgntn);

// Create the <PgNb> element for page number and set its value
$pgNb = $doc->createElement('PgNb', '001');
$msgPgntn->appendChild($pgNb);

// Create the <LastPgInd> element for last page indicator and set its value
$lastPgInd = $doc->createElement('LastPgInd', 'true');
$msgPgntn->appendChild($lastPgInd);

// Create the <Ntfctn> element
$ntfctn = $doc->createElement('Ntfctn');
$bkToCstmrDbtCdtNtfctn->appendChild($ntfctn);

// Create the <Id> element and set its value
$id = $doc->createElement('Id', '4967C542022040422244984');
$ntfctn->appendChild($id);

// Create the <CreDtTm> element and set its value
$creDtTm = $doc->createElement('CreDtTm', $acctCreDtTm);
$ntfctn->appendChild($creDtTm);

// Create the <Acct> element
$acct = $doc->createElement('Acct');
$ntfctn->appendChild($acct);

// Create the <Id> element for the account and set its value
$acctId = $doc->createElement('Id');
$iban = $doc->createElement('IBAN', $acctIban);
$acctId->appendChild($iban);
$acct->appendChild($acctId);

// Create the <Ccy> element for the account and set its value
$acctCcy = $doc->createElement('Ccy', $acctCcy);
$acct->appendChild($acctCcy);

// Create the <Ownr> element for the account
$ownr = $doc->createElement('Ownr');
$acct->appendChild($ownr);

// Create the <Nm> element for the account owner and set its value
$ownrNm = $doc->createElement('Nm', $acctOwnrNm);
$ownr->appendChild($ownrNm);

// Create the <Svcr> element for the service provider
$svcr = $doc->createElement('Svcr');
$acct->appendChild($svcr);

// Create the <FinInstnId> element for the service provider
$finInstnId = $doc->createElement('FinInstnId');
$svcr->appendChild($finInstnId);

// Create the <BIC> element for the service provider and set its value
$bic = $doc->createElement('BICFI', $acctSvcrBic);
$finInstnId->appendChild($bic);

// Create the <Nm> element for the service provider and set its value
$nm = $doc->createElement('Nm', $acctSvcrNm);
$finInstnId->appendChild($nm);

// Create the <Othr> element for other information about the service provider
$othr = $doc->createElement('Othr');
$finInstnId->appendChild($othr);

// Create the <Id> element for the other information and set its value
$id = $doc->createElement('Id', $acctSvcrOthrId);
$othr->appendChild($id);

// Create the <Issr> element for the issuer of the other information and set its value
$issr = $doc->createElement('Issr', 'UmsStId');
$othr->appendChild($issr);

// Create the <NtryDtls> element
$ntryDtls = $doc->createElement('NtryDtls');
$ntfctn->appendChild($ntryDtls);

// Create the <Btch> element for the summary
$btch = $doc->createElement('Btch');
$ntryDtls->appendChild($btch);

// Calculate the number of transactions (assuming you have a count of transactions)
$numTransactions = 10; // Replace with your actual count of transactions

// Create the <PmtInfId> element for the summary
$pmtInfId = $doc->createElement('PmtInfId', 'windata S0000337'); // Replace with your payment information ID
$btch->appendChild($pmtInfId);

// Create the <NbOfTxs> element for the summary and set its value
$nbOfTxs = $doc->createElement('NbOfTxs', $numTransactions);
$btch->appendChild($nbOfTxs);

$ntry = $doc->createElement('Ntry');
$ntfctn->appendChild($ntry);

$totalAmount = 0;


if ($inputSourceType === 'pain.008') {
    // Read and parse the input file
    $painXml = file_get_contents($inputFile);
    $painDoc = new DOMDocument();
    $painDoc->loadXML($painXml);
// Iterate through the pain.008 file data and create TxDtls elements
    $drctDbtTxInfList = $painDoc->getElementsByTagName('DrctDbtTxInf');
    foreach ($drctDbtTxInfList as $drctDbtTxInf) {
        // Create a new TxDtls element for each transaction
        $txDtls = $doc->createElement('TxDtls');

        // Extract and add the necessary data from the pain.008 file
        $endToEndId = $drctDbtTxInf->getElementsByTagName('EndToEndId')->item(0)->textContent;
        $instdAmtElement = $drctDbtTxInf->getElementsByTagName('InstdAmt')->item(0);
        $instdAmt = $instdAmtElement->textContent;
        $currency = $instdAmtElement->getAttribute('Ccy');

        // Create the <Refs> element and set its values
        $refs = $doc->createElement('Refs');
        $msgId = $doc->createElement('MsgId', '20200824-203541');
        $acctsVcrRef = $doc->createElement('AcctSvcrRef', '201-40128241-774');
        $pmtInfId = $doc->createElement('PmtInfId', '20200824-203541-00');
        $instrId = $doc->createElement('InstrId', '11608683549');
        $endToEndIdElement = $doc->createElement('EndToEndId', $endToEndId);
        $refs->appendChild($msgId);
        $refs->appendChild($acctsVcrRef);
        $refs->appendChild($pmtInfId);
        $refs->appendChild($instrId);
        $refs->appendChild($endToEndIdElement);
        $txDtls->appendChild($refs);

        // Create the <AmtDtls> element
        $amtDtls = $doc->createElement('AmtDtls');

        // Create the <TxAmt> element and set its value with Ccy attribute
        $txAmt = $doc->createElement('TxAmt');
        $amtValue = '30.00 EUR'; // Replace with your desired value
        list($txAmount, $txCurrency) = sscanf($amtValue, '%f %s');
        $txAmtAmt = $doc->createElement('Amt', $txAmount);
        $txAmtAmt->setAttribute('Ccy', $txCurrency);
        $txAmt->appendChild($txAmtAmt);
        $amtDtls->appendChild($txAmt);

        // Append the AmtDtls element to TxDtls
        $txDtls->appendChild($amtDtls);

        // Add the specified elements as a template
        $bkTxCd = $doc->createElement('BkTxCd');

        $domn = $doc->createElement('Domn');
        $cd = $doc->createElement('Cd', 'PMNT');
        $fmly = $doc->createElement('Fmly');
        $fmlyCd = $doc->createElement('Cd', 'IDDT');
        $subFmlyCd = $doc->createElement('SubFmlyCd', 'ESDD');
        $fmly->appendChild($fmlyCd);
        $fmly->appendChild($subFmlyCd);
        $domn->appendChild($cd);
        $domn->appendChild($fmly);

        $prtry = $doc->createElement('Prtry');
        $cd = $doc->createElement('Cd', 'NCOL+192+00807');
        $issr = $doc->createElement('Issr', 'DK');
        $prtry->appendChild($cd);
        $prtry->appendChild($issr);

        $bkTxCd->appendChild($domn);
        $bkTxCd->appendChild($prtry);
        $txDtls->appendChild($bkTxCd);

        // Create the <RltdPties> element
        $rltdPties = $doc->createElement('RltdPties');

        // Add dummy values to <Dbtr>
        $nmElement = $drctDbtTxInf->getElementsByTagName('Nm')->item(0);
        $nmValue = $nmElement ? $nmElement->textContent : '';
        $rltdPties->appendChild($doc->createElement('Dbtr'))->appendChild($doc->createElement('Nm', $nmValue));


        // Add dummy values to <DbtrAcct>
        $dbtrIBAN = generateRandomIBAN();
        $rltdPties->appendChild($doc->createElement('DbtrAcct'))->appendChild($doc->createElement('Id'))->appendChild($doc->createElement('IBAN', $dbtrIBAN));

        // Create the <Cdtr> element as specified
        $cdtr = $doc->createElement('Cdtr');
        $cdtrName = $doc->createElement('Nm', $acctOwnrNm);
        $cdtr->appendChild($cdtrName);
        $rltdPties->appendChild($cdtr);

        // Add dummy values to <CdtrAcct>
        $cdtrIBAN = 'DE52430609677023575300';
        $rltdPties->appendChild($doc->createElement('CdtrAcct'))->appendChild($doc->createElement('Id'))->appendChild($doc->createElement('IBAN', $cdtrIBAN));

        // Add the <RltdPties> element to the <TxDtls> element
        $txDtls->appendChild($rltdPties);

        // Create the <RltdAgts> element with <DbtrAgt> and <FinInstnId>
        $rltdAgts = $doc->createElement('RltdAgts');
        $dbtrAgt = $doc->createElement('DbtrAgt');
        $finInstnId = $doc->createElement('FinInstnId');
        $bic = $doc->createElement('BIC', 'INGDDEFFXXX'); // Replace with the actual BIC
        $finInstnId->appendChild($bic);
        $dbtrAgt->appendChild($finInstnId);
        $rltdAgts->appendChild($dbtrAgt);
        $txDtls->appendChild($rltdAgts);
        $rmtInf = $doc->createElement('RmtInf');

        // Extract the Ustrd data from the pain.008 file and set its value
        $ustrd = $drctDbtTxInf->getElementsByTagName('Ustrd')->item(0)->textContent;
        $rmtInf->appendChild($doc->createElement('Ustrd', $ustrd));

        // Add the <RmtInf> element to the <TxDtls> element
        $txDtls->appendChild($rmtInf);


        // Add the TxDtls element to NtryDtls
        $ntryDtls->appendChild($txDtls);

        // Calculate and accumulate the transaction amount
        $totalAmount += $txAmount;
    }
    $amt = $doc->createElement('Amt', number_format($totalAmount, 2, '.', ''));
    $amt->setAttribute('Ccy', 'EUR');
    $ntry->appendChild($amt);

    $ntry->appendChild($doc->createElement('CdtDbtInd', 'CRDT'));
    $ntry->appendChild($doc->createElement('Sts', 'BOOK'));

    $bookgDt = $doc->createElement('BookgDt');
    $bookgDt->appendChild($doc->createElement('Dt', '2022-04-04'));
    $ntry->appendChild($bookgDt);

    $valDt = $doc->createElement('ValDt');
    $valDt->appendChild($doc->createElement('Dt', '2022-04-04'));
    $ntry->appendChild($valDt);

    $ntry->appendChild($doc->createElement('AcctSvcrRef', '2022040400555880000'));

    $bkTxCd = $doc->createElement('BkTxCd');
    $domn = $doc->createElement('Domn');
    $domn->appendChild($doc->createElement('Cd', 'PMNT'));

    $fmly = $doc->createElement('Fmly');
    $fmly->appendChild($doc->createElement('Cd', 'IDDT'));
    $fmly->appendChild($doc->createElement('SubFmlyCd', 'ESDD'));
    $domn->appendChild($fmly);

    $prtry = $doc->createElement('Prtry');
    $prtry->appendChild($doc->createElement('Cd', 'NCOL+192+00807'));
    $prtry->appendChild($doc->createElement('Issr', 'DK'));

    $bkTxCd->appendChild($domn);
    $bkTxCd->appendChild($prtry);

    $ntry->appendChild($bkTxCd);

    $ntry->appendChild($ntryDtls);
}

if ($inputSourceType === 'reference-collection') {
// Open and read the CSV file
    $file = fopen($inputFile, 'r');

// Check if the file could be opened
    if ($file !== false) {
        // Read the header row from the CSV
        $header = fgetcsv($file);

        // Check if the header row contains the required columns
        if ($header !== false && in_array('reference', $header) && in_array('amount', $header)) {
            // Loop through the lines in the CSV file
            while (($row = fgetcsv($file)) !== false) {
                // Access data using column indices or associative array
                $reference = $row[array_search('reference', $header)];
                $amount = $row[array_search('amount', $header)];
                #name = generateDummyName();
                $txDtls = $doc->createElement('TxDtls');

                // Create the <Refs> element
                $refs = $doc->createElement('Refs');

                // Set values for the <AcctSvcrRef>, <InstrId>, and <Ref> elements
                $acctSvcrRef = $doc->createElement('AcctSvcrRef', '21011XXXYYY');
                $instrId = $doc->createElement('InstrId', '444-1-1');
                $prtry = $doc->createElement('Prtry');
                $tp = $doc->createElement('Tp', '01');
                $ref = $doc->createElement('Ref', '99999');

                // Create the <TxAmt> element and set its value with Ccy attribute
                $txAmt = $doc->createElement('TxAmt');
                $txAmtAmt = $doc->createElement('Amt', $amount);
                $txAmtAmt->setAttribute('Ccy', 'CHF');

                // Create the <CdtDbtInd> element and set its value
                $txCdtDbtInd = $doc->createElement('CdtDbtInd','CRDT');

                // Create the <BkTxCd> element
                $bkTxCd = $doc->createElement('BkTxCd');
                $domn = $doc->createElement('Domn');
                $cd = $doc->createElement('Cd', 'PMNT');
                $fmly = $doc->createElement('Fmly');
                $fmlyCd = $doc->createElement('Cd', 'RCDT');
                $subFmlyCd = $doc->createElement('SubFmlyCd', 'AUTT');
                $fmly->appendChild($fmlyCd);
                $fmly->appendChild($subFmlyCd);
                $domn->appendChild($cd);
                $domn->appendChild($fmly);
                $bkTxCd->appendChild($domn);


                // Create the <RltdPties> element
                $rltdPties = $doc->createElement('RltdPties');

                $dbtr = $doc->createElement('Dbtr');

                $dbtrName = generateDummyName();
                $dbtrName = $doc->createElement('Nm', $dbtrName); // Replace with the appropriate name from your data
                $dbtr->appendChild($dbtrName);

                $pstlAdr = $doc->createElement('PstlAdr');
                $strtNm = $doc->createElement('StrtNm', 'Sonnenrain'); // Replace with the appropriate street name
                $bldgNb = $doc->createElement('BldgNb', '8'); // Replace with the appropriate building number
                $pstCd = $doc->createElement('PstCd', '3063'); // Replace with the appropriate postal code
                $twnNm = $doc->createElement('TwnNm', 'Ittigen'); // Replace with the appropriate town name
                $ctry = $doc->createElement('Ctry', 'CH'); // Replace with the appropriate country code

                $pstlAdr->appendChild($strtNm);
                $pstlAdr->appendChild($bldgNb);
                $pstlAdr->appendChild($pstCd);
                $pstlAdr->appendChild($twnNm);
                $pstlAdr->appendChild($ctry);

                $dbtr->appendChild($pstlAdr);

                $dbtrAcct = $doc->createElement('DbtrAcct');
                $dbtrIBAN = generateRandomIBAN();
                $Id = $doc->createElement('Id');
                $Iban = $doc->createElement('IBAN', $dbtrIBAN); // Replace with the appropriate IBAN from your data
                $Id->appendChild($Iban);
                $dbtrAcct->appendChild($Id);

                $rltdPties->appendChild($dbtr);
                $rltdPties->appendChild($dbtrAcct);

                $rltdAgts = $doc->createElement('RltdAgts');

                $dbtrAgt = $doc->createElement('DbtrAgt');

                $finInstnId = $doc->createElement('FinInstnId');
                $bic = $doc->createElement('BICFI', 'POFICHBEXXX'); // Replace with the appropriate BIC from your data
                $nm = $doc->createElement('Nm', 'POSTFINANCE AG'); // Replace with the appropriate name from your data

                $finInstnId->appendChild($bic);
                $finInstnId->appendChild($nm);

                $dbtrAgt->appendChild($finInstnId);

                $rltdAgts->appendChild($dbtrAgt);

                // Create the <RmtInf> element
                $rmtInf = $doc->createElement('RmtInf');
                $strd = $doc->createElement('Strd');
                $cdtrRefInf = $doc->createElement('CdtrRefInf');
                $cdtrRefInfTp = $doc->createElement('Tp');
                $cdOrPrtry = $doc->createElement('CdOrPrtry');
                $refPrtry = $doc->createElement('Prtry', 'ISR Reference');
                $cdOrPrtry->appendChild($refPrtry);
                $cdtrRefInfTp->appendChild($cdOrPrtry);
                $cdtrRefInf->appendChild($cdtrRefInfTp);
                $strd->appendChild($cdtrRefInf);

                // Add Reference
                $refIsr = $doc->createElement('Ref', $reference);
                $cdtrRefInf->appendChild($refIsr);
                // Add <AddtlRmtInf> element
                $addtlRmtInf = $doc->createElement('AddtlRmtInf', '?REJECT?0');

                $strd->appendChild($addtlRmtInf);
                $rmtInf->appendChild($strd);

                // Create the <RltdDts> element
                $rltdDts = $doc->createElement('RltdDts');
                $accptncDtTm = $doc->createElement('AccptncDtTm', $acctCreDtTm); // Assuming 'acceptance_date' is the CSV column name
                $rltdDts->appendChild($accptncDtTm);



                $prtry->appendChild($tp);
                $prtry->appendChild($ref);
                $refs->appendChild($acctSvcrRef);
                $refs->appendChild($instrId);
                $refs->appendChild($prtry);
                $txDtls->appendChild($refs);
                $txDtls->appendChild($txAmtAmt);
                $txDtls->appendChild($txCdtDbtInd);
                $txDtls->appendChild($bkTxCd);
                $txDtls->appendChild($rltdPties);
                $txDtls->appendChild($rltdAgts);
                $txDtls->appendChild($rmtInf);
                $txDtls->appendChild($rltdDts);


                // Create and set values for other elements (Amt, CdtDbtInd, BkTxCd, RltdPties, RltdAgts, RmtInf, RltdDts) here

                // Append the <TxDtls> element to <NtryDtls>
                $ntryDtls->appendChild($txDtls);

                // Process data as needed
                echo "Reference: $reference, Amount: $amount" . PHP_EOL;
                $totalAmount += $amount;
            }
        } else {
            // The CSV file does not have the required headers
            echo 'The CSV file is missing required headers: reference and/or amount';
        }

        // Close the file
        fclose($file);
    } else {
        // Unable to open the file
        echo 'Error opening the CSV file';
    }

    $amt = $doc->createElement('Amt', number_format($totalAmount, 2, '.', ''));
    $amt->setAttribute('Ccy', 'EUR');
    $ntry->appendChild($amt);

    $ntry->appendChild($doc->createElement('CdtDbtInd', 'CRDT'));
    $ntry->appendChild($doc->createElement('Sts', 'BOOK'));

    $bookgDt = $doc->createElement('BookgDt');
    $bookgDt->appendChild($doc->createElement('Dt', '2022-04-04'));
    $ntry->appendChild($bookgDt);

    $valDt = $doc->createElement('ValDt');
    $valDt->appendChild($doc->createElement('Dt', '2022-04-04'));
    $ntry->appendChild($valDt);

    $ntry->appendChild($doc->createElement('AcctSvcrRef', '2022040400555880000'));

    $bkTxCd = $doc->createElement('BkTxCd');
    $domn = $doc->createElement('Domn');
    $domn->appendChild($doc->createElement('Cd', 'PMNT'));

    $fmly = $doc->createElement('Fmly');
    $fmly->appendChild($doc->createElement('Cd', 'RCDT'));
    $fmly->appendChild($doc->createElement('SubFmlyCd', 'VCOM'));
    $domn->appendChild($fmly);

    $prtry = $doc->createElement('Prtry');
    $prtry->appendChild($doc->createElement('Cd', 'NCOL+192+00807'));
    $prtry->appendChild($doc->createElement('Issr', 'DK'));

    $bkTxCd->appendChild($domn);
    $bkTxCd->appendChild($prtry);

    $ntry->appendChild($bkTxCd);

    $ntry->appendChild($ntryDtls);

}



// Save the XML to a file in the output directory
$outputDirectory = './output/'; // Replace with your output directory path

// Check if the output directory exists, and create it if not
if (!is_dir($outputDirectory)) {
    mkdir($outputDirectory, 0777, true);
}

$outputFileName = pathinfo($inputFileName, PATHINFO_FILENAME) . $inputSourceType . '-camt054.xml';
$outputFilePath = $outputDirectory . $outputFileName;

// Save the XML to the output file
$doc->formatOutput = true;
$doc->save($outputFilePath);

echo 'XML file generated and saved to: ' . $outputFilePath . PHP_EOL;
?>
