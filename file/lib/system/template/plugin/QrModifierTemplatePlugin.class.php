<?php
namespace wcf\system\template\plugin;
use wcf\util\StringUtil;

/**
 * Creates an <img>-Tag with the given contents encoded in a QR-Code.
 * 
 * Usage:
 * 	{@"Never gonna give you up"|qr:'L':150:4:'FFF':'000'}
 * 
 * First parameter is the value, second the error correction level, third the
 * minimum size and fourth the margin size. The fifth parameter controls the
 * background color and the sixth controls the foreground color.
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
		
		// margin
		if (isset($tagArgs[3])) {
			$renderer->setMargin(intval($tagArgs[3]));
		}

		// background
		if (isset($tagArgs[4])) {
			$bg = $this->hex2rgb(StringUtil::trim($tagArgs[4]));
			
			if ($bg) {
				$renderer->setBackgroundColor(new \BaconQrCode\Renderer\Color\Rgb($bg[0], $bg[1], $bg[2]));
			}
		}
		
		// foreground
		if (isset($tagArgs[5])) {
			$fg = $this->hex2rgb(StringUtil::trim($tagArgs[5]));
			
			if ($fg) {
				$renderer->setForegroundColor(new \BaconQrCode\Renderer\Color\Rgb($fg[0], $fg[1], $fg[2]));
			}
		}
		
		$qrCode = \BaconQrCode\Encoder\Encoder::encode($content, $errorCorrection, 'UTF-8');
		
		return '<img src="data:image/svg+xml;base64,'.base64_encode($renderer->render($qrCode)).'" alt="'.StringUtil::encodeHTML($content).'" />';
	}
	
    /**
     * Convert hex to rgb
     *
     * @var string
     */
	private function hex2rgb($hex) {
		preg_match_all('/^#?([a-f0-9]{6}|[a-f0-9]{3})$/i', $hex, $matches);
		
		if (!isset($matches[1][0]) || empty($matches[1][0])) {
			return false;
		}

		$width = mb_strlen($matches[1][0]);
		
		$r = ($width == 3 ? hexdec(mb_substr($matches[1][0], 0, 1).mb_substr($matches[1][0], 0, 1)) : hexdec(mb_substr($matches[1][0], 0, 2)));
		$g = ($width == 3 ? hexdec(mb_substr($matches[1][0], 1, 1).mb_substr($matches[1][0], 1, 1)) : hexdec(mb_substr($matches[1][0], 2, 2)));
		$b = ($width == 3 ? hexdec(mb_substr($matches[1][0], 2, 1).mb_substr($matches[1][0], 2, 1)) : hexdec(mb_substr($matches[1][0], 4, 2)));

		return array($r, $g, $b);
	}
}
