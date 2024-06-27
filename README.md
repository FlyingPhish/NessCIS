# NessCIS

NessCIS is a PHP tool designed to extract CIS (Center for Internet Security) benchmarks from a .Nessus file and present them in an easily readable Excel format.

## Features

- Extracts CIS benchmark data from .Nessus files
- Generates an Excel (.xlsx) file with two sheets:
  1. CIS Results: Detailed findings for each IP address with straight-to-the-point recommendations
  2. Control Remediations: Unique findings with compliance solutions
- Uses PhpOffice/PhpSpreadsheet for Excel file generation

## Requirements

- PHP 7.4 or higher
- Composer
- SimpleXML PHP extension
- PhpOffice/PhpSpreadsheet (installed via Composer)

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/FlyingPhish/NessCIS.git
   cd NessCIS
   ```

2. Install dependencies using Composer:
   ```bash
   composer install
   ```

3. Ensure the SimpleXML extension is installed:
   ```bash
   sudo apt install php-xml
   ```

## Usage

Run the script by providing the path to your .Nessus file:

```bash
php nessCIS.php path/to/your/scan.nessus
```

The script will generate an Excel file named after your policy, e.g., `Your_Policy_Name_CIS_Results.xlsx`.

## Output

The generated Excel file contains two sheets:

1. **CIS Results:**
   - IP Address
   - Benchmark Name
   - Check Name
   - Result
   - Compliance Actual Value

2. **Control Remediations:**
   - Benchmark Name
   - Check Name
   - Result
   - Compliance Solution

## Example

![CIS Results Example](https://github.com/FlyingPhish/NessCIS/assets/46652779/1c5852f7-3058-4c11-b1fb-930d7ecb02f1)

![Control Remediations Example](https://github.com/FlyingPhish/NessCIS/assets/46652779/4ac7fd1b-a790-4cb3-b421-2ac39b6da5dd)
