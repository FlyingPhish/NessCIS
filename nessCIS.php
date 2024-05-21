#!/usr/bin/php
<?php
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

# INITIALIZE ARRAY FOR CSV OUTPUT
$array = [];

# LOOP THROUGH EACH ReportHost
foreach ($parsed->Report->ReportHost as $reportHost) {
    $ipAddress = (string) $reportHost->attributes()->name;
    # LOOP THROUGH EACH ReportItem WITHIN THE CURRENT ReportHost
    foreach ($reportHost->ReportItem as $reportItem) {
        $benchmarkName = (string) $reportItem->{'compliance-benchmark-name'};
        $checkName = (string) $reportItem->{'compliance-check-name'};
        $result = (string) $reportItem->{'compliance-result'};
        
        # ONLY ADD TO ARRAY IF RESULT IS NOT EMPTY
        if (!empty($result)) {
            $array[] = [
                'ipAddress' => $ipAddress,
                'benchmarkName' => $benchmarkName,
                'checkName' => $checkName,
                'result' => $result
            ];
        }
    }
}

# CREATE CSV OUTPUT
$outputCSV = fopen('output.csv', 'w');
fwrite($outputCSV, "IP Address,Benchmark Name,Check Name,Result\n");

foreach ($array as $line) {
  fputcsv($outputCSV, $line);
}

fclose($outputCSV);

echo "\nDone. Please view file output.csv\n";
unlink('temp.nessus');
?>
