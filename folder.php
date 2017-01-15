<?php
/*
 * Copyright 2013 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

include_once __DIR__ . '/vendor/autoload.php';
include_once "examples/templates/base.php";

$client = new Google_Client();

if ($credentials_file = checkServiceAccountCredentialsFile()) {
  // set the location manually
  $client->setAuthConfig($credentials_file);
} elseif (getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
  // use the application default credentials
  $client->useApplicationDefaultCredentials();
} else {
  echo missingServiceAccountDetailsWarning();
  return;
}

$client->setApplicationName("Watermark Project");
$client->setScopes([
'https://www.googleapis.com/auth/drive.file',
'https://www.googleapis.com/auth/drive.readonly',
'https://www.googleapis.com/auth/drive.appfolder']);
$service = new Google_Service_Drive($client);

$tokenArray = $client->fetchAccessTokenWithAssertion();
$accessToken = $tokenArray["access_token"];
$method = 'GET';
$headers = ["Authorization" => "Bearer $accessToken"];
$httpClient = new GuzzleHttp\Client(['headers' => $headers]);
?>
<!doctype html>
<html class="no-js" lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Watermark Project</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" href="css/normalize.min.css">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="css/materialize.min.css" type="text/css" rel="stylesheet" media="screen,projection"/>
        <link href="css/main.css" type="text/css" rel="stylesheet" media="screen,projection"/>

        <script src="js/vendor/modernizr-2.8.3.min.js"></script>
    </head>
    <body>
            <?php
            if(isset($_GET['id'])){
                $folder_id = $_GET['id'];
                echo "Folder ID: ". $folder_id;
                ?>
                <div class="a-gd-x">
                <?php
                /* Start Retrieve all files in folder */
                $folderId = $folder_id; // Upload folder
                $url = "https://www.googleapis.com/drive/v2/files/".$folderId."/children";
                $resp = $httpClient->request($method, $url);
                $body = $resp->getBody()->getContents();
                //print_r($body); echo "<br><br>";
                $body_decoded = json_decode($body);
                $folder_list = $body_decoded->items;
                //print_r($folder_list);
                $i=0;
                foreach($folder_list as $file) {
                  $file_id = $file->id;
                  //print_r($file_id); echo "<br><br>";
                  // Get filename from id
                  $url = "https://www.googleapis.com/drive/v2/files/".$file_id;
                  $resp = $httpClient->request($method, $url);
                  $body = $resp->getBody()->getContents();
                  //print_r($body); echo "<br><br>";
                  $meta_file = json_decode($body);
                  $mimeType = $meta_file->mimeType;
                  if($mimeType=="application/vnd.google-apps.folder"){
                    // is a folder
                    $title = $meta_file->title;
                    $f_id = $meta_file->id;
                    ?>
                   <div class="a-u-xb-j a-Wa-ka" style="margin-top:16px;margin-right:16px">
                       <div class="a-u-xb-ag-fa-ve"></div>
                           <div class="l-u-xb l-u-o">
                             <div class="l-A-ia-ef"></div>
                             <div class="l-u-o-c-j a-c-j"></div>
                             <div class="l-u-o-c-yf">
                                 <div class="l-o-c-qd">
                                   <i class="material-icons">&#xE2C9;</i>
                                 </div>
                            </div>
                           <a href="/folder.php?id=<?=$f_id?>"><div class="l-u-o-V-j">
                             <div class="l-u-V">
                               <span class="l-Ab-T-r"><?=$title?></span>
                             </div>
                          </a>
                           </div>
                           <div class="l-u-o-c-yf l-u-o-c-yf-right" <?php echo ($title=='Watermarked' ? 'style="display:none"':'style="display:inline-block"');?>>
                               <div class="l-o-c-qd l-o-c-qd-right">
                                 <ul id="dropdown<?=$i?>" class="dropdown-content">
                                    <li><a href="/edit_all.php?id=<?=$f_id?>&name=<?=$title?>">Edit All</a></li>
                                  </ul>
                                  <a class="dropdown-button" href="#!" data-activates="dropdown<?=$i?>"><i class="material-icons">&#xE5D4;</i></a>
                               </div>
                          </div>
                           <div class="l-u-o-ja-Dc"></div>
                      </div>
                      </div>
                    <?php
                  }
                  else if($mimeType=="image/jpeg"){
                    // is an image
                    $originalFilename = $meta_file->originalFilename;
                    $thumbnailLink = $meta_file->thumbnailLink;
                    //echo $originalFilename; echo "<br><br>";
                    ?>
                    <div class="a-u-xb-j a-Wa-ka" style="margin-top:16px;margin-right:16px">
                      <div class="a-u-xb-ag-fa-ve"></div>
                      <div class="l-u-xb l-u-Ab l-u-Xc-Wa-ka l-oi-cc">
                        <div class="l-A-ia-ef"></div>
                        <div class="l-u-jjgHhb-ed-yc-j"></div>
                        <div class="l-u-Ab-zb-j">
                          <div class="l-u-Ab-zb-x">
                            <div class="l-u-Ab-zb ta-gc-np-Nd">
                            <img class="l-u-Ab-zb-Ua" src="<?=$thumbnailLink?>">
                          </div>
                        </div>
                        </div>
                        <div class="l-u-Ab-T-j">
                            <div class="l-u-Ab-T">
                              <div class="a-Oa-qd-Nd">
                                <i class="material-icons">&#xE3F4;</i>
                              </div>
                              <div class="l-u-Ab-c">
                                <div class="a-c">
                                  <i class="material-icons">&#xE3F4;</i>
                              </div>
                            </div>
                              <div class="l-u-V"><span class="l-Ab-T-r"><?=$originalFilename?></span>
                              </div>
                            </div>
                            </div>
                          </div>
                        </div>
                        <?php
                  }
                  ?>
                  <?php
                  $i++;
                }
            ?>
          </div>
            <div style="clear:both"></div>
            <?php
            }
            ?>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
  <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.11.2.min.js"><\/script>')</script>
  <script src="js/vendor/materialize.min.js"></script>
  <script src="js/main.js"></script>
  </body>
</html>
