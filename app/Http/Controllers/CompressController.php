<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CompressController extends Controller
{
    public function showForm()
    {
        return view('compress.form');
    }

    public function compress(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // max 50MB
            'mode' => 'required|in:lossless,maximal',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $tempPath = $file->storeAs('temp', Str::random(10) . '.' . $extension);
        $fullPath = storage_path('app/' . $tempPath);

        $compressedPath = null;
        $compressedName = 'compressed_' . $originalName;
        $mode = $request->input('mode', 'lossless');

        // Kompresi berdasarkan tipe file
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'bmp':
            case 'webp':
                $compressedPath = $this->compressImage($fullPath, $extension, $mode);
                break;
            case 'pdf':
                $compressedPath = $this->compressPdf($fullPath, $mode);
                break;
            case 'doc':
            case 'docx':
                $compressedPath = $this->compressWord($fullPath);
                break;
            case 'ppt':
            case 'pptx':
                $compressedPath = $this->compressPpt($fullPath);
                break;
            case 'mp4':
            case 'avi':
            case 'mov':
            case 'mkv':
            case 'webm':
                $compressedPath = $this->compressVideo($fullPath, $extension, $mode);
                break;
            default:
                return back()->with('error', 'Tipe file tidak didukung untuk kompresi.');
        }

        if (!$compressedPath || !file_exists($compressedPath)) {
            return back()->with('error', 'Gagal mengompres file.');
        }

        return response()->download($compressedPath, $compressedName)->deleteFileAfterSend(true);
    }

    // Kompresi gambar (lossless, jika tidak signifikan baru lossy)
    private function compressImage($path, $ext, $mode = 'lossless')
    {
        try {
            $info = getimagesize($path);
            $output = storage_path('app/temp/compressed_' . basename($path));
            if (in_array($ext, ['jpg', 'jpeg'])) {
                $img = imagecreatefromjpeg($path);
                if ($mode === 'lossless') {
                    imagejpeg($img, $output, 100);
                } else {
                    imagejpeg($img, $output, 75);
                }
                imagedestroy($img);
                if ($mode === 'lossless' && filesize($output) >= filesize($path) * 0.95) {
                    $img = imagecreatefromjpeg($path);
                    imagejpeg($img, $output, 75);
                    imagedestroy($img);
                }
            } elseif ($ext === 'png') {
                $img = imagecreatefrompng($path);
                if ($mode === 'lossless') {
                    imagepng($img, $output, 9);
                } else {
                    $jpegOutput = preg_replace('/\\.png$/i', '.jpg', $output);
                    imagejpeg($img, $jpegOutput, 75);
                    imagedestroy($img);
                    if (file_exists($jpegOutput) && filesize($jpegOutput) < filesize($path)) {
                        return $jpegOutput;
                    }
                }
                imagedestroy($img);
                if ($mode === 'lossless' && filesize($output) >= filesize($path) * 0.95) {
                    $img = imagecreatefrompng($path);
                    $jpegOutput = preg_replace('/\\.png$/i', '.jpg', $output);
                    imagejpeg($img, $jpegOutput, 75);
                    imagedestroy($img);
                    if (file_exists($jpegOutput) && filesize($jpegOutput) < filesize($output)) {
                        return $jpegOutput;
                    }
                }
            } elseif ($ext === 'webp') {
                $img = imagecreatefromwebp($path);
                if ($mode === 'lossless') {
                    imagewebp($img, $output, 100);
                } else {
                    imagewebp($img, $output, 75);
                }
                imagedestroy($img);
                if ($mode === 'lossless' && filesize($output) >= filesize($path) * 0.95) {
                    $img = imagecreatefromwebp($path);
                    imagewebp($img, $output, 75);
                    imagedestroy($img);
                }
            } else {
                copy($path, $output);
            }
            return $output;
        } catch (\Exception $e) {
            Log::error('Gagal kompres gambar: ' . $e->getMessage());
            return null;
        }
    }

    // Kompresi PDF (lossless, jika tidak signifikan baru lossy)
    private function compressPdf($path, $mode = 'lossless')
    {
        $output = storage_path('app/temp/compressed_' . basename($path));
        $gsPath = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'gswin64c' : 'gs';
        $setting = $mode === 'maximal' ? '/screen' : '/ebook';
        $cmd = "$gsPath -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=$setting -dNOPAUSE -dQUIET -dBATCH -sOutputFile=\"$output\" \"$path\"";
        try {
            $result = null;
            $returnVar = null;
            @exec($cmd, $result, $returnVar);
            if ($returnVar === 0 && file_exists($output) && filesize($output) < filesize($path)) {
                return $output;
            }
            // Jika lossless dan tidak signifikan, coba lossy
            if ($mode === 'lossless') {
                $cmdLossy = "$gsPath -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/screen -dNOPAUSE -dQUIET -dBATCH -sOutputFile=\"$output\" \"$path\"";
                @exec($cmdLossy, $result, $returnVar);
                if ($returnVar === 0 && file_exists($output) && filesize($output) < filesize($path)) {
                    return $output;
                }
            }
        } catch (\Exception $e) {
            Log::error('Gagal kompres PDF: ' . $e->getMessage());
        }
        return $path;
    }

    // Kompresi Word (lossless, jika tidak signifikan baru lossy)
    private function compressWord($path)
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext === 'docx') {
            $output = storage_path('app/temp/compressed_' . basename($path));
            try {
                // Unzip dan zip ulang (lossless, hapus metadata jika perlu)
                $zip = new \ZipArchive();
                if ($zip->open($path) === TRUE) {
                    $tmpDir = storage_path('app/temp/' . uniqid('docx_'));
                    mkdir($tmpDir);
                    $zip->extractTo($tmpDir);
                    $zip->close();
                    // Bisa hapus file metadata di $tmpDir/docProps jika ingin
                    $zip2 = new \ZipArchive();
                    if ($zip2->open($output, \ZipArchive::CREATE) === TRUE) {
                        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($tmpDir));
                        foreach ($files as $file) {
                            if (!$file->isDir()) {
                                $filePath = $file->getRealPath();
                                $relativePath = substr($filePath, strlen($tmpDir) + 1);
                                $zip2->addFile($filePath, $relativePath);
                            }
                        }
                        $zip2->close();
                    }
                    // Hapus folder sementara
                    $this->deleteDir($tmpDir);
                    if (file_exists($output) && filesize($output) < filesize($path)) {
                        return $output;
                    }
                }
            } catch (\Exception $e) {
                Log::error('Gagal kompres DOCX: ' . $e->getMessage());
            }
        }
        // Untuk DOC (bukan docx), return file asli
        return $path;
    }

    // Kompresi PPT (lossless, jika tidak signifikan baru lossy)
    private function compressPpt($path)
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext === 'pptx') {
            $output = storage_path('app/temp/compressed_' . basename($path));
            try {
                // Unzip dan zip ulang (lossless, hapus metadata jika perlu)
                $zip = new \ZipArchive();
                if ($zip->open($path) === TRUE) {
                    $tmpDir = storage_path('app/temp/' . uniqid('pptx_'));
                    mkdir($tmpDir);
                    $zip->extractTo($tmpDir);
                    $zip->close();
                    // Bisa hapus file metadata di $tmpDir/docProps jika ingin
                    $zip2 = new \ZipArchive();
                    if ($zip2->open($output, \ZipArchive::CREATE) === TRUE) {
                        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($tmpDir));
                        foreach ($files as $file) {
                            if (!$file->isDir()) {
                                $filePath = $file->getRealPath();
                                $relativePath = substr($filePath, strlen($tmpDir) + 1);
                                $zip2->addFile($filePath, $relativePath);
                            }
                        }
                        $zip2->close();
                    }
                    // Hapus folder sementara
                    $this->deleteDir($tmpDir);
                    if (file_exists($output) && filesize($output) < filesize($path)) {
                        return $output;
                    }
                }
            } catch (\Exception $e) {
                Log::error('Gagal kompres PPTX: ' . $e->getMessage());
            }
        }
        // Untuk PPT (bukan pptx), return file asli
        return $path;
    }

    // Kompresi Video (lossless, jika tidak signifikan baru lossy)
    private function compressVideo($path, $ext, $mode = 'lossless')
    {
        $output = storage_path('app/temp/compressed_' . basename($path));
        $ffmpeg = 'ffmpeg';
        if ($mode === 'lossless') {
            $cmd = "$ffmpeg -i \"$path\" -c copy -map 0 \"$output\" -y";
        } else {
            $cmd = "$ffmpeg -i \"$path\" -b:v 800k -b:a 128k -vf scale=iw*0.8:ih*0.8 \"$output\" -y";
        }
        try {
            $result = null;
            $returnVar = null;
            @exec($cmd, $result, $returnVar);
            if ($returnVar === 0 && file_exists($output) && filesize($output) < filesize($path)) {
                return $output;
            }
            // Jika lossless dan tidak signifikan, coba lossy
            if ($mode === 'lossless') {
                $cmdLossy = "$ffmpeg -i \"$path\" -b:v 800k -b:a 128k -vf scale=iw*0.8:ih*0.8 \"$output\" -y";
                @exec($cmdLossy, $result, $returnVar);
                if ($returnVar === 0 && file_exists($output) && filesize($output) < filesize($path)) {
                    return $output;
                }
            }
        } catch (\Exception $e) {
            Log::error('Gagal kompres video: ' . $e->getMessage());
        }
        return $path;
    }

    // Fungsi bantu hapus folder rekursif
    private function deleteDir($dir)
    {
        if (!file_exists($dir)) return;
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDir($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
} 