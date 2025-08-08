<?php

namespace App\Service;

use Cloudinary\Cloudinary as CloudinarySDK;
use Cloudinary\Transformation\Resize;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CloudinaryService
{
    private CloudinarySDK $cloudinary;

    public function __construct(CloudinarySDK $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }

    /**
     * Upload an image to Cloudinary
     */
    public function uploadImage(UploadedFile $file, string $folder = 'products'): array
    {
        try {
            $result = $this->cloudinary->uploadApi()->upload(
                $file->getPathname(),
                [
                    'public_id' => $folder . '/' . uniqid(),
                    'resource_type' => 'image',
                    'transformation' => [
                        'width' => 800,
                        'height' => 800,
                        'crop' => 'limit',
                        'quality' => 'auto'
                    ]
                ]
            );

            return [
                'success' => true,
                'public_id' => $result['public_id'],
                'url' => $result['secure_url'],
                'width' => $result['width'],
                'height' => $result['height']
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete an image from Cloudinary
     */
    public function deleteImage(string $publicId): array
    {
        try {
            $result = $this->cloudinary->uploadApi()->destroy($publicId);
            return [
                'success' => true,
                'result' => $result
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate a transformed URL for an image
     */
    public function getTransformedUrl(string $publicId, array $options = []): string
    {
        $defaultOptions = [
            'width' => 400,
            'height' => 400,
            'crop' => 'fill',
            'quality' => 'auto'
        ];

        $transformationOptions = array_merge($defaultOptions, $options);

        return $this->cloudinary->image($publicId)
            ->resize(Resize::fill($transformationOptions['width'], $transformationOptions['height']))
            ->quality($transformationOptions['quality'])
            ->toUrl();
    }

    /**
     * Generate a thumbnail URL
     */
    public function getThumbnailUrl(string $publicId): string
    {
        return $this->getTransformedUrl($publicId, [
            'width' => 150,
            'height' => 150,
            'crop' => 'fill'
        ]);
    }

    /**
     * Generate a medium size URL
     */
    public function getMediumUrl(string $publicId): string
    {
        return $this->getTransformedUrl($publicId, [
            'width' => 400,
            'height' => 400,
            'crop' => 'fill'
        ]);
    }

    /**
     * Generate a large size URL
     */
    public function getLargeUrl(string $publicId): string
    {
        return $this->getTransformedUrl($publicId, [
            'width' => 800,
            'height' => 800,
            'crop' => 'limit'
        ]);
    }
} 