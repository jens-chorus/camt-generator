<?php
require_once 'Helper.php';
// ReferenceCollectionProcessor.php

class ReferenceCollectionProcessor {

    private $config;

    public function __construct($config) {
        $this->config = $config;
    }
    public function processReferenceCollection($inputFile,$doc,$ntryDtls,$ntry) {
        $helper = new Helper();
        $totalAmount = 0;

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

                    $dbtrName = $helper->generateDummyName();
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
                    $dbtrIBAN = $helper->generateRandomIBAN();
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
                    $accptncDtTm = $doc->createElement('AccptncDtTm', $this->config['acct']['credttm']); // Assuming 'acceptance_date' is the CSV column name
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

    // Add other methods as needed
}


