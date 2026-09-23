<?php

namespace SSPM\Theme;

use App\Models\Media;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class MediaAccess
{
    public static function path(string $path): ?string
    {
        $path = preg_replace('#^public/#', '', ltrim($path, '/'));
        if (! $path || str_contains($path, '\\') || in_array('..', explode('/', $path), true)) {
            return null;
        }

        return $path;
    }

    public static function allowed(Media $media): bool
    {
        $item = $media->item;
        if (! $item || ! Gate::allows('view', $item)) {
            return false;
        }

        return match ($media->metadata['visibility'] ?? 'public') {
            'public' => true,
            'authenticated' => auth()->check(),
            'hidden' => Gate::allows('update', $item),
            default => false,
        };
    }

    public static function exists(Media $media): bool
    {
        $path = self::path($media->path);

        return $path !== null && Storage::disk('public')->exists($path);
    }

    public static function url(Media $media, bool $download = false): string
    {
        if (! $download && in_array(self::kind($media), ['audio', 'video'])) {
            return route('media.stream', $media);
        }

        return Storage::disk('public')->url(self::path($media->path) ?? '');
    }

    public static function kind(Media $media): string
    {
        $mime = strtolower($media->mime_type ?? '');
        foreach (['audio', 'video', 'image'] as $kind) {
            if (str_starts_with($mime, "$kind/")) {
                return $kind;
            }
        }

        return match (strtolower(pathinfo($media->filename, PATHINFO_EXTENSION))) {
            'wav', 'wave', 'mp3', 'm4a', 'ogg', 'flac', 'aac' => 'audio',
            'mp4', 'mov', 'webm', 'mkv' => 'video',
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'tif', 'tiff' => 'image',
            'pdf', 'doc', 'docx', 'txt', 'vtt', 'srt', 'xml' => 'document',
            default => 'other',
        };
    }
}
