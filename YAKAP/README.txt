YAKAP Version 1.1

Major changes:
- Manual Patient ID (patient_code) throughout patient records.
- Age auto-computed from birthdate; no age column required.
- Patient Profile has one-click Medicine History button.
- Patient Medicine History summarizes different medicines, total quantity, transaction count, and total amount.
- Patient list shows auto-computed Age.
- Edit Patient shows auto-computed Age.
- Login logo slot is /assets/images/yakap-logo.png and CSS uses a smaller 120px logo.

Installation:
1. Copy YAKAP_V1_1 to C:\xampp\htdocs\YAKAP (or rename the folder to YAKAP).
2. Import database/yakap.sql in phpMyAdmin.
3. If your MySQL root password is not blank, edit config/database.php.
4. Open http://localhost/YAKAP/login/index.php

Default login:
Username: admin
Password: admin123

Important:
- The supplied schema stores birthdate, not age. Age is calculated live using MySQL TIMESTAMPDIFF.
- Existing purchase records must use medicine_purchases.patient_id and medicine_purchase_items.purchase_id/medicine_id for Patient Medicine History to display.
