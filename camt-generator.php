<?php
require_once 'vendor/autoload.php'; // Include Composer autoload
use Symfony\Component\Yaml\Yaml;
require_once 'Helper.php';
require_once 'Pain008Processor.php';
require_once 'ReferenceCollectionProcessor.php';

// Create an instance of the Helper class
$helper = new Helper();

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


if ($inputSourceType === 'pain.008') {
    $pain_processor = new Pain008Processor($config);
    $pain_processor->processPainFile($inputFile,$doc,$ntryDtls,$ntry);
}

if ($inputSourceType === 'reference-collection') {
    $reference_collection_processer = new ReferenceCollectionProcessor($config);
    $reference_collection_processer->processReferenceCollection($inputFile,$doc,$ntryDtls,$ntry);
}



// Save the XML to a file in the output directory
$outputDirectory = './output/'; // Replace with your output directory path

// Check if the output directory exists, and create it if not
if (!is_dir($outputDirectory)) {
    mkdir($outputDirectory, 0777, true);
}

$outputFileName = pathinfo($inputFileName, PATHINFO_FILENAME) . "-inputType-" . $inputSourceType . '-outputType-camt054.xml';
$outputFilePath = $outputDirectory . $outputFileName;

// Save the XML to the output file
$doc->formatOutput = true;
$doc->save($outputFilePath);

echo 'XML file generated and saved to: ' . $outputFilePath . PHP_EOL;
?>
