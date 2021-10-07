<?php

class FinalResult
{
    /**
     * 
     * 1) We can create a separate file as well where we manage all the messages. It could be class base or simple array but i believe this class in only for bank detail so we can manage all the messages here. why i create protected variable so that we can access these messages in the subclass as well if needed.
     * 
     * 2) In this example, this class is supposed to be a bank detail class so messages can be easily managed here.
     * 
     * 3) I still don't understand why the first line of CSV is maintaining the messages there is no need for those we can manage all the messages and the response code in our code CSV should have only records. But in this example, I just leave the CSV as it is.
     * 
     * 4) Create an array in a class no need to define it separately. For best coding practice we need to define variables in a meaningful way like array should start with (a) an object should be (o) in this way new engineer will understand the things quickly. And we can decide the notation of defining variables like camel notation or snack notation in this case I use camel notation.
     * 
     * 5) There is no need to check the typecasting of variables as mentioned this code is from a very huge application then no need for typecasting. In PHP it is auto-detected and variables work as it is they have a value. If it is necessary then we can use is_float(mixed $value): bool or is_int(mixed $value): bool functions.
     * **/

    protected $bankAccountNumberMissing   =   "Bank account number missing";
    protected $bankBranchCodeMissing      =   "Bank branch code missing";
    protected $endToEndIdMissing          =   "End to end id missing";
    protected $fileNotFound               =   "Please check the path, there is no file.";
    protected $generalFailureCode         =   100;
    protected $generalFailureMessage      =   'All systems go';
    protected $generalFailureCurrency     =   'SGD';

    private $aRecords;

    function results($f)
    {
        $this->aRecords =   [];
        try {
            if (!file_exists($f)) {
                throw new Exception($this->fileNotFound);
            }
            if (!fopen($f, "r")) {
                throw new Exception($this->fileNotFound);
            }
            if (($document = fopen($f, "r")) !== FALSE) {
                $h = fgetcsv($document);
                while (($r = fgetcsv($document)) !== FALSE) {
                    if (!empty($r)) {
                        $amt = (empty($r[8])) ? 0.0 : $r[8];
                        $ban = (empty($r[6])) ? $this->bankAccountNumberMissing : $r[6];
                        $bac = (empty($r[2])) ? $this->bankBranchCodeMissing : $r[2];
                        $e2e = (empty($r[10] . $r[11])) ? $this->endToEndIdMissing : $r[10] . $r[11];
                        $rcd = [
                            "amount" => [
                                "currency" => $h[0],
                                "subunits" => intval($amt * 100)
                            ],
                            "bank_account_name" => (!empty($r[7])) ? str_replace(" ", "_", strtolower($r[7])) : '',
                            "bank_account_number" => $ban,
                            "bank_branch_code" => $bac,
                            "bank_code" => (!empty($r[0])) ? $r[0] : '',
                            "end_to_end_id" => $e2e,
                        ];
                        $this->aRecords[] = $rcd;
                    }
                }
                fclose($document); // After success reading need to close
                $this->aRecords = array_filter($this->aRecords);
                return [
                    "filename" => basename($f),
                    "document" => $document,
                    "failure_code" => $h[1],
                    "failure_message" => $h[2],
                    "records" => $this->aRecords
                ];
            }
        } catch (Exception $e) {
            return [
                "filename" => basename($f),
                "document" => $this->fileNotFound,
                "failure_code" => $this->generalFailureCode,
                "failure_message" => $e->getMessage(),
                "records" => $this->aRecords
            ];
        }
    }









    function OldFunctionResults($f)
    {
        $d = fopen($f, "r");
        $h = fgetcsv($d);
        $rcs = [];
        while (!feof($d)) {
            $r = fgetcsv($d);
            if (count($r) == 16) {
                $amt = !$r[8] || $r[8] == "0" ? 0 : (float) $r[8];
                $ban = !$r[6] ? "Bank account number missing" : (int) $r[6];
                $bac = !$r[2] ? "Bank branch code missing" : $r[2];
                $e2e = !$r[10] && !$r[11] ? "End to end id missing" : $r[10] . $r[11];
                $rcd = [
                    "amount" => [
                        "currency" => $h[0],
                        "subunits" => (int) ($amt * 100)
                    ],
                    "bank_account_name" => str_replace(" ", "_", strtolower($r[7])),
                    "bank_account_number" => $ban,
                    "bank_branch_code" => $bac,
                    "bank_code" => $r[0],
                    "end_to_end_id" => $e2e,
                ];
                $rcs[] = $rcd;
            }
        }
        $rcs = array_filter($rcs);
        return [
            "filename" => basename($f),
            "document" => $d,
            "failure_code" => $h[1],
            "failure_message" => $h[2],
            "records" => $rcs
        ];
    }
}
