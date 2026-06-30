@php
    // Cloudinary serves this PDF under /image/upload/, which can fail to
    // render reliably in an <iframe> (some accounts block/limit raw PDF
    // delivery through the image pipeline for security reasons). As a
    // display-only workaround, we ask Cloudinary to rasterize page 1 of
    // the PDF as a JPG instead — swapping the .pdf extension for .jpg
    // (and adding pg_1) triggers Cloudinary's built-in PDF-to-image
    // transformation on image-pipeline assets.
    $previewImageUrl = null;

    if ($invoiceUrl && str_contains($invoiceUrl, '/image/upload/')) {
        $previewImageUrl = preg_replace(
            '#/image/upload/#',
            '/image/upload/pg_1/',
            $invoiceUrl
        );
        $previewImageUrl = preg_replace('/\.pdf$/i', '.jpg', $previewImageUrl);
    }
@endphp

<div class="fi-in-entry-wrp">
    @if ($previewImageUrl)
        <img
            src="{{ $previewImageUrl }}"
            alt="Invoice PDF preview (page 1)"
            style="max-width: 100%; max-height: 480px; border: 1px solid rgb(229 231 235); border-radius: 0.5rem; display: block;"
        />
    @elseif ($invoiceUrl)
        {{-- Fallback: not a recognizable Cloudinary image-pipeline URL,
             just link out directly. --}}
    @else
        <p class="text-sm text-gray-500">No invoice file available.</p>
    @endif
</div>