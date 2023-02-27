<?php
define('HOST','127.0.0.1');
define('USER','root');
define('PASS','shames11');
define('PORT','3306');
define('DATABASE','sdk2');

require_once('project/redis.php');

if (!isset($_GET['crud']) {
    //404
}

require_once('vendor/PDO/PDO.class.php');

$DB_SDK2 = new DB(HOST, PORT, DB_SDK2, USER, PASS);

if(!validateCRUD($params['crud'],$params['interface'])) {

	output404();
}

function createthumbimage($org_fname,$new_fname,$new_width,$new_height,$ImageTy)
{
    // $org_fname==>TMP name on the server
    if(strlen($org_fname)>0)
    {
		list($OldWidth, $OldHeight) = getimagesize($org_fname);
		
		if ( $OldWidth > $OldHeight ) {
		  $thumd_width  = $new_width;
		  $thumd_height = $OldHeight*($new_height/$OldWidth);
		}
		if ( $OldWidth < $OldHeight ) {
		  $thumd_width  = $OldWidth*($new_width/$OldHeight);
		  $thumd_height = $new_height;
		}
		if ( $OldWidth == $OldHeight ) {
		  $thumd_width  = $new_width;
		  $thumd_height = $new_height;
		}
		
		$thumd_height 	= round($thumd_height, 0);
		$thumd_width 	= round($thumd_width, 0);
		$dest_img		= imagecreatetruecolor($thumd_width,$thumd_height);

		if ( $ImageTy=="image/jpeg" || $ImageTy=="image/jpg" || $ImageTy=="image/jpe_" || $ImageTy=="image/pjpeg" || $ImageTy=="image/vnd.swiftview-jpeg" ) {
		  $src_image=imagecreatefromjpeg($org_fname);
		}
		if ( $ImageTy=="image/png" || $ImageTy=="image/png" || $ImageTy=="image/x-png" ) {
		  $src_image=imagecreatefrompng($org_fname);
		}
		if ( $ImageTy=="image/gif" || $ImageTy=="image/x-xbitmap" || $ImageTy=="image/gi_" ) {
		  $src_image=imagecreatefromgif($org_fname);
		}
		
		if( imagecopyresampled($dest_img,$src_image,0,0,0,0,$thumd_width,$thumd_height,$OldWidth,$OldHeight)) {
			if( $ImageTy=="image/jpeg" || $ImageTy=="image/jpg" || $ImageTy=="image/jpe_" || $ImageTy=="image/pjpeg" || $ImageTy=="image/vnd.swiftview-jpeg" ) {
			  imagejpeg($dest_img,$new_fname);
			}
			if($ImageTy=="image/png" || $ImageTy=="image/png" || $ImageTy=="image/x-png") {
			  imagepng($dest_img,$new_fname);
			}
			if($ImageTy=="image/gif" || $ImageTy=="image/x-xbitmap" || $ImageTy=="image/gi_") {
			  imagegif($dest_img,$new_fname);
			}
		}
		//unlink($org_fname); to delete original file.
		return $new_fname;
    }
}
