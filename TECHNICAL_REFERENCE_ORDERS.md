# Technical Reference - Available Orders Feature

## API Response Structure

When fetching available orders, each order object now includes the following calculated fields:

```javascript
{
  // Original Order Fields
  id: 123,
  order_number: "ORD-ABC123",
  status: "ready_for_pickup",
  delivery_fee: 500,
  delivery_address: "123 Main St",
  delivery_latitude: -1.2921,
  delivery_longitude: 36.8219,
  phone: "+254712345678",
  special_instructions: "Ring doorbell twice",
  
  // NEW Distance Calculation Fields
  distance_to_vendor: 2.5,                    // KM (numeric)
  distance_to_vendor_formatted: "2.5 km",     // String
  eta_to_vendor: "5 min",                     // String
  
  delivery_distance: 3.2,                     // KM (numeric)
  delivery_distance_formatted: "3.2 km",      // String
  eta_delivery: "7 min",                      // String
  
  total_distance: 5.7,                        // KM (numeric)
  total_distance_formatted: "5.7 km",         // String
  
  // Related Objects
  vendor: {
    id: 5,
    business_name: "Pizza Palace",
    latitude: -1.2895,
    longitude: 36.8234,
    ...
  },
  customer: {
    id: 42,
    name: "John Doe",
    ...
  }
}
```

## Service Usage

### DistanceService Class

Located at: `app/Services/DistanceService.php`

#### Method 1: Calculate Distance
```php
use App\Services\DistanceService;

$distance = DistanceService::calculateDistance(
    latitude1: -1.2921,
    longitude1: 36.8219,
    latitude2: -1.2895,
    longitude2: 36.8234
);

// Returns: 2.5 (kilometers as float)
```

#### Method 2: Format Distance
```php
$formatted = DistanceService::formatDistance(
    latitude1: -1.2921,
    longitude1: 36.8219,
    latitude2: -1.2895,
    longitude2: 36.8234
);

// Returns: "2.5 km" or "450 m" (string)
```

#### Method 3: Estimate Delivery Time
```php
$eta = DistanceService::estimateDeliveryTime(distance: 2.5);

// Returns: "5 min" or "1h 30min" (string)
```

## Database Fields Used

### Riders Table
```php
$table->decimal('current_latitude', 10, 8)->nullable();
$table->decimal('current_longitude', 11, 8)->nullable();
$table->timestamp('last_location_update')->nullable();
```

### Orders Table
```php
$table->decimal('delivery_latitude', 10, 8);
$table->decimal('delivery_longitude', 11, 8);
$table->text('delivery_address');
$table->string('phone');
$table->text('special_instructions')->nullable();
```

### Vendors Table
```php
$table->decimal('latitude', 10, 8);
$table->decimal('longitude', 11, 8);
```

## Controller Logic Flow

### DeliveryController::index()

1. **Fetch Rider**
   - Get current authenticated rider
   - Check if rider has location data

2. **Fetch Available Orders**
   - Query orders with `rider_id = null`
   - Filter by status = 'ready_for_pickup'
   - Include vendor and customer relationships

3. **Calculate Distances (For Each Order)**
   ```php
   // Pickup Distance
   distance_to_vendor = DistanceService::calculateDistance(
       rider->current_latitude,
       rider->current_longitude,
       order->vendor->latitude,
       order->vendor->longitude
   );
   
   // Delivery Distance
   delivery_distance = DistanceService::calculateDistance(
       order->vendor->latitude,
       order->vendor->longitude,
       order->delivery_latitude,
       order->delivery_longitude
   );
   
   // Total Distance
   total_distance = distance_to_vendor + delivery_distance;
   ```

4. **Format and Calculate ETAs**
   - Use `DistanceService::formatDistance()` for display strings
   - Use `DistanceService::estimateDeliveryTime()` for time estimates

5. **Return View**
   - Pass enhanced order collection to view
   - Each order now has all calculated fields

## Blade Template Usage

### Displaying Distance Information
```blade
<!-- In dashboard.blade.php -->
@use('App\Services\DistanceService')

<!-- Display formatted distance -->
{{ $order->distance_to_vendor_formatted }}

<!-- Display ETA -->
{{ $order->eta_to_vendor }}

<!-- Display total distance -->
{{ $order->total_distance_formatted }}

<!-- Calculate new ETA if needed -->
{{ DistanceService::estimateDeliveryTime($order->total_distance) }}
```

## JavaScript Integration

### Location Update Endpoint
```javascript
// POST /rider/location
fetch('/rider/location', {
  method: 'POST',
  headers: {
    'X-CSRF-TOKEN': token,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    latitude: position.coords.latitude,
    longitude: position.coords.longitude
  })
})
.then(response => response.json())
.then(data => {
  // data.success = true/false
  // Distances will be recalculated on next page load
});
```

### Accept Order Endpoint
```javascript
// POST /rider/deliveries/{orderId}/accept
fetch(`/rider/deliveries/${orderId}/accept`, {
  method: 'POST',
  headers: {
    'X-CSRF-TOKEN': token,
    'Content-Type': 'application/json'
  }
})
.then(response => response.json())
.then(data => {
  // data.success = true/false
  // data.message = success message or error
});
```

## Accuracy Notes

### Distance Calculation (Haversine Formula)
- **Accuracy**: ±0.1 km for distances up to 100 km
- **Method**: Uses mathematical great-circle distance
- **Reality**: Actual driving distance may be 1.2-1.4x longer due to road networks
- **Use Case**: Estimated distances, not GPS-accurate route planning

### Time Estimation
- **Assumption**: 30 km/h average urban speed
- **Factors not considered**: Traffic, time of day, weather, vehicle type
- **Accuracy**: ±5-10 minutes for typical deliveries
- **Improvement**: Can be enhanced with real-time traffic API

## Future Enhancement Points

### 1. Integrate Google Maps API
```php
// Calculate actual route distance
$route = GoogleMapsService::getRoute($pickup, $delivery);
$distance = $route['distance'];  // Actual route distance
$time = $route['duration'];      // Actual driving time
```

### 2. Add Traffic Consideration
```php
// Adjust ETA based on current traffic
$baseTime = DistanceService::estimateDeliveryTime($distance);
$trafficMultiplier = GoogleMapsService::getTrafficFactor();
$adjustedTime = $baseTime * $trafficMultiplier;
```

### 3. Machine Learning for Better Time Estimates
- Use historical delivery data
- Factor in time of day, weather, vendor type
- Learn rider-specific delivery speeds

### 4. Order Batching
```php
// Group nearby orders for optimal routing
$batchedOrders = DistanceService::optimizeRoute([
  $order1, $order2, $order3
]);
// Reduce total distance and improve earnings/efficiency
```

## Debugging

### Check Rider Location Update
```php
$rider = Auth::user()->rider;
dd([
  'latitude' => $rider->current_latitude,
  'longitude' => $rider->current_longitude,
  'updated_at' => $rider->last_location_update
]);
```

### Verify Order Coordinates
```php
$order = Order::find(123);
dd([
  'vendor_lat' => $order->vendor->latitude,
  'vendor_lon' => $order->vendor->longitude,
  'delivery_lat' => $order->delivery_latitude,
  'delivery_lon' => $order->delivery_longitude
]);
```

### Test Distance Calculation
```php
use App\Services\DistanceService;

// Nairobi coordinates examples
$nrbCenter = [-1.286389, 36.817223];
$nrbKasarani = [-1.267937, 36.847359];

$distance = DistanceService::calculateDistance(
  $nrbCenter[0], $nrbCenter[1],
  $nrbKasarani[0], $nrbKasarani[1]
);
// Should return approximately 5.8 km
```

## Performance Considerations

- **Distance calculations** are performed server-side (better accuracy)
- **Calculations done** during initial page load
- **No real-time updates** until page refresh or new accept/complete cycle
- **Database queries**: Optimized with eager loading (with relationships)
- **Scalability**: For 1000+ orders, consider caching calculated distances

