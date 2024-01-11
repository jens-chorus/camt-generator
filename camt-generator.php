<?php
require_once 'vendor/autoload.php'; // Include Composer autoload
use Symfony\Component\Yaml\Yaml;


// Check if an argument for the input file path is provided
if (isset($argv[1])) {
    $inputFile = $argv[1];
} else {
    echo "Usage: php script.php <full_input_file_path>\n";
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
$acctOwnrNm = $config['acct']['ownr']['nm'];
$acctSvcrBic = $config['acct']['svcr']['fin_instn_id']['bic'];
$acctSvcrNm = $config['acct']['svcr']['fin_instn_id']['nm'];
$acctSvcrOthrId = $config['acct']['svcr']['fin_instn_id']['othr']['id'];
$acctSvcrOthrIssr = $config['acct']['svcr']['fin_instn_id']['othr']['issr'];



// Extract the directory path and input file name from the full file path
$inputDirectory = dirname($inputFile);
$inputFileName = basename($inputFile);

// Read and parse the input file
$painXml = file_get_contents($inputFile);
$painDoc = new DOMDocument();
$painDoc->loadXML($painXml);

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
$creDtTm = $doc->createElement('CreDtTm', '2022-04-04T21:43:14.0+02:00');
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
$creDtTm = $doc->createElement('CreDtTm', '2022-04-04T21:43:14.0+02:00');
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
$bic = $doc->createElement('BIC', $acctSvcrBic);
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
    $dbtrIBAN = 'DE89370400440532013000';
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


// Save the XML to a file in the output directory
$outputDirectory = './output/'; // Replace with your output directory path

// Check if the output directory exists, and create it if not
if (!is_dir($outputDirectory)) {
    mkdir($outputDirectory, 0777, true);
}

$outputFileName = pathinfo($inputFileName, PATHINFO_FILENAME) . 'sepadd-camt054.xml';
$outputFilePath = $outputDirectory . $outputFileName;

// Save the XML to the output file
$doc->formatOutput = true;
$doc->save($outputFilePath);

echo 'XML file generated and saved to: ' . $outputFilePath . PHP_EOL;
?>
