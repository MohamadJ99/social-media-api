<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdRequest;
use App\Http\Requests\UpdateAdRequest;
use App\Http\Resources\AdResource;
use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class AdController extends Controller
{
    /**
     * Display active advertisements to authenticated users.
     */
    public function index()
    {
        $ads = Ad::query()
            ->available()
            ->latest()
            ->paginate(10);

        return AdResource::collection($ads);
    }

    /**
     * Display all advertisements for administrators.
     */
    public function adminIndex()
    {
        $ads = Ad::query()
            ->latest()
            ->paginate(15);

        return AdResource::collection($ads);
    }

    /**
     * Display a specific advertisement.
     */
    public function show(Ad $ad)
    {
        return new AdResource($ad);
    }

    /**
     * Create a new advertisement.
     */
    public function store(StoreAdRequest $request)
    {
        $validated = $request->validated();

        $imagePath = null;
        $logoPath = null;

        try {
            $imagePath = $this->storeImage(
                $request,
                'image',
                'ads/images'
            );

            if ($request->hasFile('advertiser_logo')) {
                $logoPath = $this->storeImage(
                    $request,
                    'advertiser_logo',
                    'ads/logos'
                );
            }

            unset(
                $validated['image'],
                $validated['advertiser_logo']
            );

            $validated['image_path'] = $imagePath;
            $validated['advertiser_logo_path'] = $logoPath;

            $ad = Ad::create($validated);

            return (new AdResource($ad))
                ->response()
                ->setStatusCode(201);

        } catch (Throwable $exception) {

            $this->deleteStoredFiles([
                $imagePath,
                $logoPath,
            ]);

            throw $exception;
        }
    }

    /**
     * Update an existing advertisement.
     */
    public function update(UpdateAdRequest $request, Ad $ad)
    {
        $validated = $request->validated();

        $newImagePath = null;
        $newLogoPath = null;

        $oldImagePath = $ad->image_path;
        $oldLogoPath = $ad->advertiser_logo_path;

        try {
            if ($request->hasFile('image')) {
                $newImagePath = $this->storeImage(
                    $request,
                    'image',
                    'ads/images'
                );

                $validated['image_path'] = $newImagePath;
            }

            if ($request->hasFile('advertiser_logo')) {
                $newLogoPath = $this->storeImage(
                    $request,
                    'advertiser_logo',
                    'ads/logos'
                );

                $validated['advertiser_logo_path'] = $newLogoPath;
            }

            unset(
                $validated['image'],
                $validated['advertiser_logo']
            );

            $ad->update($validated);

        } catch (Throwable $exception) {

            $this->deleteStoredFiles([
                $newImagePath,
                $newLogoPath,
            ]);

            throw $exception;
        }

        // Delete old files only after a successful database update.
        if ($newImagePath !== null && $oldImagePath !== null) {
            Storage::disk('public')->delete($oldImagePath);
        }

        if ($newLogoPath !== null && $oldLogoPath !== null) {
            Storage::disk('public')->delete($oldLogoPath);
        }

        return new AdResource($ad->refresh());
    }

    /**
     * Soft delete an advertisement.
     */
    public function destroy(Ad $ad)
    {
        $ad->delete();

        return response()->noContent();
    }

    /**
     * Store an uploaded image on the public disk.
     */
    private function storeImage(
        Request $request,
        string $field,
        string $directory
    ): string {
        $path = $request->file($field)->store(
            $directory,
            'public'
        );

        if ($path === false) {
            throw new RuntimeException(
                "Failed to store advertisement file: {$field}"
            );
        }

        return $path;
    }

    /**
     * Delete newly uploaded files if an operation fails.
     */
    private function deleteStoredFiles(array $paths): void
    {
        foreach ($paths as $path) {
            if ($path !== null) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
