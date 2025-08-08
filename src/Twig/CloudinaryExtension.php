<?php

namespace App\Twig;

use App\Service\CloudinaryService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CloudinaryExtension extends AbstractExtension
{
    private CloudinaryService $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('cloudinary_url', [$this, 'getCloudinaryUrl']),
            new TwigFunction('cloudinary_thumbnail', [$this, 'getThumbnailUrl']),
            new TwigFunction('cloudinary_medium', [$this, 'getMediumUrl']),
            new TwigFunction('cloudinary_large', [$this, 'getLargeUrl']),
        ];
    }

    public function getCloudinaryUrl(?string $publicId, array $options = []): string
    {
        if (!$publicId) {
            return '';
        }
        return $this->cloudinaryService->getTransformedUrl($publicId, $options);
    }

    public function getThumbnailUrl(?string $publicId): string
    {
        if (!$publicId) {
            return '';
        }
        return $this->cloudinaryService->getThumbnailUrl($publicId);
    }

    public function getMediumUrl(?string $publicId): string
    {
        if (!$publicId) {
            return '';
        }
        return $this->cloudinaryService->getMediumUrl($publicId);
    }

    public function getLargeUrl(?string $publicId): string
    {
        if (!$publicId) {
            return '';
        }
        return $this->cloudinaryService->getLargeUrl($publicId);
    }
} 