<?php
/**
 * ImageProcessor — standalone image processing service
 *
 * Extracted from SiteBill::ImageTrait (Этап 2 рефакторинга).
 * Handles pure image processing operations without database dependency.
 *
 * Dependencies:
 *  - ConfigProvider (object with getConfigValue method)
 *  - GD extension for image manipulation
 *
 * @author Refactoring — auto-extracted from ImageTrait
 */

class ImageProcessor
{
    /** @var object Config provider with getConfigValue($key) method */
    private $config;

    /**
     * @param object $config Object with getConfigValue($key) method
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Make preview / resize image
     *
     * @param string     $src       Path to source image
     * @param string     $dst       Path to destination image
     * @param int        $width     Target width
     * @param int        $height    Target height
     * @param string     $ext       Source extension hint (jpg, png, gif, webp)
     * @param int|string $md        Resize mode (0, 1, 'width', 'height', 'smart', 'c', 'f')
     * @param string     $final_ext Extension for output (if different from $ext)
     * @return array|false          [width, height] on success, false on failure
     */
    public function makePreview($src, $dst, $width, $height, $ext = 'jpg', $md = 0, $final_ext = '')
    {
        // Get real MIME type
        $realmimetype = mime_content_type($src);

        $unpackfrom = $ext;
        $packto = $ext;

        // Determine unpack format by MIME
        switch ($realmimetype) {
            case 'image/jpeg':  $unpackfrom = 'jpg';  break;
            case 'image/png':   $unpackfrom = 'png';  break;
            case 'image/webp':  $unpackfrom = 'webp'; break;
            case 'image/gif':   $unpackfrom = 'gif';  break;
        }

        // Determine pack format
        if (in_array($final_ext, ['jpg', 'jpeg', 'jfif'])) {
            $packto = 'jpg';
        } elseif (in_array($final_ext, ['png'])) {
            $packto = 'png';
        } elseif (in_array($final_ext, ['gif'])) {
            $packto = 'gif';
        } elseif (in_array($final_ext, ['webp'])) {
            $packto = 'webp';
        } elseif (in_array($ext, ['jpg', 'jpeg', 'jfif'])) {
            $packto = 'jpg';
        } elseif (in_array($ext, ['png'])) {
            $packto = 'png';
        } elseif (in_array($ext, ['gif'])) {
            $packto = 'gif';
        } elseif (in_array($ext, ['webp'])) {
            $packto = 'webp';
        }

        $dst_info = pathinfo($dst);
        if (!is_file($src) || empty($dst_info['extension'])) {
            return false;
        }

        // Load source image
        $source_img = false;
        switch ($unpackfrom) {
            case 'jpg':
            case 'jpeg':
            case 'jfif':
                $source_img = @ImageCreateFromJPEG($src);
                break;
            case 'png':
                $source_img = @ImageCreateFromPNG($src);
                break;
            case 'gif':
                $source_img = @ImageCreateFromGIF($src);
                break;
            case 'webp':
                $source_img = @ImageCreateFromWebp($src);
                break;
        }

        if ($source_img === false) {
            return false;
        }

        $w_src = imagesx($source_img);
        $h_src = imagesy($source_img);

        // Determine mode
        if ($w_src > $h_src) {
            $mode = 'width';
        } else {
            $mode = 'height';
        }
        if ($md == 'height') { $mode = 'height'; }
        if ($md == 'width')  { $mode = 'width';  }
        if ($md == 'smart')  { $mode = 'smart';  }
        if ($md == 'c' || $md == 'f') { $mode = $md; }

        if ($mode == 'smart' || $mode == 'c') {
            // Smart crop: fit into dimensions, crop extra
            $source_width = $w_src;
            $source_height = $h_src;
            $dest_width = $width;
            $dest_height = $height;

            $width_proportion = $source_width / $dest_width;
            $height_proportion = $source_height / $dest_height;
            $common_proportion = ($width_proportion < $height_proportion) ? $width_proportion : $height_proportion;

            $equal_width = $dest_width * $common_proportion;
            $equal_height = $dest_height * $common_proportion;
            $width_offset = intval(($source_width - $equal_width) / 2);
            $height_offset = intval(($source_height - $equal_height) / 2);

            $tmp_img = imageCreateTrueColor($dest_width, $dest_height);
            imageAlphaBlending($tmp_img, false);
            imageSaveAlpha($tmp_img, true);
            imageCopyResampled($tmp_img, $source_img, 0, 0, $width_offset, $height_offset, $dest_width, $dest_height, $equal_width, $equal_height);

        } elseif ($mode == 'f') {
            // Fit mode: fit into box with padding
            $source_width = $w_src;
            $source_height = $h_src;
            $dest_width = $width;
            $dest_height = $height;

            $width_proportion = $source_width / $dest_width;
            $height_proportion = $source_height / $dest_height;
            $common_proportion = ($width_proportion > $height_proportion) ? $width_proportion : $height_proportion;

            $equal_width = $source_width / $common_proportion;
            $equal_height = $source_height / $common_proportion;
            $width_offset = intval(($dest_width - $equal_width) / 2);
            $height_offset = intval(($dest_height - $equal_height) / 2);

            $tmp_img = imageCreateTrueColor($dest_width, $dest_height);
            imageAlphaBlending($tmp_img, false);
            $trans_colour = imagecolorallocatealpha($tmp_img, 255, 255, 255, 127);
            imagefill($tmp_img, 0, 0, $trans_colour);
            imageCopyResampled($tmp_img, $source_img, $width_offset, $height_offset, 0, 0, $equal_width, $equal_height, $source_width, $source_height);
            imageSaveAlpha($tmp_img, true);

        } else {
            // Simple resize by width or height
            $ratio = 1;
            if ($mode == 'width') {
                if ($w_src > $width) {
                    $ratio = $w_src / $width;
                }
            } else {
                $tmp = $width;
                $width = $height;
                $height = $tmp;
                if ($h_src > $height) {
                    $ratio = $h_src / $height;
                }
            }
            $width_tmp = intval($w_src / $ratio);
            $height_tmp = intval($h_src / $ratio);
            $tmp_img = imageCreateTrueColor($width_tmp, $height_tmp);
            imageAlphaBlending($tmp_img, false);
            imageSaveAlpha($tmp_img, true);
            imageCopyResampled($tmp_img, $source_img, 0, 0, 0, 0, $width_tmp, $height_tmp, $w_src, $h_src);
        }

        // Save result
        $this->saveImage($tmp_img, $dst, $packto);

        ImageDestroy($source_img);
        ImageDestroy($tmp_img);
        return array($width, $height);
    }

    /**
     * Save GD image resource to file with format-specific quality
     *
     * @param resource $img   GD image resource
     * @param string   $dst   Destination path
     * @param string   $packto Format: 'jpg', 'png', 'gif', 'webp'
     */
    public function saveImage($img, string $dst, string $packto): void
    {
        switch ($packto) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($img, $dst, (int)$this->config->getConfigValue('jpeg_quality'));
                break;
            case 'png':
                imagepng($img, $dst, (int)$this->config->getConfigValue('png_quality'));
                break;
            case 'gif':
                imagegif($img, $dst);
                break;
            case 'webp':
                imagewebp($img, $dst);
                break;
        }
    }

    /**
     * Rotate image to normal position based on EXIF orientation data
     *
     * @param string $image Path to image file
     */
    public function rotateImageToNormalPosition(string $image): void
    {
        if (function_exists('exif_read_data')) {
            $exif = @exif_read_data($image, 0, true);
            if (isset($exif['IFD0']['Orientation']) && !empty($exif['IFD0']['Orientation'])) {
                switch ($exif['IFD0']['Orientation']) {
                    case 8:
                        $this->rotateImageInDestination($image, $image, 90);
                        break;
                    case 3:
                        $this->rotateImageInDestination($image, $image, 180);
                        break;
                    case 6:
                        $this->rotateImageInDestination($image, $image, -90);
                        break;
                }
            }
        }
    }

    /**
     * Rotate image and save to destination
     *
     * @param string $source_image Path to source image
     * @param string $destination  Path to destination
     * @param int    $degree       Rotation degrees
     * @return string
     */
    public function rotateImageInDestination(string $source_image, string $destination, int $degree): string
    {
        $arr = explode('.', $source_image);
        $ext = end($arr);

        if ($source_image === '') {
            return '';
        }

        $source_image_res = $this->loadImage($source_image, $ext);

        if (false === $source_image_res) {
            return '';
        }

        $im = imagerotate($source_image_res, $degree, 0);
        $this->saveImage($im, $destination, $ext);

        ImageDestroy($source_image_res);
        ImageDestroy($im);

        return '';
    }

    /**
     * Load image from file by extension
     *
     * @param string $path Path to image
     * @param string $ext  Extension (jpg, png, gif, webp)
     * @return resource|false GD image resource or false
     */
    public function loadImage(string $path, string $ext)
    {
        $ext = strtolower($ext);
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
            case 'jfif':
                return @imagecreatefromjpeg($path);
            case 'png':
                return @imagecreatefrompng($path);
            case 'gif':
                return @imagecreatefromgif($path);
            case 'webp':
                return @imagecreatefromwebp($path);
            default:
                return false;
        }
    }

    /**
     * Get image dimensions
     *
     * @param string $file_name Path to image
     * @return array ['width' => int, 'height' => int]
     */
    public function getImageInfo(string $file_name): array
    {
        list($width, $height) = getimagesize($file_name);
        return ['width' => $width, 'height' => $height];
    }

    /**
     * Get SVG dimensions from file
     *
     * @param string $svg_file_name Path to SVG file
     * @return array ['width' => string, 'height' => string]
     */
    public function getSvgInfo(string $svg_file_name): array
    {
        $xmlget = simplexml_load_string(file_get_contents($svg_file_name));
        $xmlattributes = $xmlget->attributes();
        return [
            'width'  => (string)$xmlattributes->width,
            'height' => (string)$xmlattributes->height,
        ];
    }

    /**
     * Get allowed extensions for document uploads
     *
     * @return array
     */
    public static function getDocUploadsExtensions(): array
    {
        return ['docx', 'doc', 'xls', 'pdf', 'xlsx', 'jpg', 'jpeg', 'png', 'webp', 'mp4'];
    }

    /**
     * Get upload image size parameters from config
     *
     * @param string $action     Model/action name (e.g. 'data')
     * @param array  $parameters Optional parameter overrides
     * @return array [big_width, big_height, preview_width, preview_height]
     */
    public function getUploadImageSizeParameters(string $action, ?array $parameters = []): array
    {
        if ($parameters === null) {
            $parameters = [];
        }
        $big_width = $this->config->getConfigValue($action . '_image_big_width');
        if ($big_width == '') {
            $big_width = $this->config->getConfigValue('data_image_big_width');
        }
        $big_height = $this->config->getConfigValue($action . '_image_big_height');
        if ($big_height == '') {
            $big_height = $this->config->getConfigValue('data_image_big_height');
        }

        $preview_width = $this->config->getConfigValue($action . '_image_preview_width');
        if ($preview_width == '') {
            $preview_width = $this->config->getConfigValue('data_image_preview_width');
        }
        $preview_height = $this->config->getConfigValue($action . '_image_preview_height');
        if ($preview_height == '') {
            $preview_height = $this->config->getConfigValue('data_image_preview_height');
        }

        // Parameter overrides
        if (isset($parameters['norm_width']) && (int)$parameters['norm_width'] != 0) {
            $big_width = (int)$parameters['norm_width'];
        }
        if (isset($parameters['norm_height']) && (int)$parameters['norm_height'] != 0) {
            $big_height = (int)$parameters['norm_height'];
        }
        if (isset($parameters['prev_width']) && (int)$parameters['prev_width'] != 0) {
            $preview_width = (int)$parameters['prev_width'];
        }
        if (isset($parameters['prev_height']) && (int)$parameters['prev_height'] != 0) {
            $preview_height = (int)$parameters['prev_height'];
        }

        return [$big_width, $big_height, $preview_width, $preview_height];
    }

    /**
     * Create watermark instance from config
     *
     * @param bool $allow_local Whether to try local watermark class
     * @return object Watermark instance
     */
    public function createWatermarkInstance(bool $allow_local = false)
    {
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/watermark/watermark.php';

        if ($allow_local && file_exists(SITEBILL_DOCUMENT_ROOT . '/local/apps/system/lib/system/watermark/local_watermark.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/local/apps/system/lib/system/watermark/local_watermark.php';
            $instance = new \Local_Watermark();
        } else {
            $instance = new \Watermark();
        }

        $instance->setPosition($this->config->getConfigValue('apps.watermark.position'));
        $instance->setOffsets([
            $this->config->getConfigValue('apps.watermark.offset_left'),
            $this->config->getConfigValue('apps.watermark.offset_top'),
            $this->config->getConfigValue('apps.watermark.offset_right'),
            $this->config->getConfigValue('apps.watermark.offset_bottom'),
        ]);

        return $instance;
    }

    /**
     * Apply watermark to images if enabled
     *
     * @param string $normal_image  Path to normal image
     * @param string $preview_image Path to preview image
     * @param object|null $watermark_inst Existing watermark instance (for reuse)
     * @return bool Whether watermark was applied
     */
    public function doWatermark(string $normal_image, string $preview_image, $watermark_inst = null): bool
    {
        if ($this->config->getConfigValue('is_watermark')) {
            if (!$watermark_inst) {
                $watermark_inst = $this->createWatermarkInstance();
            }
            $watermark_inst->printWatermark($normal_image);
            if ($this->config->getConfigValue('apps.watermark.preview_enable')) {
                $watermark_inst->printWatermark($preview_image, true);
            }
            return true;
        }
        return false;
    }

    /**
     * Move/rename file
     *
     * @param string $src Source path
     * @param string $dst Destination path
     */
    public function makeMove(string $src, string $dst): void
    {
        @rename($src, $dst);
    }

    /**
     * Check if extension is a supported image type
     *
     * @param string $ext File extension
     * @return bool
     */
    public function isImageExtension(string $ext): bool
    {
        return in_array(strtolower($ext), ['jpg', 'jpeg', 'gif', 'png', 'webp', 'jfif']);
    }

    /**
     * Check if extension is SVG
     *
     * @param string $ext File extension
     * @return bool
     */
    public function isSvgExtension(string $ext): bool
    {
        return strtolower($ext) === 'svg';
    }
}
