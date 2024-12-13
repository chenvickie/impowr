#!/bin/bash

'''
   This is a script that will transfer survey data from the source site to destination site
   based on the required forms/fields from control tables. 
'''

# variables defined
env="dev"
site="TEST" # a site name you would like to import
forms="" # override the forms from database if need, for example, "demographics"
fields="" # override the fields from the database if need, for example, imp_bstrong_inter,impowr_ime2_inter,imp_bstrong_moud", etc

# we can pass those variables from crontab to run different sites in different scheduled time
while getopts e:s:fm:fd opts; do
   case ${opts} in
      e) env=${OPTARG} ;;
      s) site=${OPTARG} ;;
      fm) forms=${OPTARG} ;;
      fd) fields=${OPTARG} ;;
   esac
done

if [[ $path == "prod" ]]; then
   cd /var/www/html/impowr/impowr-api/etl
fi

# import survey records from source site to destination site
# IMPORTANT: we will need to make sure php path to be picked up in server or different devices
php import.php env=$env site=$site forms=$forms fields=$fields 
