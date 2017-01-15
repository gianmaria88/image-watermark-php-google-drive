 <?php
include('config.php');
$session_uid='';
$_SESSION['uid']=''; 
if(empty($session_uid) && empty($_SESSION['uid']))
{
$url=BASE_URL.'index.php';
header("Location: $url");
//echo "<script>window.location='$url'</script>";
}
?>