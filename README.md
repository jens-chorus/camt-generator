# CAMT.054 XML Generator

This PHP script generates a CAMT.054 XML file based on input data from a `pain.008` file and uses configuration settings from a `config.yml` file. The generated XML file follows the ISO 20022 standard for financial messaging.

## Getting Started

### Prerequisites

Before running the script, make sure you have PHP installed on your system. You'll also need the `php-xml` extension enabled.

### Installation

1. Clone this repository to your local machine:

   ```bash
   git clone https://github.com/jens-chorus/camt-generator.git
2. Navigate to the project directory:
  ```cd camt-generator```

3. Create a config directory and place your config.yml file inside it. You can use the provided config.example.yml as a template.

4. Place your pain.008 file in the input directory.

## Usage
Run the script with the following command:

```php camt-generator.php path/to/input/pain.008```

Replace path/to/input/pain.008 with the actual path to your pain.008 file.

The script will generate a CAMT.054 XML file and save it in the output directory using the name provided from the input file.


## Configuration

You can customize the script by editing the config.yml file. The following configuration properties are available:

- xmlns: The XML namespace for the CAMT.054 document.
- xmlns_xsi: The XML namespace for xsi (XML Schema Instance).
- xsi_schemaLocation: The xsi schema location.
- ... check the provided config.yml for available options
