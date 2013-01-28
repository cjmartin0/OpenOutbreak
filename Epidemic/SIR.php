<?php
// Opensource model for the openOutpbreak project
// Chris Martin
// A basic SIR model to calulate the next day's infected, susceptible, recovered compartments in a population.

//get the q parameter from URL
$q=$_GET["q"];   // R0  Duration  Infected  Susceptible  Recovered doPerturb

//Decode the $q
$theData = json_decode($q);
$metres=$theData->Duration/100;

$ipd = $theData->R0/$theData->Duration;  // Number of new infections per day
$iDuration = 1/$theData->Duration;   //Inverse of the duration.
$dTurnover = $theData->Turnover/365;  // Annual turnover to daily turnover.

// Arrays for results
$iArray = array($theData->Infected);
$rArray = array($theData->Recovered);
$sArray = array($theData->Susceptible);

// Daily change values.
$dI=0;
$dR=0;
$dS=0;

for ($i=1; $i<$theData->Days; $i++){
	if ($theData->Perturb){
		$perIPD = $ipd + rand(0,1)*0.1*$ipd;
		$perIDuration = $iDuration+ rand(0,1)*0.1*$iDuration;
		$perDTurnover = $dTurnover+ rand(0,1)*0.1*$dTurnover;
	} else{
		$perIPD = $ipd;
		$perIDuration = $iDuration;
		$perDTurnover = $dTurnover;		
	}
	$dR = $perIDuration * $iArray[$i-1];
	$dS = -$perIPD * $iArray[$i-1]*$sArray[$i-1];
	$dI = -$dS-$dR;
	
	$iArray[$i] = ($iArray[$i-1]+$dI)*(1-$perDTurnover);
	$rArray[$i] = ($rArray[$i-1]+$dR)*(1-$perDTurnover);
	$sArray[$i] = 1-$iArray[$i]-$rArray[$i];
	
}

$resultData = array(
	"Infected"=>$iArray,
	"Recovered"=>$rArray,
	"Susceptible"=>$sArray
);

$theResult = json_encode(array('Results'=>$resultData));
  
//output the response
echo $_GET['callback']. '('. json_encode($theResult) . ')';

//header('Access-Control-Allow-Origin: *');
?>

