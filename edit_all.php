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

include('config.php');
include('session.php');
$userDetails=$userClass->userDetails($session_uid);

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

$filelist = array();
if ($handle = opendir("fonts")) {
	while ($entry = readdir($handle)) {
		if($entry != "." && $entry != "..")
		{
			$filelist[] = $entry;
		}
	}
	closedir($handle);
}

/**
 * Permanently delete a file, skipping the trash.
 *
 * @param Google_Service_Drive $service Drive API service instance.
 * @param String $fileId ID of the file to delete.
 */
function deleteFile($service, $fileId) {
  try {
    $service->files->delete($fileId);
  } catch (Exception $e) {
    print "An error occurred: " . $e->getMessage();
  }
}

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
        <link href="css/canvas-gh-pages.css" type="text/css" rel="stylesheet" media="screen,projection"/>

        <script src="js/vendor/modernizr-2.8.3.min.js"></script>
    </head>
    <body>
            <?php
            if(isset($_GET['id'])){
                $folder_id = $_GET['id'];
                $folder_name = $_GET['name'];
                echo "Folder ID: ". $folder_id;
                ?>
                <div class="wrap-a-gd-x">
                  <div class="a-gd-x edit-all">
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
                  if($mimeType=="image/jpeg"){
                    // is an image
                    $originalFilename = $meta_file->originalFilename;
                    $thumbnailLink = $meta_file->thumbnailLink;
                    $webContentLink = $meta_file->webContentLink;
                    $fileExtension = $meta_file->fileExtension;

                    $imgUrl[$i] = $webContentLink;
                    $imgFilename[$i] = $originalFilename;
                    $imgExt[$i] = $fileExtension;
                    $imgId[$i] = $file_id;

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
          </div>
            <div style="clear:both"></div>
            <?php
            }
            ?>
            <form action="" enctype="multipart/form-data" method="POST">
            <div id='main-container'>
                <div id="left-container">
                  <textarea name="oo" id="text" cols="60" rows="25" style="height:540px" class="hidden"></textarea>
                  <textarea name="watermark_text" id="text2" cols="60" rows="5">my text</textarea>
                  <button id="btn">Draw text</button>
                </div>
                <div id="container" data-banner-editable="true"></div>
              </div>
            <div style="clear: both;"></div>

            <?php
            $filelist = array();
            if ($handle = opendir("fonts")) {
            	while ($entry = readdir($handle)) {
            		if($entry != "." && $entry != "..")
            		{
            			$filelist[] = $entry;
            		}
            	}
            	closedir($handle);
            }
            ?>
            <table border="1">
              <tr class="hidden">
                <td>Preview Size : </td>
                <td><input type="text" name="canvas_size" value="400"></td>
              </tr>
            <tr class="hidden">
            	<td>Set TOP :</td>
            	<td><input type="text" name="top_pos" value="90"></td>
            </tr>
            <tr class="hidden">
            	<td>Set LEFT :</td>
            	<td><input type="text" name="left_pos" value="43"></td>
            </tr>
            <tr>
            	<td>FONT SIZE :</td>
            	<td><input type="text" name="font_size" value="8.89"></td>
            </tr>
            <tr>
            	<td>Set TEXT COLOR :</td>
            	<td>
            	RED : <input type="text" name="r_color" value="255">
            	GREEN : <input type="text" name="g_color" value="255">
            	BLUE : <input type="text" name="b_color" value="255">
            	</td>
            </tr>
            <tr>
            	<td>Select Font : </td>
            	<td>
            		<select name="font_selection" class="browser-default">
            			<?php
            			foreach($filelist as $f)
            			{
            				echo '<option value="'.$f.'" '. ($f=="Allan_Rg.ttf"?'selected':'').'>'.$f.'</option>';
            			}
            			?>
            		</select>
            	</td>
            </tr>
            <tr>
            	<td colspan="2"><input type="submit" value="Submit" class="btn btn-primary" name="submit"></td>
            </tr>
            </table>
            </form>

            <?php
            $target_dir = 'uploads/';
            $watermarked_dir = 'watermarked/';
            $font_dir = 'fonts/';

            if(isset($_POST["submit"]))
            {
              $filename = 'config_'.$userDetails->username.'.json';
              if(file_exists($filename)){
                $handle = fopen($filename, "r");
                $contents = fread($handle, filesize($filename));
                fclose($handle);
                $json_decoded = json_decode($contents);
                $watermarked_id = $json_decoded[0]->watermarked_id;
              }
              $url = "https://www.googleapis.com/drive/v2/files/".$watermarked_id."/children";
              $resp = $httpClient->request($method, $url);
              $body = $resp->getBody()->getContents();
              //print_r($body); echo "<br><br>";
              $body_decoded = json_decode($body);
              $watermarked_list = $body_decoded->items;
              //print_r($watermarked_list);
              $i=0;
              foreach($watermarked_list as $watermarked_file) {
                $file_id = $watermarked_file->id;
                $url = "https://www.googleapis.com/drive/v2/files/".$file_id;
                $resp = $httpClient->request($method, $url);
                $body = $resp->getBody()->getContents();
                //print_r($body); echo "<br><br>";
                $meta_file = json_decode($body);
                $title = $meta_file->title;
                if($title == $folder_name){
                  //echo "already exists<br>";
                  deleteFile($service, $watermarked_file->id);
                }
              }

              $fileMetadata = new Google_Service_Drive_DriveFile(array(
                'name' => $folder_name,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => array($watermarked_id)));
              $file = $service->files->create($fileMetadata, array(
                'fields' => 'id'));
              echo "New Folder created: <a href='https://drive.google.com/drive/folders/".$file->id."' target='_blank'>View Folder</a><br>";
              $watermark_id = $file->id;

              $i=0;
              foreach($folder_list as $file) {

                $img = $target_dir.$imgFilename[$i];
                // Get file object
                $url = "https://www.googleapis.com/drive/v3/files/".$imgId[$i]."?alt=media";
                $resp = $httpClient->request($method, $url);
                $body = $resp->getBody()->getContents();
                //echo "$body\n";
                ob_start();
                $filename = $body;
                ob_end_clean();
                $fh = fopen($img, "w" );
                fwrite( $fh, $filename );
                fclose( $fh );

              	$target_file = $target_dir . $imgFilename[$i];
              	$imageFileType = $imgExt[$i];

              	if(strtolower($imageFileType) != "jpg" && strtolower($imageFileType) != "png" && strtolower($imageFileType) != "jpeg" && strtolower($imageFileType) != "gif" ) {
                      echo "File is not an image.";
                      $uploadOk = 0;
                  }
              	else
              	{
              			if(strtolower($imageFileType) == "jpg" || strtolower($imageFileType) == "jpeg")
              			{
              				$imagetobewatermark=imagecreatefromjpeg($target_file);
                      list($width, $height) = getimagesize($target_file);
              				$watermarktext=$_POST['watermark_text'];
              				$font=$font_dir.$_POST['font_selection'];
                      $fontsize = $width * $_POST['font_size']/$_POST['canvas_size']/1.3333333333333; // 1 Pt = 1.3333333333333 Px
                      $left_pos = $width/100*$_POST['left_pos'];
                      $top_pos = $height/100*$_POST['top_pos']+$fontsize;
              				$color = imagecolorallocate($imagetobewatermark, $_POST['r_color'], $_POST['g_color'], $_POST['b_color']); //set color via RGB
              				imagettftext($imagetobewatermark, $fontsize, 0, $left_pos, $top_pos, $color, $font, $watermarktext);
              				//header("Content-type:image/jpeg");
              				$watermark_file = $watermarked_dir.date("dmy").rand()."_watermarked.jpg";
              				imagejpeg($imagetobewatermark,$watermark_file);
              				imagedestroy($imagetobewatermark);

                      //echo 'Here\'s Your watermarked image : <a target="_blank" href='.$watermark_file.'>'.$watermark_file.'</a>';

                      $fileMetadata = new Google_Service_Drive_DriveFile(array(
                        'name' => $imgFilename[$i],
                        'parents' => array($watermark_id)
                      ));
                      $content = file_get_contents($watermark_file);
                      $file = $service->files->create($fileMetadata, array(
                        'data' => $content,
                        'mimeType' => 'image/jpeg',
                        'uploadType' => 'multipart',
                        'fields' => 'id'));
                      echo "File created: <a href='https://drive.google.com/uc?id=".$file->id."' target='_blank'>View File</a><br>";
                      unlink($img);
                      unlink($watermark_file);
              			}
                	}
                  $i++;
                }
            }
            ?>

  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
  <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.11.2.min.js"><\/script>')</script>
  <script src="js/vendor/materialize.min.js"></script>
  <script src="js/vendor/CanvasBanner.js"></script>
  <script src="js/main.js"></script>
  <script>
  var fontProperties = {
    fontSize: 8.89,
    fillStyle: 'rgba(255,255,255,0.5)',
    fontName: 'Allan',
    stroke: {
      size: 0,
      color: 'rgba(255,255,255,0.5)'
    }
  };
  var textPosition = {
    left: 43, top: 90
  }
  var text = 'my text';
  var options = {
    container: container,
    "editable": true,
    width: '400',
    height: '700',
    imgUrl: '<?=$imgUrl[0]?>',
    text: text,
    fontProperties: fontProperties,
    textPosition: textPosition
  };
  </script>
  <script src="js/vendor/canvas-gh-pages.js"></script>
  </body>
</html>
