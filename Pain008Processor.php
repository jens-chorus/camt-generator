<?php
require_once 'Helper.php';

// Pain008Processor.php
class Pain008Processor
{
    private $config;

    public function __construct($config) {
        $this->config = $config;
    }

    public function processPainFile($inputFile,$doc,$ntryDtls,$ntry)
    {
        $helper = new Helper();
        $totalAmount = 0;
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
            $txInstdAmt = $instdAmtElement->textContent;
            // Check if the 'Ccy' attribute exists before trying to access it
            if ($instdAmtElement->hasAttribute('Ccy')) {
                $txCurrency = $instdAmtElement->getAttribute('Ccy');
            } else {
                echo "Cannot determine currency from instdAmtElement. Falling back to account currency";
                $txCurrency = $this->config['acct']['ccy'];
            }

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

            $txAmtAmt = $doc->createElement('Amt', $txInstdAmt);
            $txAmtAmt->setAttribute('Ccy', $txCurrency);


            // Append the AmtDtls element to TxDtls
            $txDtls->appendChild($txAmtAmt);

            // Append CdtDbtInd element
            $txCdtDbtInd = $doc->createElement('CdtDbtInd', 'CRDT');
            $txDtls->appendChild($txCdtDbtInd);

            // Add the specified elements as a template
            $bkTxCd = $doc->createElement('BkTxCd');

            $domn = $doc->createElement('Domn');
            $cd = $doc->createElement('Cd', 'PMNT');
            $fmly = $doc->createElement('Fmly');
            $fmlyCd = $doc->createElement('Cd', 'IDDT');
            $subFmlyCd = $doc->createElement('SubFmlyCd', 'PMDD');
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
            $dbtrIBAN = $helper->generateRandomIBAN();
            $rltdPties->appendChild($doc->createElement('DbtrAcct'))->appendChild($doc->createElement('Id'))->appendChild($doc->createElement('IBAN', $dbtrIBAN));

            // Create the <Cdtr> element as specified
            $cdtr = $doc->createElement('Cdtr');
            $cdtrName = $doc->createElement('Nm', $this->config['acct']['ownr']['nm']);
            $cdtr->appendChild($cdtrName);
            $rltdPties->appendChild($cdtr);

            // Add dummy values to <CdtrAcct>
            $cdtrIBAN = $this->config['acct']['iban'];
;
            $rltdPties->appendChild($doc->createElement('CdtrAcct'))->appendChild($doc->createElement('Id'))->appendChild($doc->createElement('IBAN', $cdtrIBAN));

            // Add the <RltdPties> element to the <TxDtls> element
            $txDtls->appendChild($rltdPties);

            // Create the <RltdAgts> element with <DbtrAgt> and <FinInstnId>
            $rltdAgts = $doc->createElement('RltdAgts');
            $dbtrAgt = $doc->createElement('DbtrAgt');
            $finInstnId = $doc->createElement('FinInstnId');
            $bic = $doc->createElement('BICFI', 'INGDDEFFXXX'); // Replace with the actual BIC
            $finInstnId->appendChild($bic);
            $dbtrAgt->appendChild($finInstnId);
            $rltdAgts->appendChild($dbtrAgt);
            $txDtls->appendChild($rltdAgts);
            $rmtInf = $doc->createElement('RmtInf');

            // Extract the Ustrd data from the pain.008 file and set its value
            $ustrdElement = $drctDbtTxInf->getElementsByTagName('Ustrd')->item(0);
            if ($ustrdElement !== null) {
                $ustrd = $ustrdElement->textContent;
            } else {
                // Handle the case where 'Ustrd' element is not found
                $ustrd = 'Generated dummy content';
            }

            // Add the <RmtInf> element to the <TxDtls> element
            $rmtInf->appendChild($doc->createElement('Ustrd', $ustrd));
            $txDtls->appendChild($rmtInf);

            // Add the TxDtls element to NtryDtls
            $ntryDtls->appendChild($txDtls);

            // Calculate and accumulate the transaction amount
            $totalAmount += $txInstdAmt;
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
        $fmly->appendChild($doc->createElement('SubFmlyCd', 'PMNT'));
        $domn->appendChild($fmly);

        $prtry = $doc->createElement('Prtry');
        $prtry->appendChild($doc->createElement('Cd', 'NCOL+192+00807'));
        $prtry->appendChild($doc->createElement('Issr', 'DK'));

        $bkTxCd->appendChild($domn);
        $bkTxCd->appendChild($prtry);

        $ntry->appendChild($bkTxCd);

        $ntry->appendChild($ntryDtls);
    }

}