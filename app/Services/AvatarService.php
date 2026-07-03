<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AvatarService
{
    /**
     * Store a new avatar. The upload is re-encoded through GD into a fresh PNG
     * so the bytes we persist are ones we generated — stripping any embedded
     * payload (EXIF, polyglot, script) that passed the mime check. Old file is
     * removed. Returns the stored (public-disk) path.
     */
    public function update(User $user, UploadedFile $file): string
    {
        $image = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));

        if ($image === false) {
            throw ValidationException::withMessages([
                'avatar' => __('Berkas gambar tidak valid.'),
            ]);
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        ob_start();
        imagepng($image, null, 6);
        $binary = (string) ob_get_clean();
        imagedestroy($image);

        $path = 'avatars/'.Str::uuid()->toString().'.png';
        Storage::disk('public')->put($path, $binary);

        $old = $user->avatar_path;
        $user->forceFill(['avatar_path' => $path])->save();

        if ($old) {
            Storage::disk('public')->delete($old);
        }

        return $path;
    }

    public function delete(User $user): void
    {
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->forceFill(['avatar_path' => null])->save();
        }
    }
}
