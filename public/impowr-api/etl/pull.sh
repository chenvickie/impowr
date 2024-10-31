#!/bin/bash

# This is a cron job to update forms and fields based on the skipForms and blank fields
# Then, transfer survey data from the source site to destination site based on the required forms/fields from control tables. 

# variables defined
env="dev"
site="all" # either site name or all 
forms="" # override the forms from database if need, for example, "demographics"
fields="" # overrid the fields from the database if need, for example, imp_bstrong_inter,impowr_ime2_inter,imp_bstrong_moud", etc
skipForms="" # override the skip forms from database if need, for example, "demographics"
blankFields="" # overrid the blank fields from the database if need, for example, imp_bstrong_inter,impowr_ime2_inter,imp_bstrong_moud", etc

# we can pass those variables from crontab to run different sites in different scheduled time
while getopts e:s:d: opts; do
   case ${opts} in
      e) env=${OPTARG} ;;
      s) site=${OPTARG} ;;
      fm) forms=${OPTARG} ;;
      fd) fields=${OPTARG} ;;
      sfm) skipForms=${OPTARG} ;;
      bfd) blankFields=${OPTARG} ;;
   esac
done

# update forms and fields on impowr_form_controls and impowr_field_controls tables
# IMPORTANT: we will need to make sure php path to be picked up in server or different devices
php updateRequiredFields.php env=$env site=$site skipForms=$skipForms blankFields=$blankFields

# wait for 1 minutes to run the import process
# Note: we might want to increase it in prod just in case the update updateRequiredFields process take longer 
sleep 1m

# import survey records from source site to destination site
# IMPORTANT: we will need to make sure php path to be picked up in server or different devices
php import.php env=$env site=$site mailto=$emailto forms=$forms fields=$fields
