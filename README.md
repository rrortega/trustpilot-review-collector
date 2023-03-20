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
$count=1;
$orderby = 'time';
$order = 'desc'
$collector = new TrustpilotReviewCollector($businessUnitId,$count, $orderby , $order );
$reviews = $collector->getReviews();
```

## Testing

You can run the unit tests for TrustpilotReviewCollector Library using PHPUnit:

```cli
./vendor/bin/phpunit
```


## License

TrustpilotReviewCollector Library is licensed under the MIT license. See the LICENSE file for more information. 

