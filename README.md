# trustpilot-reviews-collector
Retrive reviews from Trustpilot.com

## Installation
You can install TrustpilotReviewsCollector library via Composer:
```yml
composer require rrortega/trustpilot-reviews-collector
```

## Usage

Here's an example of how to use TrustpilotReviewsCollector:

```php
use rrortega\TrustpilotReviewsCollector; 

$id="www.google.com";
$count=1;
$orderby = 'time';
$order = 'desc'
$collector = new TrustpilotReviewsCollector($id,$count, $orderby , $order );
$reviews = $collector->getReviews();
```

## Testing

You can run the unit tests for TrustpilotReviewsCollector Library using PHPUnit:

```cli
./vendor/bin/phpunit
```


## License

TrustpilotReviewsCollector Library is licensed under the MIT license. See the LICENSE file for more information. 

