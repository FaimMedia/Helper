<?php

namespace FaimMedia\Helper\Component;

use Phalcon\Http\Request\File as RequestFile;

use Phalcon\Mvc\User\Component as UserComponent;

use Imagick;

use ImagickException;

/**
 * Image resize wrapper class using Imagick
 */
class Image extends Imagick {

	const CANVAS_CENTER     =  1;
	const CANVAS_NORTH_WEST = 11;
	const CANVAS_NORTH_EAST = 12;
	const CANVAS_SOUTH_WEST = 21;
	const CANVAS_SOUTH_EAST = 22;

	/**
	 * Constructor
	 */
	public function __construct($file) {
		if($file instanceof RequestFile) {
			$file = $file->getTempName();
		}

		if(!file_exists($file) || !is_file($file) || !is_readable($file)) {
			throw new ImagickException('The file does not exist or is not readable');
		}

		parent::__construct($file);
	}

	public function crop(int $cropWidth, int $cropHeight, int $cropX = null, int $cropY = null) {
		$centerX = round($this->getWidth() / 2);
		$centerY = round($this->getHeight() / 2);

		$cropWidthHalf  = round($cropWidth / 2);
		$cropHeightHalf = round($cropHeight / 2);

		$x1 = max(0, $centerX - $cropWidthHalf);
		$y1 = max(0, $centerY - $cropHeightHalf);

		$x2 = min($this->getWidth(), $centerX + $cropWidthHalf);
		$y2 = min($this->getHeight(), $centerY + $cropHeightHalf);

		$this->newImage = imagecreatetruecolor($cropWidth, $cropHeight);
        imagecopy($this->newImage, $this->image, 0, 0, $x1, $y1, $cropWidth, $cropHeight);

        return $this;
	}

	/**
	 * Resize
	 */
	public function resize(int $newWidth, int $newHeight, $canvas = false) {

		$w = $this->getWidth();
		$h = $this->getHeight();

		if($w > $h) {
		    $resizeWidth = $w * $newHeight / $h;
		    $resizeHeight = $newHeight;
		} else {
		    $resizeWidth = $newWidth;
		    $resizeHeight = $h * $newWidth / $w;
		}

		if(!$canvas && ($w > $newWidth || $h > $newHeight)) {
			parent::resizeImage($newWidth, $newHeight, Imagick::FILTER_LANCZOS, 0.9, true);
		}

		if($canvas === self::CANVAS_CENTER || $canvas === true) {
			parent::cropThumbnailImage($newWidth, $newHeight);
		}

		if($canvas == self::CANVAS_NORTH_WEST) {
			parent::cropImage($newWidth, $newHeight, 0, 0);
		}

		if($canvas === self::CANVAS_NORTH_EAST) {
			parent::cropImage($newWidth, $newHeight, $resizeWidth - $newWidth, 0);
		}

		if($canvas === self::CANVAS_SOUTH_WEST) {
			parent::cropImage($newWidth, $newHeight, 0, $resizeHeight - $newHeight);
		}

		if($canvas === self::CANVAS_SOUTH_EAST) {
			parent::cropImage($newWidth, $newHeight, $resizeWidth - $newWidth, $resizeHeight - $newHeight);
		}

		return $this;
	}

	/**
	 * Generate file
	 */
	protected function generateFile($type, $filename = null) {

		parent::writeImage($filename);
		$image = parent::getImageBlob();

		$mime = parent::getImageMimeType();

		$this->newMimeType = $mime;

		return $image;
	}

	/**
	 * Save file
	 */
	public function save($filename, $type = null) {
		if(!is_resource($this->newImage) && !($this->newImage instanceof Imagick)) {
			throw new ImageException('Image has not been modified');
		}

		if($type === null) {
			$type = $this->type;
		}

		$dirname = dirname($filename);
		if(!file_exists($dirname)) {
			@mkdir($dirname, 0775, true);
		}

		if(!is_writable($dirname)) {
			throw new ImageException('The target path is not writable');
		}

		$image = $this->generateFile($type, $filename);

		if(!$image) {
			throw new ImageException('File could not be saved to `'.$filename.'`');
		}

		$this->resizedWidth = parent::getImageWidth();
		$this->resizedHeight = parent::getImageHeight();

		$this->isSaved = true;
		$this->newFile = $filename;

		return $this;
	}

	/**
	 * Output image directly
	 */
	public function output() {
		$this->generateFile($this->type, null);
	}

	/**
	 * Destruct and clear images
	 */
	public function __destruct() {
		parent::clear();
		parent::destroy();
	}
}