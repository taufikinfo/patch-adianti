# AdiantiUploaderService

A robust PHP file upload service built to ensure secure file uploads by checking file types, validating extensions, and detecting malicious content. This service is part of the [Adianti Framework](https://www.adiantiframework.com.br).

## Features

- **File Extension Whitelisting:** Only allows certain file types as defined.
- **Blocked Extensions List:** Blocks dangerous file types like `.php`, `.py`, `.jsp`, and more.
- **MIME Type Validation:** Ensures the uploaded file matches the expected MIME type.
- **Malicious Code Detection:** Detects obfuscated or harmful code (like `eval()` or `base64_decode()`).
- **Permission Handling:** Handles writable directories and file storage gracefully.
- **Logs Suspicious Activity:** Renames suspicious files and logs details to help with incident tracking.

## Prerequisites

- PHP 7.4 or higher
- Web server with PHP support (e.g., Apache, Nginx)
- Ensure the `tmp/` directory is writable

## Installation

1. Clone this repository or download the PHP file.
   ```bash
   git clone https://github.com/taufikinfo/patch-adianti.git
   cd patch-adianti```
   
   Copy AdiantiUploaderService.php into lib/adianti/service 
   Copy SystemDocumentUploaderService.class.php into app/service/system/

sample output
	```	
	[2024-10-28 11:30:45] Upload rejected and file renamed: File Corrupt
	IP Address: 192.168.1.1
	Session ID: abc123
	Session File: /tmp/sess_abc123
	File: dangerous.php
	New File Name: dangerous.php.suspicious-detected
	Line: 10
	Pattern: eval
	```