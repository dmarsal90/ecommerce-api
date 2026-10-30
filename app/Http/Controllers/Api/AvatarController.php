<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateAvatarRequest;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;
use Throwable;

class AvatarController extends Controller
{
    private const SIZE = 256;

    public function store(UpdateAvatarRequest $request): JsonResponse
    {
        $user = $request->user();
        $temp = tempnam(sys_get_temp_dir(), 'avatar').'.webp';

        try {
            // Re-encoding drops metadata and anything hidden in the original file.
            Image::load($request->file('avatar')->getRealPath())
                ->fit(Fit::Crop, self::SIZE, self::SIZE)
                ->format('webp')
                ->save($temp);

            $path = 'avatars/user-'.$user->id.'-'.Str::random(10).'.webp';
            Storage::disk('public')->put($path, file_get_contents($temp));
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'The image could not be processed.',
                'errors' => ['avatar' => ['The image could not be processed.']],
            ], 422);
        } finally {
            @unlink($temp);
        }

        $this->deleteOwnFile($user);
        $user->update(['avatar_url' => $path]);

        return response()->json(['user' => (new UserResource($user))->resolve()]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->deleteOwnFile($user);
        $user->update(['avatar_url' => null]);

        return response()->json(['user' => (new UserResource($user))->resolve()]);
    }

    /** Only files uploaded here are removed; admin presets and uploads stay untouched. */
    private function deleteOwnFile(User $user): void
    {
        if ($user->avatar_url && preg_match('#^avatars/user-\d+-#', $user->avatar_url)) {
            Storage::disk('public')->delete($user->avatar_url);
        }
    }
}
