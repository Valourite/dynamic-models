<?php

namespace Valourite\DynamicModels\Filament\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Str;

enum FileOptions: string implements HasLabel
{
    case JPEG = 'image/jpeg';
    case PNG  = 'image/png';
    case GIF  = 'image/gif';
    case WEBP = 'image/webp';
    case AVIF = 'image/avif';
    case PDF  = 'application/pdf';
    case WORD = 'application/docx';

    /**
     * @inheritDoc
     */
    public function getLabel(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return Str::title($this->name);
    }
}
