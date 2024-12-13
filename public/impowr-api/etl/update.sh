#!/bin/bash

# This is a script to update forms and fields controls based on the skipForms and blank fields

# variables defined
env="dev"
site="TEST" # a site name you would like to update
skipForms="" # override the skip forms from database if need, for example, "demographics"
blankFields="" # override the blank fields from the database if need, for example, imp_bstrong_inter,impowr_ime2_inter,imp_bstrong_moud", etc

# we can pass those variables from crontab to run different sites in different scheduled time
while getopts e:s:sfm:bfd opts; do
   case ${opts} in
      e) env=${OPTARG} ;;
      s) site=${OPTARG} ;;
      sfm) skipForms=${OPTARG} ;;
      bfd) blankFields=${OPTARG} ;;
   esac
done

if [[ $path == "prod" ]]; then
   cd /var/www/html/impowr/impowr-api/etl
fi

# update forms and fields on impowr_form_controls and impowr_field_controls tables
# IMPORTANT: we will need to make sure php path to be picked up in server or different devices
php updateRequiredFields.php env=$env site=$site skipForms=$skipForms blankFields=$blankFields 