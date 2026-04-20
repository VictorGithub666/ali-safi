{{-- resources/views/components/map.blade.php --}}
<div id="{{ $mapId }}" style="height: {{ $height ?? '400px' }}; width: 100%;"></div>

@push('scripts')
<script>
    let map{{ $mapId }};
    let marker{{ $mapId }};
    
    function initMap{{ $mapId }}() {
        const defaultLocation = { 
            lat: {{ $lat ?? -1.2921 }}, 
            lng: {{ $lng ?? 36.8219 }} 
        };
        
        map{{ $mapId }} = new google.maps.Map(document.getElementById('{{ $mapId }}'), {
            center: defaultLocation,
            zoom: {{ $zoom ?? 13 }},
            mapTypeControl: true,
            streetViewControl: false,
            fullscreenControl: true
        });
        
        marker{{ $mapId }} = new google.maps.Marker({
            position: defaultLocation,
            map: map{{ $mapId }},
            draggable: {{ $draggable ?? 'false' }},
            animation: google.maps.Animation.DROP
        });
        
        @if($draggable ?? false)
        google.maps.event.addListener(marker{{ $mapId }}, 'dragend', function(event) {
            document.getElementById('{{ $inputId }}_lat').value = event.latLng.lat();
            document.getElementById('{{ $inputId }}_lng').value = event.latLng.lng();
            
            // Reverse geocode to get address
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: event.latLng }, (results, status) => {
                if (status === 'OK' && results[0]) {
                    document.getElementById('{{ $inputId }}_address').value = results[0].formatted_address;
                }
            });
        });
        @endif
        
        @if($searchable ?? false)
        const input = document.getElementById('{{ $searchInputId }}');
        const searchBox = new google.maps.places.SearchBox(input);
        
        map{{ $mapId }}.addListener('bounds_changed', () => {
            searchBox.setBounds(map{{ $mapId }}.getBounds());
        });
        
        searchBox.addListener('places_changed', () => {
            const places = searchBox.getPlaces();
            if (places.length === 0) return;
            
            const bounds = new google.maps.LatLngBounds();
            places.forEach(place => {
                if (!place.geometry || !place.geometry.location) return;
                
                if (place.geometry.viewport) {
                    bounds.union(place.geometry.viewport);
                } else {
                    bounds.extend(place.geometry.location);
                }
            });
            
            map{{ $mapId }}.fitBounds(bounds);
            
            const place = places[0];
            marker{{ $mapId }}.setPosition(place.geometry.location);
            
            document.getElementById('{{ $inputId }}_lat').value = place.geometry.location.lat();
            document.getElementById('{{ $inputId }}_lng').value = place.geometry.location.lng();
            document.getElementById('{{ $inputId }}_address').value = place.formatted_address;
        });
        @endif
    }
    
    // Initialize map when page loads
    if (typeof google !== 'undefined') {
        google.maps.event.addDomListener(window, 'load', initMap{{ $mapId }});
    }
</script>
@endpush