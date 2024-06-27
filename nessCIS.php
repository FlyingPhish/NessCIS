#!/usr/bin/php
<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

error_reporting(E_ERROR | E_PARSE);

echo "\033[96m
                         _______________
   ____  ___  __________/ ____/  _/ ___/
  / __ \/ _ \/ ___/ ___/ /    / / \__ \ 
 / / / /  __(__  (__  / /____/ / ___/ / 
/_/ /_/\___/____/____/\____/___//____/  

\033[0mDoing something that Nessus can't. /Shrug
by @FlyingPhishy\n";

# IDIOT CHECKING <3 
if (!(extension_loaded('simplexml'))) {
  echo "\nPlease install simplexml.\nsudo apt install php-xml\n";
  exit;
}

if (empty($argv[1])) {
  echo "\nPlease specify file: ./nessCIS.php dir/scan.nessus\n";
  exit;
}

# PREP A TEMP FILE
copy($argv[1], 'temp.nessus');

# REPLACE SHITE STRUCTURE FROM NESSUS
$file_contents = file_get_contents('temp.nessus');
$file_contents = str_replace("cm:","",$file_contents);
file_put_contents('temp.nessus',$file_contents);

# PARSE IT 
$xmlFile = simplexml_load_file('temp.nessus');
$parsed = $xmlFile; 

# EXTRACT POLICY NAME AND FORMAT IT
$policyName = (string)$parsed->Policy->policyName;
$formattedPolicyName = str_replace(' ', '_', $policyName) . '_CIS_Results';

# INITIALIZE ARRAYS FOR OUTPUT
$array = [];
$complianceArray = [];

// # FUNCTION TO SANITIZE VALUES
// function sanitize($value) {
//   return htmlspecialchars(trim($value), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
// }

# FUNCTION TO EXTRACT RECOMMENDED STATE
function extractRecommendedState($complianceInfo) {
  if (preg_match('/The recommended state for this setting is:\s*(.+?)(?:\r?\n|$)/', $complianceInfo, $matches)) {
    return trim($matches[1]);
  }
  return '';
}

# LOOP THROUGH EACH ReportHost
foreach ($parsed->Report->ReportHost as $reportHost) {
  $ipAddress = (string) $reportHost->attributes()->name;
  # LOOP THROUGH EACH ReportItem WITHIN THE CURRENT ReportHost
  foreach ($reportHost->ReportItem as $reportItem) {
      $benchmarkName = (string) $reportItem->{'compliance-benchmark-name'};
      $checkName = (string) $reportItem->{'compliance-check-name'};
      $result = (string) $reportItem->{'compliance-result'};
      $complianceSolution = (string) $reportItem->{'compliance-solution'};
      $complianceActualValue = (string) $reportItem->{'compliance-actual-value'};
      $recommendedState = extractRecommendedState((string) $reportItem->{'compliance-info'});
      
      # ONLY ADD TO ARRAY IF RESULT IS NOT EMPTY
      if (!empty($result)) {
          $array[] = [
              'ipAddress' => $ipAddress,
              'benchmarkName' => $benchmarkName,
              'checkName' => $checkName,
              'result' => $result,
              'complianceActualValue' => $complianceActualValue,
              'recommendedState' => $recommendedState
          ];
          
          # Add unique findings to complianceArray
          $complianceKey = $benchmarkName . '|' . $checkName . '|' . $result . '|' . $complianceSolution;
          if (!array_key_exists($complianceKey, $complianceArray)) {
              $complianceArray[$complianceKey] = [
                  'benchmarkName' => $benchmarkName,
                  'checkName' => $checkName,
                  'result' => $result,
                  'complianceSolution' => $complianceSolution
              ];
          }
      }
  }
}

# CREATE XLSX OUTPUT
$spreadsheet = new Spreadsheet();

# ADD DATA TO FIRST SHEET
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('CIS Results');

$sheet->setCellValue('A1', 'IP Address');
$sheet->setCellValue('B1', 'Benchmark Name');
$sheet->setCellValue('C1', 'Check Name');
$sheet->setCellValue('D1', 'Result');
$sheet->setCellValue('E1', 'Compliance Actual Value');
$sheet->setCellValue('F1', 'Recommended State');

$row = 2;
foreach ($array as $line) {
    $sheet->setCellValue('A' . $row, $line['ipAddress']);
    $sheet->setCellValue('B' . $row, $line['benchmarkName']);
    $sheet->setCellValue('C' . $row, $line['checkName']);
    $sheet->setCellValue('D' . $row, $line['result']);
    $sheet->setCellValue('E' . $row, $line['complianceActualValue']);
    $sheet->setCellValue('F' . $row, $line['recommendedState']);
    $row++;
}

# ADD DATA TO SECOND SHEET
$complianceSheet = $spreadsheet->createSheet();
$complianceSheet->setTitle('Control Remediations');

$complianceSheet->setCellValue('A1', 'Benchmark Name');
$complianceSheet->setCellValue('B1', 'Check Name');
$complianceSheet->setCellValue('C1', 'Result');
$complianceSheet->setCellValue('D1', 'Compliance Solution');

$row = 2;
foreach ($complianceArray as $line) {
    $complianceSheet->setCellValue('A' . $row, $line['benchmarkName']);
    $complianceSheet->setCellValue('B' . $row, $line['checkName']);
    $complianceSheet->setCellValue('C' . $row, $line['result']);
    $complianceSheet->setCellValue('D' . $row, $line['complianceSolution']);
    $row++;
}

# WRITE XLSX FILE
$writer = new Xlsx($spreadsheet);
$writer->save($formattedPolicyName . '.xlsx');

echo "\nDone. Please view file " . $formattedPolicyName . ".xlsx\n";
unlink('temp.nessus');
?>