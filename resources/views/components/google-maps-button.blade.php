@props(['url', 'label' => 'Open in Google Maps', 'icon' => 'bi-geo-alt', 'variant' => 'primary'])

@if($url)
    <a href="{{ $url }}" 
       target="_blank" 
       rel="noopener noreferrer"
       class="btn btn-{{ $variant }} w-100 mb-2">
        <i class="{{ $icon }} me-2"></i>
        {{ $label }}
        <i class="bi bi-box-arrow-up-right ms-2 small"></i>
    </a>
@endif