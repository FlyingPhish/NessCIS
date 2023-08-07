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
} else {
  # DO NOTHING
}

if (empty($argv[1])) {
  echo "\nPlease specify file: ./nessCIS.php dir/scan.nessus\n";
  exit;
} else {
  # DO NOTHING
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

# SETTING THE STAGE FOR LOOP
$numberOfNested = $parsed->Report->ReportHost->ReportItem->count();
$array = [];
$i = -1;

while ($i <= $numberOfNested) {
  $item = $parsed->Report->ReportHost->ReportItem[$i];
  # ADD BELOW TO MY NEW ARRAY
  $array[$i]['ipAddress'] = $parsed->Report->ReportHost->attributes()->name;
  $array[$i]['benchmarkName'] = $item[$i]->{'compliance-benchmark-name'};
  $array[$i]['checkName'] = $item[$i]->{'compliance-check-name'};
  $array[$i]['result'] = $item[$i]->{'compliance-result'};

  # REMOVE EMPTY ARRAY KEYS
  $test = strlen($array[$i]['result'][0]);
  if ($test == 0) {
    unset($array[$i]);
  }
  $i++;
}

# CREATE CSV OUTPUT
$outputCSV = fopen('output.csv', 'w');
fwrite($outputCSV, "IP Address,Benchmark Name,Check Name,Result\n");

foreach ($array as $line) {
  fputcsv($outputCSV, $line);
}

fclose($outputCSV);

echo "\nDone. Please view file output.csv\n";
unlink('temp.nessus')
?>