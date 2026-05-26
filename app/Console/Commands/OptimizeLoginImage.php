<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OptimizeLoginImage extends Command
{
    protected $signature = 'audit:optimize-login-image';

    protected $description = 'Resize and compress the login banner image for faster page loads';

    public function handle(): int
    {
        $source = public_path('images/deped-marikina-login.png');

        if (! is_file($source)) {
            $this->error('Login image not found at public/images/deped-marikina-login.png');

            return self::FAILURE;
        }

        if (! extension_loaded('gd')) {
            $this->warn('PHP GD extension is not enabled. Enable gd in php.ini to compress the login image.');

            return self::FAILURE;
        }

        $image = imagecreatefrompng($source);

        if (! $image) {
            $this->error('Could not read PNG image.');

            return self::FAILURE;
        }

        imagepalettetotruecolor($image);
        imagesavealpha($image, true);

        $width = imagesx($image);
        $height = imagesy($image);
        $maxWidth = 1400;

        if ($width > $maxWidth) {
            $targetHeight = (int) round($height * ($maxWidth / $width));
            $resized = imagecreatetruecolor($maxWidth, $targetHeight);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $maxWidth, $targetHeight, $width, $height);
            imagedestroy($image);
            $image = $resized;
            $width = $maxWidth;
            $height = $targetHeight;
        }

        imagepng($image, $source, 7);
        imagedestroy($image);

        $jpegPath = public_path('images/deped-marikina-login.jpg');
        $jpeg = imagecreatefrompng($source);
        imagejpeg($jpeg, $jpegPath, 82);
        imagedestroy($jpeg);

        $pngSize = filesize($source);
        $jpegSize = is_file($jpegPath) ? filesize($jpegPath) : 0;

        $this->info('Login images optimized.');
        $this->line('  PNG: '.number_format($pngSize).' bytes');
        $this->line('  JPEG: '.number_format($jpegSize).' bytes (used on login page)');

        return self::SUCCESS;
    }
}
