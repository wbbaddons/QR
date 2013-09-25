<?php
namespace wcf\system\template\plugin;
use wcf\util\StringUtil;

/**
 * Creates an <img>-Tag with the given contents encoded in a QR-Code.
 * 
 * Usage:
 * 	{"Never gonna give you up"|qr:'L':150}
 * 
 * First parameter is the value, second the error correction level and third the
 * minimum size.
 * 
 * @author	Tim Düsterhus
 * @copyright	2013 Tim Düsterhus
 * @license	BSD 2-Clause License <http://opensource.org/licenses/BSD-2-Clause>
 * @package	be.bastelstu.wcf.qr
 * @subpackage	system.template.plugin
 */
class QrModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * @see	wcf\system\template\IModifierTemplatePlugin::execute()
	 */
	public function execute($tagArgs, \wcf\system\template\TemplateEngine $tplObj) {
		require_once(WCF_DIR.'lib/system/api/qr/autoload_register.php');
		
		$errorCorrection = new \BaconQrCode\Common\ErrorCorrectionLevel(\BaconQrCode\Common\ErrorCorrectionLevel::L);
		$size = 150;
		$content = $tagArgs[0];
		
		// error correction level
		if (isset($tagArgs[1])) {
			$values = $errorCorrection->getConstList();
			if (!in_array(mb_strtoupper($tagArgs[1]), $values)) throw new \wcf\system\exception\SystemException("Unknown error correction level '".mb_strtoupper($tagArgs[1])."'");
			$errorCorrection->change(constant('\BaconQrCode\Common\ErrorCorrectionLevel::'.mb_strtoupper($tagArgs[1])));
		}
		// minimum size
		if (isset($tagArgs[2])) {
			$size = intval($tagArgs[2]);
		}
		
		$renderer = new \BaconQrCode\Renderer\Image\Svg();
		$renderer->setWidth($size);
		$renderer->setHeight($size);
		$qrCode = \BaconQrCode\Encoder\Encoder::encode($content, $errorCorrection, 'UTF-8');
		
		return '<img src="data:image/svg+xml;base64,'.base64_encode($renderer->render($qrCode)).'" alt="'.StringUtil::encodeHTML($content).'" />';
	}
}
