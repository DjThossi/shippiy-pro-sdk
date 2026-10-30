<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Domain;

/**
 * Optional controls over the label format, size, rotation, splitting and
 * selection.
 *
 * The ShippyPro v1 API accepts label dimensions in CENTIMETRES only; this SDK
 * does not provide an inches convenience type. A 4x8 inch label must be
 * expressed in centimetres (approximately 10.16 x 20.32 cm).
 *
 * These controls are distinct from asynchronous label retrieval, which is not
 * supported in this iteration.
 */
final readonly class LabelFormatting
{
    public function __construct(
        public ?LabelType $labelType = null,
        public ?float $labelWidthCm = null,
        public ?float $labelHeightCm = null,
        public ?int $rotate = null,
        public ?int $pageNumber = null,
        public ?bool $splitPages = null,
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->labelType === null
            && $this->labelWidthCm === null
            && $this->labelHeightCm === null
            && $this->rotate === null
            && $this->pageNumber === null
            && $this->splitPages === null;
    }
}
