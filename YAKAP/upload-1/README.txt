YAKAP Medicine Excel Import - Composer Free

1. Extract this folder to:
   C:\xampp\htdocs\yakap\

2. Make sure these files are together:
   upload.php
   simple_xlsx_reader.php
   medicine_template.xlsx

3. In XAMPP php.ini, enable:
   extension=zip
   extension=simplexml

4. Restart Apache.

5. Open:
   http://localhost/yakap/upload.php

Database:
   Database: yakap
   Table: medicines

Excel columns:
   Medicine Name
   Generic Name
   Dosage Form
   Unit Price
   Status

Supported Excel format:
   .xlsx only

No Composer is required.
