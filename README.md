# trustpilot-reviews-collector
Retrive reviews from Trustpilot.com

## Installation
You can install TrustpilotReviewCollector library via Composer:
```yml
composer require rrortega/trustpilot-review-collector
```

## Usage

Here's an example of how to use TrustpilotReviewCollector:

```php
use RRO\Review\Collector\TrustpilotReviewCollector;

$businessUnitId="www.google.com";
$count=5;
$orderby = 'time';
$order = 'desc'
$collector = new TrustpilotReviewCollector($businessUnitId,$count, $orderby , $order );
$reviews = $collector->getReviews();
foreach($reviews as $r){
    
     //echo  $r["id"];
     //echo  $r["user"];
     //echo  $r["avatar"];
     //echo  $r["verified"]; //true if user is verified in truspilot
     //echo  $r["title"];
     //echo  $r["url"];
     //echo  $r["body"];
     //echo  $r["rating"]; //1 to 5 float value
     //echo  $r["time"]; //ex: 2023-03-20T13:30:34.000Z
}

```

## Testing

You can run the unit tests for TrustpilotReviewCollector Library using PHPUnit:

```cli
./vendor/bin/phpunit
```


## License

TrustpilotReviewCollector Library is licensed under the MIT license. See the LICENSE file for more information. 

