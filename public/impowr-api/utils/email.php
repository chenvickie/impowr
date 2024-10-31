<?php
include_once '../utils/utils.php';

function getEmailHeaders ()
{
  // To send HTML mail, the Content-type header must be set
  $headers = 'MIME-Version: 1.0' . "\r\n";
  $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
  return $headers;
}


function sendTransferErrorNotification ($siteInfo, $payload)
{
  global $isProd;
  $headers = getEmailHeaders ();
  $title   = "Impowr Data Transfer Error: " . $siteInfo['project_name'];
  if ( ! $isProd ) $title .= " [DEV TESTING] ";

  $msg = "<div>Hello " . $siteInfo['project_contact_name'] . ",</div>";
  $msg .= "<div> The following error occurred while transfering redCap data in IMPOWR</div>";
  $msg .= "<div> Source Site : " . $siteInfo['source_project_name'] . " ( " . $siteInfo['source_project_id'] . " )</div>";
  $msg .= "<div> Destination Site: " . $siteInfo['project_name'] . " ( " . $siteInfo['project_id'] . " ) </div>";
  $msg .= "<div class='error'>" . $payload['status'] . "</div>";
  $msg .= "<div>Please investigate this matter!</div>";

  $body = "
    <html>
    <head>
      <title>" . $title . "</title>
      <style>
        body {
          size:14px;
        }
        .content, .footer{
            margin:20px 0px;
            line-height: 2em;
        }
        .error{
            color: red;
        }
        .note{
            font-size: 12px;
            font-style: italic;
        }
      </style>
    </head>
    <body>
      <div class='content'>" . $msg . "</div>
      <div class='footer'>Best Regards,<br> Impowr</div>
      <div class='note'>This is an automatically generated email. Please do not reply to it.</div>
    </body>
    </html>
  ";
  //$mailto = $isProd ? $siteInfo['project_contact_email'] . ", wachen@wakehealth.edu" : "wachen@wakehealth.edu";
  $mailto = $siteInfo['project_contact_email'] . ", wachen@wakehealth.edu";
  if ( ! mail ($mailto, $title, $body, $headers) ) {
    logs ("Mailer Error");
  }
}

