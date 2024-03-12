<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class ShareController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	public function _index()
	{
		$pageuser = checkLogin(); //isLogin
		$tg_img = $this->genCover2($pageuser, true);
		$where = " log.type=10 and uid='" . $pageuser['id'] . "'";

		$RS = Db::table('wallet_log log')
			->where($where)
			->sum('money');

		$where = " log.pid='" . $pageuser['id'] . "'";
		$people = Db::table('sys_user log')
			->where($where)
			->count();

		$return_data = [
			'icode' => $pageuser['icode'],
			//'url' => $this->getQrcodeUrl($pageuser['icode']),
			'qrcode' => $tg_img . '?rt=' . mt_rand(11111, 99999),
			'RS' => $RS,
			'people' => $people
		];
		jReturn(1, 'ok', $return_data);
	}

	public function _download()
	{
		//$pageuser=checkLogin();
		$filename = ROOT_PATH . urldecode($this->params['src']);
		$filename_arr = explode('?', $filename);
		$filename = $filename_arr[0];
		$mime = 'application/force-download';
		header('Pragma: public'); // required
		header('Expires: 0'); // no cache
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: private', false);
		header('Content-Type: ' . $mime);
		header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
		header('Content-Type: application/octet-stream; name=' . $filename);
		header('Content-Transfer-Encoding: binary');
		header('Connection: close');
		readfile($filename);
		exit();
	}

	private function genCover2($user, $reflush = false)
	{
		$icode = $user['icode'];
		$tg_img = 'uploads/qrcode/tg_' . $icode . '.jpg';
		if (file_exists(ROOT_PATH . $tg_img) && !$reflush) {
			return $tg_img;
		}

		$qrcode = ROOT_PATH . $this->genQrcode($icode);

		//$tpl=ROOT_PATH.'public/images/tpl.jpg';
		$tpl = $qrcode;
		$image = new \Imagick($tpl);
		$width = $image->getImageWidth();
		$height = $image->getImageHeight();
		//首先进行一个图片绘画
		$newImg = new Imagick(ROOT_PATH . $user['headimgurl']);
		$percent = 0.28;
		$qwidth = $width * $percent;
		$qheight = $width * $percent;
		$newImg->thumbnailImage($qwidth, $qheight);

		$radius = $qwidth * 0.15;
		$mask = new Imagick();
		$mask->newImage($qwidth, $qheight, new ImagickPixel('transparent'), 'png');
		$shape = new ImagickDraw();
		$shape->setFillColor(new ImagickPixel('magenta'));
		$shape->roundRectangle(0, 0, $qwidth, $qheight, $radius, $radius);
		$mask->drawImage($shape);
		$newImg->compositeImage($mask, Imagick::COMPOSITE_DSTIN, 0, 0);

		//$newImg->newImage($width * $xNum + ($xNum - 1) * $xDistance, $height * $yNum + ($yNum - 1) * $yDistance, '#AAAAAA', 'jpg');
		$image->compositeImage($newImg, Imagick::COMPOSITE_OVER, ($width - $qwidth) / 2, ($height - $qheight) / 2);

		$draw = new ImagickDraw();
		//$draw->setTextKerning(10); // 设置文字间距
		$draw->setFont(ROOT_PATH . 'public/fonts/simhei.ttf');
		$draw->setFontWeight(800); // 字体粗体
		$draw->setFillColor('#333333'); // 字体颜色
		//$draw->setFontFamily( "Palatino" );
		$draw->setFontSize(32);
		$draw->setGravity(\Imagick::GRAVITY_NORTH);
		//$image->annotateImage($draw,-76,565+$qheight, 0, '推荐码:') ;
		//$phone=substr($user['phone'],0,3).'***'.substr($user['phone'],8);
		//$image->annotateImage($draw, 4,435, 0, $phone) ;
		$draw->setFillColor('#f860d7'); // 字体颜色
		$draw->setFontSize(50);
		//$image->annotateImage($draw,60,556+$qheight, 0, $icode) ;
		//header("Content-Type: image/{$image->getImageFormat()}");
		//echo $image->getImageBlob();
		file_put_contents(ROOT_PATH . $tg_img, $image->getImageBlob());
		return $tg_img;
	}

	private function genCover($user, $reflush = false)
	{
		$icode = $user['icode'];
		$tg_img = 'uploads/qrcode/tg_' . $icode . '.jpg';
		if (file_exists(ROOT_PATH . $tg_img) && !$reflush) {
			return $tg_img;
		}

		$qrcode = ROOT_PATH . $this->genQrcode($icode);

		$tpl = ROOT_PATH . 'public/images/tg.png';
		$image = new \Imagick($tpl);
		$width = $image->getImageWidth();
		$height = $image->getImageHeight();
		//首先进行一个图片绘画
		$newImg = new Imagick($qrcode);
		$qwidth = 420;
		$qheight = 420;
		$newImg->thumbnailImage($qwidth, $qheight);
		//$newImg->newImage($width * $xNum + ($xNum - 1) * $xDistance, $height * $yNum + ($yNum - 1) * $yDistance, '#AAAAAA', 'jpg');
		$image->compositeImage($newImg, Imagick::COMPOSITE_OVER, ($width - $qwidth) / 2, 560);

		$draw = new ImagickDraw();
		//$draw->setTextKerning(10); // 设置文字间距
		$draw->setFont(ROOT_PATH . 'public/fonts/simhei.ttf');
		$draw->setFontWeight(800); // 字体粗体
		$draw->setFillColor('#333333'); // 字体颜色
		//$draw->setFontFamily( "Palatino" );
		$draw->setFontSize(32);
		$draw->setGravity(\Imagick::GRAVITY_NORTH);
		$image->annotateImage($draw, -76, 565 + $qheight, 0, '推荐码:');
		//$phone=substr($user['phone'],0,3).'***'.substr($user['phone'],8);
		//$image->annotateImage($draw, 4,435, 0, $phone) ;
		$draw->setFillColor('#f860d7'); // 字体颜色
		$draw->setFontSize(50);
		$image->annotateImage($draw, 60, 556 + $qheight, 0, $icode);
		//header("Content-Type: image/{$image->getImageFormat()}");
		//echo $image->getImageBlob();
		file_put_contents(ROOT_PATH . $tg_img, $image->getImageBlob());
		return $tg_img;
	}

	private function genQrcode($icode)
	{
		$icode_name = getRsn();
		$qrcode = 'uploads/qrcode/' . date('Ym') . '/' . $icode_name . '.png';
		if (file_exists($qrcode)) {
			return $qrcode;
		}
		$qrcode_str = $this->getQrcodeUrl($icode);
		if (!is_dir(dirname(ROOT_PATH . $qrcode))) {
			mkdir(dirname(ROOT_PATH . $qrcode), 0755, true);
		}
		QRcode::png($qrcode_str, ROOT_PATH . $qrcode, 'L', 14, 1);
		return $qrcode;
	}

	private function getQrcodeUrl($icode)
	{
		// $qr_domain = $_SERVER['HTTP_HOST'];
		//$qrcode_str="{$_SERVER['REQUEST_SCHEME']}://{$qr_domain}/api/?a=wxLogin&icode={$icode}";
		// {$_SERVER['REQUEST_SCHEME']}://{$qr_domain}
		$qrcode_str = "/h5/#/register?icode={$icode}";
		return $qrcode_str;
	}
}
