<?php


require_once 'nusoap.php';


class Docmailing
{
	//public $sUsr                ="username";
	public $sUsr                  = "nevergivein";
	public $sPwd                  = "nevergivein111";
	public $NameTitle             = "John";
	public $FirstName             = "Paul";
	public $LastName              =  "Saeed";
	public $sAddress1             =  "Easter Inch Court";
	public $sAddress2             =  "Easter Inch Business Park";
	public $sAddress3             =  "Bathgate";
	public $sAddress4             =  "West Lothian";
	public $sAddress5             =  "sAddress5";
	public $sPostCode             =  "EH48 2FJ";
	public $wsdl                  =  "https://www.cfhdocmail.com/TestAPI2/DMWS.asmx?WSDL";

	public $sMailingName          =  "new class";
	public $sCallingApplicationID =  "PHPCodeTemplate";
	public $sTemplateName         =  "My Test Template";
	public $sTemplateFileName     =  "../app/libraries/docmailing/src/sample.pdf";
	public $TemplateFile          =  "../app/libraries/docmailing/src/sample.pdf";
	public $bColour               =  true;
	public $bDuplex               =  true;
	public $eDeliveryType         =  "First";
	public $eAddressNameFormat    =  "Full Name";
	public $ProductType           =  "A4Letter";
	public $DocumentType          =  "A4Letter";

	public function senddocmail()
	{
		// Flag for outputting debug print messages
		$debug = false;
		$fileContentsDebug = false;
		$callParamArrayDebug = false;

		try {
			// human-readable web service definition for all available calls can be found here:   https://www.cfhdocmail.com/TestAPI2/DMWS.asmx
			//Test URL
			//$wsdl = "https://www.cfhdocmail.com/Test_SimpleAPI/DocMail.SimpleAPI.asmx?WSDL";
			$wsdl = $this->wsdl;
			//Live URL
			//$wsdl = "https://www.cfhdocmail.com/LiveAPI2/DMWS.asmx?WSDL";
			$sMailingName          = $this->sMailingName;  // "Your reference" for this mailing (information)
			$sCallingApplicationID = $this->sCallingApplicationID;   // could be useful to show your appliction name in docmail (information)
			$sTemplateName         = $this->sTemplateName;// friendly name in docmail for your template file (information)
			$sTemplateFileName     = $this->sTemplateFileName;// file name to be passed to docmail for your template file (information)
			$TemplateFile          = $this->TemplateFile;           // filename (in this case the file is on the root of the webserver!)
			$bColour               = $this->bColour;                // Print in colour?
			$bDuplex               = $this->bDuplex;                // Print on both sides of paper?
			$eDeliveryType         = $this->eDeliveryType;// First, Standard or Courier - to get the BEST benefit use Standard
			$eAddressNameFormat    = $this->eAddressNameFormat;//How the name appears in the envelope address  “Full Name”, “Firstname Surname”, “Title Initial Surname”,“Title Surname”, or “Title Firstname Surname”
			$ProductType           = $this->ProductType;	//ProductType (on Mailing): “A4Letter”, “BusinessCard”, “GreetingCard”, or “Postcard”
			$DocumentType          = $this->DocumentType; //DocumentType (on Templates - selects the sub-type for a given template): “A4Letter”, “BusinessCard”,“GreetingCardA5”, “PostcardA5”, “PostcardA6”, “PostcardA5Right” or “PostcardA6Right”

			//used in adding an address list file
			//$AddressFile = "test.csv";           // address CSV file filename (in this case the file is on the root of the webserver!)

			//used in adding a single address
			$NameTitle = $this->NameTitle;		   //recipient title/saultation
			$FirstName = $this->FirstName;	   //recipient 1st name
			$LastName  = $this->LastName;		   //recipient surname
			$sAddress1 = $this->sAddress1;         // Address line 1
			$sAddress2 = $this->sAddress2;         // Address line 2
			$sAddress3 = $this->sAddress3;       // Address line 3
			$sAddress4 = $this->sAddress4;          // Address line 4
			$sAddress5 = $this->sAddress5;
			$sPostCode = $this->sPostCode;       // PostCode    // PostCode


			//$sUsr					 = Config::get('myconfig.esendexUsername');
			//$sPwd					 = Config::get('myconfig.esendexPassowrd');

			$sMailingDescription ="" ;
			$bIsMono = !$bColour  ;
			$sDespatchASAP =true ;
			$sDespatchDate ="" ;
			$ProofApprovalRequired = false; //false = Automatically approve the order without returning a proof


			/********** Added variables used in ProcessMaiing to return error and success messages on completion of async processing ***********/

			$EmailOnProcessMailingError = "";	//email address
			$EmailOnProcessMailingSuccess = "";	//email address

			$HttpPostOnProcessMailingError = "";     //URL on your server set up to handle callbacks
			$HttpPostOnProcessMailingSuccess = "";   //URL on your server set up to handle callbacks

			//true  = proof approval requried.
			//	call ProcessMailing  with Submit=0 PartialProcess=1 to approve the proof and submit the mailing
			//	Poll GetStatus to check that proof is ready (loop)  'Mailing submitted', 'Mailing processed' or 'Partial processing complete' mean the proof is ready
			//	call GetProofFile to return the proof file data
			//	call ProcessMailing  with Submit=1 PartialProcess=0 to approve the proof and submit the mailing
			// Setup nusoap client
			$client = new nusoap_client($wsdl, true); //PHP5 now has is't own soapclient class, so use nusoap_client to avoid clash
			// Increase soap client timeout
			$client->timeout = 240;
			// Increase php script server timeout
			set_time_limit(240);

			error_reporting(E_ALL);

			///////////////////////
			// CreateMailing  - Setup array to pass into webservice call
			///////////////////////

			// Setup array to pass into webservice call
			$arr = [
				"Username"            => $this->sUsr,
				"Password"            => $this->sPwd,
				"CustomerApplication" => $sCallingApplicationID,
				"ProductType"         => $ProductType,
				"MailingName"         => $sMailingName,
				"MailingDescription"  => $sMailingDescription,
				"IsMono"              => $bIsMono,
				"IsDuplex"            => $bDuplex,
				"DeliveryType"        => $eDeliveryType,
				"DespatchASAP"        => $sDespatchASAP,
				//"DespatchDate"      => $sDespatchDate,   //only include if delayed despatch is required
				"AddressNameFormat"   => $eAddressNameFormat,
				"ReturnFormat"        => "Text"
			];
			// other available params listed here:  https://www.cfhdocmail.com/TestAPI2/DMWS.asmx?op=CreateMailing
			print "<b>About to call CreateMailing</b><br>";
			if ($callParamArrayDebug) print_r($arr)."<br>";
			$result = $client->call("CreateMailing",$arr);
			if ($debug) {print_r($result["CreateMailingResult"]);   print "<br> " ;}
			$this->CheckError($result["CreateMailingResult"]);   //parse & check error fields from result as described above

			print "get mailing guid  <br>" ;
			$MailingGUID = $this->GetFld($result["CreateMailingResult"],"MailingGUID");//parse the value  for key 'MailingGUID' from $result

			///////////////////////
			//Add Single Address   - use this to add a single address by setting up array to pass into webservice call
			///////////////////////

			$arr = [
				"Username"     => $this->sUsr,
				"Password"     => $this->sPwd,
				"MailingGUID"  => $MailingGUID,
				"Title"        => $NameTitle,
				"FirstName"    => $FirstName,
				"Surname"      => $LastName,
				"Address1"     => $sAddress1,
				"Address2"     => $sAddress2,
				"Address3"     => $sAddress3,
				"Address4"     => $sAddress4,
				"Address5"     => $sAddress5,
				"Address6"     => $sPostCode,
				"ReturnFormat" => "Text"
			];
			print "<br>About to call AddAddress for MailingGUID ".$MailingGUID."<br>";
			if ($callParamArrayDebug) print_r($arr)."<br>";
			$result = $client->call("AddAddress",$arr);
			if ($debug) {print_r($result["AddAddressResult"]);   print "<br> " ;}
			$this->CheckError($result["AddAddressResult"]);   //parse & check error fields from result as described above


			///////////////////////
			//Add Address File  - use this to add a file of addresses (in CSV format with a header row) - Read in $AddressFile file data and Setup array to pass into webservice call
			///////////////////////

			// Load contents of the Address CSV file into base-64 array to pass across SOAP
			// for example the Address CSV file is at the root of the webserver
			/*
			$AddressFileHandle = fopen($AddressFile, "rb");
			$AddressFileContents = base64_encode(fread($AddressFileHandle, filesize($AddressFile)));
			fclose($AddressFileHandle);
			if ($debug) {
				print "address file is " .filesize($AddressFile) ." bytes<br>";
				if ($fileContentsDebug) {
					print "Contents of file:<br>";
					print_r($AddressFileContents);
					print "<br><br>";
				}
			}

			$arr = [
				"Username" => $sUsr,
				"Password" => $sPwd,
				"MailingGUID" => $MailingGUID,
				"FileName" => $AddressFile,
				"FileData" => $AddressFileContents,
				"HasHeaders" => True,
				"ReturnFormat" => "Text"
			];
			print "<b>About to call AddMailingListFile for MailingGUID</b> ".$MailingGUID."<br>";
			if ($callParamArrayDebug) print_r($arr)."<br>";
			$result = $client->call("AddMailingListFile",$arr);
			if ($debug) {print_r($result["AddMailingListFileResult"]);   print "<br> " ;}
			$this->CheckError($result["AddMailingListFileResult"]);
			*/
			///////////////////////
			// AddTemplateFile  - Read in $TemplateFile file data and Setup array to pass into webservice call
			///////////////////////

			// Load contents of word file into base-64 array to pass across SOAP
			// for example the word file is at the root of the webserver
			$TemplateHandle = fopen($TemplateFile, "rb");
			$TemplateContents = base64_encode(fread($TemplateHandle, filesize($TemplateFile)));
			fclose($TemplateHandle);
			if ($debug) {
				print "file is " .filesize($TemplateFile) ." bytes<br>";
				if ($fileContentsDebug) {
					print "Contents of file:<br>";
					print_r($TemplateContents);
					print "<br><br>";
				}
			}

			$arr = [
				"Username"          => $this->sUsr,
				"Password"          => $this->sPwd,
				"MailingGUID"       => $MailingGUID,
				"DocumentType"      => $DocumentType,
				"TemplateName"      => $sTemplateName,
				"FileName"          => $sTemplateFileName,
				"FileData"          => $TemplateContents,
				"AddressedDocument" => true,
				"Copies"            => 1,
				"ReturnFormat"      => "Text"
			];
			// other available params listed here:  https://www.cfhdocmail.com/TestAPI2/DMWS.asmx?op=AddTemplateFile
			print "<b>About to call AddTemplateFile for MailingGUID</b> ".$MailingGUID."<br>";
			if ($callParamArrayDebug) print_r($arr)."<br>";
			$result = $client->call("AddTemplateFile",$arr);
			if ($debug) {print_r($result["AddTemplateFileResult"]);   print "<br> " ;}
			$this->CheckError($result["AddTemplateFileResult"]);


			///////////////////////
			// ProcessMailing - Setup array to pass into webservice call
			///////////////////////
			$arr = [
				"Username"                   => $this->sUsr,
				"Password"                   => $this->sPwd,
				"MailingGUID"                => $MailingGUID,
				"CustomerApplication"        => $sCallingApplicationID,
				"SkipPreviewImageGeneration" => false,
				"Submit"                     => !$ProofApprovalRequired , //auto submit when approval is not requried
				"PartialProcess"             =>  $ProofApprovalRequired, //fully process when approval is not requried
				"Copies"                     => 1,
				"ReturnFormat"               => "Text",
				"EmailSuccessList"           => 	$EmailOnProcessMailingSuccess,
				"EmailErrorList"             => 	$EmailOnProcessMailingError,
				"HttpPostOnSuccess"          => 	$HttpPostOnProcessMailingSuccess,
				"HttpPostOnError"            =>	$HttpPostOnProcessMailingError
			];
			// there are useful parameters that you may wish to include on this call which enable asynchronous notifications of successes and fails of automated orders to be sent to you via email or HTTP Post:
			//		EmailSuccessList,EmailErrorList
			//		HttpPostOnSuccess,HttpPostOnError
			// other available params listed here:  https://www.cfhdocmail.com/TestAPI2/DMWS.asmx?op=ProcessMailing
			print "<b>About to call ProcessMailing</b><br>";
			if ($callParamArrayDebug) print_r($arr)."<br>";
			$result = $client->call("ProcessMailing",$arr);
			if ($debug) {print_r($result["ProcessMailingResult"]);   print "<br> " ;}
			$this->CheckError($result["ProcessMailingResult"]);

			//Example polling loop function to wait & confirm the processing from ProcessMailing has completed
			if ($ProofApprovalRequired) {
				$status = $this->WaitForProcessMailingStatus($client,$this->sUsr,$this->sPwd,$MailingGUID,"Partial processing complete",$callParamArrayDebug, $debug);
			}
			else{
				$status = $this->WaitForProcessMailingStatus($client,$this->sUsr,$this->sPwd,$MailingGUID,"Mailing submitted",$callParamArrayDebug, $debug);
			}

			return [$MailingGUID, $status];


			/*
			//additional calls that may be useful:

			///////////////////////
			// GetStatus - Setup array to pass into webservice call
			///////////////////////
			$arr = [
				"Username" => $sUsr,
				"Password" => $sPwd,
				"MailingGUID" => $MailingGUID,
				"ReturnFormat" => "Text"
			];
			// other available params listed here:  (https://www.cfhdocmail.com/TestAPI2/DMWS.asmx?op=GetStatus) returns the status of a mailing from the mailing guid
			print "<b>About to call GetStatus</b><br>";
			if ($callParamArrayDebug) print_r($arr)."<br>";
			$result = $client->call("GetStatus",$arr);
			if ($debug) {print_r($result["GetStatusResult"]);   print "<br> " ;}
			CheckError($result["GetStatusResult"]);


			///////////////////////
			// GetProofFile - Setup array to pass into webservice call
			///////////////////////
			//NOTE:  Status must that the show last "ProcessMailing"	call is complete before a proof can be returned.

			$arr = [
				"Username" => $sUsr,
				"Password" => $sPwd,
				"MailingGUID" => $MailingGUID,
				"ReturnFormat" => "Text"
			];
			//  other available params listed here:  (GetProofFile (https://www.cfhdocmail.com/TestAPI2/DMWS.asmx?op=GetProofFile) returns the file data of the PDF proof if it has been generated.
			print "<b>About to call GetProofFile</b><br>";
			if ($callParamArrayDebug) print_r($arr)."<br>";
			$result = $client->call("GetProofFile",$arr);
			if ($debug) {print_r($result["GetProofFileResult"]);   print "<br> " ;}
			CheckError($result["GetProofFileResult"]);

			///////////////////////
			// ProcessMailing - With "Submit" and "PartialProcess" flags set to APPROVE the mailing - Setup array to pass into webservice call
			///////////////////////
			$arr = [
				"Username" => $sUsr,
				"Password" => $sPwd,
				"MailingGUID" => $MailingGUID,
				"CustomerApplication" => $sCallingApplicationID,
				"SkipPreviewImageGeneration" => false,
				"Submit" => true, //auto submit
				"PartialProcess" =>  false, //fully process
				"Copies" => 1,
				"ReturnFormat" => "Text",
				"EmailSuccessList" => 	$EmailOnProcessMailingSuccess,
				"EmailErrorList" => 	$EmailOnProcessMailingError,
				"HttpPostOnSuccess" => 	$HttpPostOnProcessMailingSuccess,
				"HttpPostOnError" =>	$HttpPostOnProcessMailingError
			];
			// there are useful parameters that you may wish to include on this call which enable asynchronous notifications of successes and fails of automated orders to be sent to you via email or HTTP Post:
			//		EmailSuccessList,EmailErrorList
			//		HttpPostOnSuccess,HttpPostOnError
			// other available params listed here:  https://www.cfhdocmail.com/TestAPI2/DMWS.asmx?op=ProcessMailing
			if ($debug) print "About to call ProcessMailing<br>";
			if ($callParamArrayDebug) print_r($arr)."<br>";
			$result = $client->call("ProcessMailing",$arr);
			if ($debug) {print_r($result["ProcessMailingResult"]);   print "<br> " ;}
			CheckError($result["ProcessMailingResult"]);

			*/


		}
		catch (Exception $e) {
			print "PROBLEM:" .$e->getMessage() ."<br>";
			var_dump($e->getMessage());
		}

	}


	function WaitForProcessMailingStatus($client,$sUsr,$sPwd,$MailingGUID,$ExpectedStatus,$callParamArrayDebug, $debug){
		///////////////////////
		// GetStatus - Setup array to pass into webservice call
		///////////////////////
		$arr = [
			"Username" => $this->sUsr,
			"Password" => $this->sPwd,
			"MailingGUID" => $MailingGUID,
			"ReturnFormat" => "Text"
		];

		//poll GetStatus in a loop until the processing has completed
		//loop a maximum of 10 times, with a 10 second delay between iterations.
		//	alternatively; handle callbacks from the HttpPostOnSuccess & HttpPostOnError parameters on ProcessMailing to identify when the processing has completed
		$i = 0;
		do {
			// other available params listed here:  (https://www.cfhdocmail.com/TestAPI2/DMWS.asmx?op=GetStatus) returns the status of a mailing from the mailing guid
			print "<b>About to call GetStatus</b><br>";
			if ($callParamArrayDebug) print_r($arr)."<br>";
			$result = $client->call("GetStatus",$arr);

			print "RESULT WAS :" ; print_r($result); print "<br> " ;
			if ($debug) {print_r($result["GetStatusResult"]);   print "<br> " ;}
			$this->CheckError($result["GetStatusResult"]);

			$Status=$this->GetFld($result["GetStatusResult"],"Status");
			$Error=$this->GetFld($result["GetStatusResult"],"Error code");
			print("Status = ".$Status);  print "<br> " ;

			//end loop once processing is complete
			if ($Status== $ExpectedStatus ){break;}	//success
			if ($Status== "Error in processing" ){break;}	//error in processing
			if ($Error ){break;}				//error


			sleep(10);//wait 10 seconds before repeasting
			++$i;
		} while ($i < 10);

		//
		if ($Status== "Error in processing") {
			//get description of error in processing
			$arr = [
				"Username" => $this->sUsr,
				"Password" => $this->sPwd,
				"MethodName" => "GetProcessingError",
				"ReturnFormat" => "Text",
				"Properties" => [
									"PropertyName" => "GetProcessingError",
									"PropertyValue" => $MailingGUID
								]
			];
			print "<b>About to call ExtendedCall: GetProcessingError</b><br>";
			if ($callParamArrayDebug) print_r($arr)."<br>";
			$result = $client->call("ExtendedCall",$arr);
			$this->CheckError($result["ExtendedCallResult"]);
			print_r($result["ExtendedCallResult"]);   print "<br> " ;
		}

		//TODO:	handle the status not being reached appropriately for your system
		if ($Status!= $ExpectedStatus) {print("WARNING: exepcted status".$ExpectedStatus." not reached.  Current status: ".$Status);}

		return $Status;
	}
	function CheckError($Res){
		print "Checking for errors<br>";
		//check for  the keys 'Error code', 'Error code string' and 'Error message' to test/report errors
		$errCode = $this->GetFld($Res,"Error code");
		if ($errCode) {
			$errName = $this->GetFld($Res,"Error code string");
			$errMsg = $this->GetFld($Res,"Error message");
			print 'ErrCode '; print_r($errCode); print "<br>";
			print 'ErrName '; print_r($errName); print "<br>";
			print 'ErrMsg '; print_r($errMsg); print "<br>";
			// throw new Exception("<h2>There was an error:</h2> ".$errCode." ".$errName." - ".$errMsg);

		}
		print "No error<br>";
	}
	function GetFld($FldList,$FldName){
		// calls return a multi-line string structured as :
		// [KEY]: [VALUE][carriage return][line feed][KEY]: [VALUE][carriage return][line feed][KEY]: [VALUE][carriage return][line feed][KEY]: [VALUE]
		//explode lines
		//print "Looking for Field '".$FldName."'<br>";
		$lines = explode("\n",$FldList);
		for ( $lineCounter=0;$lineCounter < count($lines); $lineCounter+=1){
			//explode field/value
			$fields = explode(":",$lines[$lineCounter]);
			//find matching field name
			if ($fields[0]==$FldName)	{
				//print "'".$FldName."' Value: ".ltrim($fields[1], " ")."<br>";
				return ltrim($fields[1], " "); //return value
			}
		}
		//print "'".$FldName."' NOT found<br>";
	}


}