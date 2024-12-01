# 6amMart-awgSaas-mod
Modification files to 6amMart for adding whatsapp notifications and Whatsapp OTP
# PLEASE BACKUP THESE 4 ORIGINAL FILES:
/app/CentralLogics/helpers.php
/app/CentralLogics/sms_module.php
/app/Http/Controllers/Admin/BusinessSettingsController.php
/resources/views/admin-views/business-settings/fcm-config.blade.php

1. Download the zip file, Upload it to root of 6amMart site, Extract.
2. Clear cache (delete all files inside /storage/framework/views/ folder)
3. Go to (SYSTEM MANAGEMENT) menu "3rd party & configurations" >> "Firebase Notification" >> "Firebase Configuration"
4. Fill TOKEN (get it from arrocy.com)
5. Click "Submit" to save the settings
