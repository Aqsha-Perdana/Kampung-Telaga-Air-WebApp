<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CompressImagesCommand extends Command
{
    protected $signature = 'images:compress 
                            {--path=assets/images/backgrounds : Path relative to public/ directory}
                            {--quality=75 : JPEG compression quality (1-100)}
                            {--max-width=1920 : Maximum width in pixels}';

    protected $description = 'Compress and resize images to optimize website performance';

    public function handle()
    {
        $relativePath = $this->option('path');
        $quality = (int) $this->option('quality');
        $maxWidth = (int) $this->option('max-width');
        $directory = public_path($relativePath);

        if (!is_dir($directory)) {
            $this->error("Directory not found: {$directory}");
            return 1;
        }

        $extensions = ['jpg', 'jpeg', 'png', 'JPG', 'JPEG', 'PNG'];
        $files = [];

        foreach ($extensions as $ext) {
            $files = array_merge($files, glob("{$directory}/*.{$ext}"));
        }

        if (empty($files)) {
            $this->warn("No image files found in {$directory}");
            return 0;
        }

        $this->info("Found " . count($files) . " image(s) in {$relativePath}");
        $this->info("Settings: quality={$quality}, max-width={$maxWidth}px");
        $this->newLine();

        $totalSavedBytes = 0;

        foreach ($files as $file) {
            $filename = basename($file);
            $originalSize = filesize($file);

            $this->info("Processing: {$filename}");
            $this->info("  Original size: " . $this->formatSize($originalSize));

            $result = $this->compressImage($file, $quality, $maxWidth);

            if ($result === false) {
                $this->warn("  ⚠ Could not process {$filename}, skipping.");
                continue;
            }

            $newSize = filesize($file);
            $saved = $originalSize - $newSize;
            $totalSavedBytes += $saved;
            $percentage = $originalSize > 0 ? round(($saved / $originalSize) * 100, 1) : 0;

            $this->info("  New size: " . $this->formatSize($newSize));
            $this->info("  ✓ Saved: " . $this->formatSize($saved) . " ({$percentage}% reduction)");
            $this->newLine();
        }

        $this->newLine();
        $this->info("═══════════════════════════════════════");
        $this->info("Total space saved: " . $this->formatSize($totalSavedBytes));
        $this->info("═══════════════════════════════════════");

        return 0;
    }

    private function compressImage(string $filePath, int $quality, int $maxWidth): bool
    {
        $info = getimagesize($filePath);
        if ($info === false) {
            return false;
        }

        $mime = $info['mime'];
        $originalWidth = $info[0];
        $originalHeight = $info[1];

        // Create image resource based on type
        switch ($mime) {
            case 'image/jpeg':
                $image = @imagecreatefromjpeg($filePath);
                break;
            case 'image/png':
                $image = @imagecreatefrompng($filePath);
                break;
            default:
                return false;
        }

        if ($image === false) {
            return false;
        }

        // Resize if wider than max width
        if ($originalWidth > $maxWidth) {
            $ratio = $maxWidth / $originalWidth;
            $newWidth = $maxWidth;
            $newHeight = (int) round($originalHeight * $ratio);

            $resized = imagecreatetruecolor($newWidth, $newHeight);

            // Preserve transparency for PNG
            if ($mime === 'image/png') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
            }

            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
            imagedestroy($image);
            $image = $resized;

            $this->info("  Resized: {$originalWidth}x{$originalHeight} → {$newWidth}x{$newHeight}");
        }

        // Save compressed image
        switch ($mime) {
            case 'image/jpeg':
                $result = imagejpeg($image, $filePath, $quality);
                break;
            case 'image/png':
                // PNG quality is 0-9 (inverted from JPEG), map from JPEG quality
                $pngQuality = (int) round((100 - $quality) / 11.111);
                $result = imagepng($image, $filePath, $pngQuality);
                break;
            default:
                $result = false;
        }

        imagedestroy($image);
        return $result;
    }

    private function formatSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
