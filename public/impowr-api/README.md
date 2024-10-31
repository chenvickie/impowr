# RedCap Impowr data transfer

The RedCap Impowr scripts pull together data from external Redcap surveys at different institutions to target Redcap instances

## Redcap

- [Recap] The API tokens for source and destination Redcap surveys

## PHP/Apache

- [PHP] (https://www.php.net/)
- Please make sure the server has php path set up in environment, so we dont have issue to run php file in different paths
- Some of data might be huge, you will need to increase memory_limit on the php.ini config file to avoid Fatal error: Allowed memory size of \*\*\*\* bytes exhausted error
- [MsSQL driver] make sure you have sql driver installed and enable extension=pdo_sqlsrv_81_ts_x64 on your php (this can be different name based your php and os versions)
- [Email] Impowr sends out email notification, make sure email service are enabled on your server

## Authenication

- [Orcid] Impowr uses Orcid as authenication method. Pleasee visit https://orcid.org/ to register an account if you dont have one. Once you login, visit https://orcid.org/developer-tools to create client ID and client sercet to be used on the impowr config later on

## Database

- MsSQL database server
- An initial query schema is located at data/db-init.sql, create tables and insert job information you need before runing the scripts
- For system admin, please insert your orcid ID on impowr_user_permissions table
- Impowr also check user's access after Login via Orcid, therefore all users need to be added into impowr_user_permissions table in order to login into the system

## General Usage

- For first time use: you will need to update database connection info and orcid auth info on config/config-default.php, and then update the file name to config.php

- Once you have Redcap's api token associated with the job(s), you will need to add/update on the impowr_jobs table (If you also have impowr UI set up, you can skip this and add jobs through the UI)

- There are few scripts available for transfering survey data from source site to destination site. Please always make sure you have up to date form/fields tables before running the data transfer

- Sync forms and fields controls in database

```bash
${your_php_path}/php ${your_redcap_impowr_api_path}/etl/update.sh
```

- Transfer survey records based on forms and fields controls

```bash
${your_php_path}/php ${your_redcap_impowr_api_path}/etl/import.sh
```

-- Sync forms and fields controls in database, and then Transfer survey records based on forms and fields controls

```bash
${your_php_path}/php ${your_redcap_impowr_api_path}/etl/pull.sh
```

## Cron job

To run the update fileds and import process. You could also run them separately by using update.sh or import.sh we mentioned above

There are options we can pass on cron job

- [-d]: (environment, either dev or prod)
- [-s]: (data_site, need to be one of the job name in impowr_jobs table, or all for all activated jobs)
- [-sfm]: (forms need to be skipped in the import process, it should a comma-separated list, such as 'demographics, demographics1', etc )
- [-bfd]: (fields need to be blank in the import process, it should a comma-separated list, such as 'email, dob', etc)

```bash
${your_redcap_impowr_api_path}/etl/pull.sh -e ${env} -s ${data_site} -sfm ${skip_forms} -bfd ${blank_fields}
```

## Logs

Log info can be found under etc\logs folder

# Disclaimer

**Important Notice: Use at Your Own Risk**

The code and materials in this repository are provided as-is, without warranty of any kind. The author is not responsible for any damages, losses, or issues that may arise from the use of this repository or its contents.

By using this repository, you acknowledge that you are responsible for any consequences resulting from its usage. Ensure you review and understand the code before deploying it in any environment.
