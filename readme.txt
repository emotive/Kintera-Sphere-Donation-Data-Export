****************************************************************************************************
Kintera SPHERE Donation DATA Export Script
Author: Chang Xiao (chang@emotivellc.com)
Last update: September, 2010

Description:

The script will export donation information from kintera sphere using the sphere API
to a MySQL database. It is batch controlled and meant to be run periodically to update 
the records
****************************************************************************************************

I. Requirement
II. Installation
III. Configuration
IV. Usage



****************************************
* I. Requirement
****************************************

This script needs several elements in order to run

1. A Kintera Sphere Account with API access
2. A LAMP environment (either local or hosted)
3. Ability to trigger CRON

****************************************
* II. Installation
****************************************

1. Upload the entire directory on your web server

common.php
curl_export.php
KinteraConnect.wsdl
->lib
output.php

2. Create a new MySQL database and create the table structure by import the 
SQL script (table.sql)

****************************************
* III. Configuration
****************************************

1. Open common.php and locate lines 13 to 23

// how many pages we will be looking at
$global_num_pages = 1;

// how many records we are going to look at on each page, maximum 100
$global_page_size = 25;

// toggle logging or not, 1 for yes, 0 for no
$global_log_toggle = 1;

The first two variables controls how many records will be exported each time the script is run.
For example, if $global_num_pages is set to 2 and $global_page_size is set to 100, the script will
pull and export 200 records each time.

The third variable: $global_log_toggle controls the logging of all the activity the script runs,
it is useful to debug or see if at a point the script encounters problems, possible values are 0 and 
1

2. locate lines 30 to 34 on common.php

define('DB_USER', 'database user name');
define('DB_PASS', 'database password');
define('DB_NAME', 'database name');
define('DB_HOST', 'database host address');

Define the database settings here.

3. Open export.php and locate lines 16 to 19
$login_info = array(
	'LoginName' => 'Kintera User Name',
	'Password' => 'Kintera User Password',
);

Enter the Kintera Sphere username and password on those two lines, BE SURE that the user has 
Kintera Connect API access.

4. locate line 35 on export.php
define('EXPORT_KEY', 'some_password');

Please enter a key or password that will be required when you run the script. This is to prevent 
unauthorized access to the export script. Please make sure you don't include any space in this key

5. On line 9 of curl_export.php
$result = http_get('http://www.yoursite.com/kintera/export.php?key=your_key', '');

Change the url to the location that you have uploaded the script to, the key is what you have defined
in step 4 of this section.


****************************************
* IV. Usage
****************************************

1. After all the configuration settings have been correctly set, the you can run the script at your
uploaded location from a browser, i.e.

http://www.yoursite.com/kintera/export.php?key=some_password

The "some_password" is what you have defined from III. Configuration step 4

OR 

2. Run http://www.yoursite.com/kintera/curl_export.php on a CRON in a defined interval

3. Lastly, output.php provides some sample output that can be used to create useful displays such as 
donation widgets.